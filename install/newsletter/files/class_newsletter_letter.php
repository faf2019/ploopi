<?php
/*
    Copyright (c) 2008 HeXad
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Gestion des newsletters
 *
 * @package newsletter
 * @subpackage entry
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_newsletter_letter
 *
 * @package newsletter
 * @subpackage entry
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

class newsletter extends data_object
{
  /**
   * Constructeur de la classe
   *
   * @return data_object
   */
  function newsletter()
  {
    parent::data_object('ploopi_mod_newsletter_letter');
  }

  /**
   * Enregistre la newsletter
   *
   * @return boolean true si l'enregistrement a été effectué
   */
  function save()
  {
    $new_newsletter = $this->new;

    if($this->new) // creation
    {
      $this->fields['timestp']    = ploopi_createtimestamp();
      $this->fields['id_author']  = $_SESSION['ploopi']['user']['id'];
      $this->fields['author']     = $_SESSION['ploopi']['user']['lastname'].' '.$_SESSION['ploopi']['user']['firstname'];
    }
    else // Modification
    {
      $this->fields['lastupdate_timestp'] = ploopi_createtimestamp();
      $this->fields['lastupdate_id_user'] = $_SESSION['ploopi']['user']['id'];
      $this->fields['lastupdate_user']    = $_SESSION['ploopi']['user']['lastname'].' '.$_SESSION['ploopi']['user']['firstname'];
    }
    if(empty($this->fields['status'])) $this->fields['status'] = 'draft';

    $this->setuwm(); // id_module, id_user, id_worspace

    // Enregistrement du contenu nettoyé
    $this->fields['content'] = ploopi_htmlpurifier($this->fields['content']);

    //Log
    if($new_newsletter)
      ploopi_create_user_action_log(_NEWSLETTER_ACTION_WRITE, ploopi_strcut($this->fields['title'],150).' (id='.$this->fields['id'].')');
    else
      ploopi_create_user_action_log(_NEWSLETTER_ACTION_MODIFY, ploopi_strcut($this->fields['title'],150).' (id='.$this->fields['id'].')');

    $result_save = parent::save();

    //Si attente de validation on envoit un ticket aux validateurs
    if($this->fields['status'] == 'wait')
    {
      $arrNewsletterTo = newsletter_ListValid($this->fields['id'],-1,false);
      ploopi_subscription_notify(_NEWSLETTER_OBJECT_NEWSLETTER, $this->fields['id'], _NEWSLETTER_ACTION_WAIT_VALID, $this->fields['title'], array_keys($arrNewsletterTo));
    }

    return $result_save;
  }

  /**
   * Valider une newsletter
   *
   * @return mixed valeur de la clé primaire
   */
  function validate()
  {
    $this->fields['status'] = 'valid';
    $this->fields['validated_timestp'] = ploopi_createtimestamp();
    $this->fields['validated_id_user'] = $_SESSION['ploopi']['user']['id'];
    $this->fields['validated_user'] = $_SESSION['ploopi']['user']['lastname'].' '.$_SESSION['ploopi']['user']['firstname'];

    // Log
    ploopi_create_user_action_log(_NEWSLETTER_ACTION_VALIDATED, ploopi_strcut($this->fields['title'],150).' (id='.$this->fields['id'].')');

    return parent::save();
  }
  /**
   * Expédition d'une newsletter
   *
   * @return mixed valeur de la clé primaire
   */
  function send()
  {
    $this->fields['status'] = 'send';
    $this->fields['send_timestp'] = ploopi_createtimestamp();
    $this->fields['send_id_user'] = $_SESSION['ploopi']['user']['id'];
    $this->fields['send_user'] = $_SESSION['ploopi']['user']['lastname'].' '.$_SESSION['ploopi']['user']['firstname'];

    // Log
    ploopi_create_user_action_log(_NEWSLETTER_ACTION_SEND, ploopi_strcut($this->fields['title'],150).' (id='.$this->fields['id'].')');

    return parent::save();
  }

  /**
   * Suppression de la newsletter
   *
   */
  function delete()
  {
    global $db;

    // Nettoyage de la liste des envois
    $db->query("DELETE FROM ploopi_mod_newsletter_send WHERE id_letter = '{$this->fields['id']}'");

    // Log
    ploopi_create_user_action_log(_NEWSLETTER_ACTION_DELETE, ploopi_strcut($this->fields['title'],150).' (id='.$this->fields['id'].')');

    parent::delete();
  }

  /**
   * Fonction de génération d'un fichier pdf a partir de la page newsletter
   *
   * @return fichier pdf
   */
  function create_pdf()
  {
    // Ouverture du buffer de sorti
    ob_start();

    global $ploopi_days;
    global $ploopi_months;

    $intIdNewsletter = $this->fields['id'];
    $strNewsletterMode = 'dompdf';

    include_once './modules/newsletter/display.php';

    $content = ob_get_contents(); // recupération du contenu généré
    ob_end_clean(); // Nettoyage du buffer

    //echo htmlentities($content).'<br/><br/><br/>';
    // Convertion du contenu au format dompdf (<a href> etc.. avec host mais <img> en realpath)
    $content = $this->convert_dompdf($content);
    //echo htmlentities($content).'<br/>';
    //ploopi_die();

    /*
     * Mise en pace de la classe dompdf
     * Conversion en PDF
     */
    include_once './lib/dompdf/dompdf_config.inc.php';

    header("Content-Encoding: none");

    $dompdf = new DOMPDF();
    $dompdf->load_html($content);
    $dompdf->render();
   // ploopi_die();
    //Export PDF
    $dompdf->stream($this->fields['title'].'.pdf');
  }

  /**
   * Gènère un "content" compatible avec un appel de "l'extérieur" (Url type http://www.monsite.fr/monimage.png pour les images par exemple)
   *
   * @return content_extern
   */
  function convert_extern($content = '')
  {
     if($content == '') $content = $this->fields['content'];
    /**
     * Réécriture des liens vers articles, documents et des images
     */
    // Recherche des liens vers des documents (du module doc)
    // Pour les remplacer (urlrewrite)
    $arrSearch = array();
    $arrReplace = array();

    // Récupération des param (pour le host)
    include_once './modules/newsletter/class_newsletter_param.php';
    $objNewsletterParam = new newsletter_param();
    $arrNewsletterParam = $objNewsletterParam->get_param($this->fields['id_module']);
    unset($objNewsletterParam);

    
    if (ploopi_init_module('webedit', false, false, false))
    {
      include_once './modules/webedit/class_article.php';

      // traitement des liens vers articles
      preg_match_all('/<a[^>]*href="(index\.php[^\"]+articleid=([0-9]+)[^\"]*)"[^>]*>/i', $content, $matches);
      foreach($matches[2] as $key => $idart)
      {
          $objArticle = new webedit_article();
          if (!empty($idart) && $objArticle->open($idart)) // article trouvé
          {
              $arrSearch[] = $matches[1][$key];
              $arrReplace[] = $arrNewsletterParam['host'].ploopi_urlrewrite("index.php?headingid={$objArticle->fields['id_heading']}&articleid={$idart}", webedit_getrewriterules(), $objArticle->fields['metatitle']);
          }
      }
    }

    if (ploopi_init_module('doc', false, false, false))
    {
        include_once './modules/doc/class_docfile.php';

        // traitement des liens vers documents
        preg_match_all('/<a[^>]*href="(index-quick\.php[^\"]+docfile_md5id=([a-z0-9]{32}))"[^>]*>/i', $content, $matches);
        foreach($matches[2] as $key => $md5)
        {
            $objDocFile = new docfile();
            if (!empty($md5) && $objDocFile->openmd5($md5)) // clé md5 présente & document trouvé
            {
                $arrSearch[] = $matches[1][$key];
                $arrReplace[] = $arrNewsletterParam['host'].ploopi_urlrewrite(html_entity_decode($matches[1][$key]), doc_getrewriterules(), $objDocFile->fields['name'], null, true);
            }
        }

        // traitement des images
        preg_match_all('/<img[^>]*src="(index-quick\.php[^\"]+docfile_md5id=([a-z0-9]{32}))"[^>]*>/i', $content, $matches);
        foreach($matches[2] as $key => $md5)
        {
            $objDocFile = new docfile();
            if (!empty($md5) && $objDocFile->openmd5($md5)) // clé md5 présente & document trouvé
            {
                $arrSearch[] = $matches[1][$key];
                $arrReplace[] = $arrNewsletterParam['host'].ploopi_urlrewrite(html_entity_decode($matches[1][$key]), doc_getrewriterules(), $objDocFile->fields['name'], null, true);
            }
        }
    }

    return str_replace($arrSearch, $arrReplace, $content);
  }

  /**
   * Gènère un "content" compatible avec dompdf
   *
   * @return string content_dompdf
   */
  private function convert_dompdf($content)
  {
    /**
     * Réécriture des liens vers articles, documents et des images
     */
    // Recherche des liens vers des documents (du module doc)
    // Pour les remplacer (urlrewrite)
    $arrSearch = array();
    $arrReplace = array();

    // Récupération des param (pour le host)
    include_once './modules/newsletter/class_newsletter_param.php';
    $objNewsletterParam = new newsletter_param();
    $arrNewsletterParam = $objNewsletterParam->get_param();
    unset($objNewsletterParam);

    if (ploopi_init_module('webedit', false, false, false))
    {
      include_once './modules/webedit/class_article.php';

      // traitement des liens vers articles
      preg_match_all('/<a[^>]*href="(index\.php[^\"]+articleid=([0-9]+)[^\"]*)"[^>]*>/i', $content, $matches);
      foreach($matches[2] as $key => $idart)
      {
          $objArticle = new webedit_article();
          if (!empty($idart) && $objArticle->open($idart)) // article trouvé
          {
              $arrSearch[] = $matches[1][$key];
              $arrReplace[] = $arrNewsletterParam['host'].ploopi_urlrewrite("index.php?headingid={$objArticle->fields['id_heading']}&articleid={$idart}", webedit_getrewriterules(), $objArticle->fields['metatitle']);
          }
      }
    }

    if (ploopi_init_module('doc', false, false, false))
    {
        include_once './modules/doc/class_docfile.php';

        // traitement des liens vers documents
        preg_match_all('/<a[^>]*href="(index-quick\.php[^\"]+docfile_md5id=([a-z0-9]{32}))"[^>]*>/i', $content, $matches);
        foreach($matches[2] as $key => $md5)
        {
            $objDocFile = new docfile();
            if (!empty($md5) && $objDocFile->openmd5($md5)) // clé md5 présente & document trouvé
            {
                $arrSearch[] = $matches[1][$key];
                $arrReplace[] = $arrNewsletterParam['host'].ploopi_urlrewrite(html_entity_decode($matches[1][$key]), doc_getrewriterules(), $objDocFile->fields['name'], null, true);
            }
        }

        // traitement des images
        // Image en chemin type doc
        preg_match_all('/<img[^>]*src="(index-quick\.php[^\"]+docfile_md5id=([a-z0-9]{32}))"[^>]*>/i', $content, $matches);
        foreach($matches[2] as $key => $md5)
        {
            $objDocFile = new docfile();
            if (!empty($md5) && $objDocFile->openmd5($md5)) // clé md5 présente & document trouvé
            {
                $arrSearch[] = $matches[1][$key];
                $arrReplace[] = $objDocFile->getfilepath();
            }
        }
    }

    // traitement des images HORS ged
    include_once './include/classes/documents.php';

    // Image en chemin type mini ged => Banniere
    preg_match_all('/<img[^>]*src="('.str_replace(array('/','.'),array('\/','\.'),$arrNewsletterParam['host']).'index-quick\.php[^\"]+banniere_id=([0-9]{1,11})[^\"]*)[^>]*>/i', $content, $matches);
    foreach($matches[2] as $key => $id_img)
    {
      $doc = new documentsfile();
      if ($doc->open($id_img))
      {
          $arrSearch[] = $matches[1][$key];
          $arrReplace[] = $doc->getfilepath();
      }
    }

    // Image en chemin relatif (./templates/...) ATTENTION, A CONSERVER A LA FIN SINON CONFLICT AVEC  Image en chemin type mini ged => Banniere
    preg_match_all('/<img[^>]*src="('.str_replace(array('/','.'),array('\/','\.'),$arrNewsletterParam['host']).'[^\"]*)[^>]*>/i' , $content, $matches);
    foreach($matches[1] as $key => $md5)
    {
      $arrSearch[] = $matches[1][$key];
      $arrReplace[] = str_replace($arrNewsletterParam['host'],realpath('.').'/',$matches[1][$key]);
    }

    return str_replace($arrSearch, $arrReplace, $content);
  }
}
?>