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
 * @subpackage send
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */
include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_newsletter_send
 *
 * @package newsletter
 * @subpackage send
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

class newsletter_send extends data_object
{
  private $IntIdNewsletter;

  /**
   * Constructeur de la classe
   *
   * @return data_object
   */
  function newsletter_send($idNewsletter)
  {
    $this->IntIdNewsletter = $idNewsletter;
    parent::data_object('ploopi_mod_newsletter_send');
  }

  /**
   * Expédition de la Newsletter
   *
   * @return true/false
   */
  function newsletter_send_letter()
  {
    global $db;

    include_once './modules/newsletter/class_newsletter_letter.php';
    include_once './modules/newsletter/class_newsletter_subscriber.php';
    include_once './modules/newsletter/class_newsletter_param.php';

    // Recupération des paramètres de la newsletter (from_name, from_email et send_by)
    $objNewsletterParam = new newsletter_param();
    $arrNewsletterParam = $objNewsletterParam->get_param();
    unset($objNewsletterParam);

    //OUverture de la newsletter
    $objLetter = new newsletter();
    if(!$objLetter->open($this->IntIdNewsletter)) return false;

    // Si ce n'est pas une lettre de type 'valid' on n'envoit pas
    if($objLetter->fields['status'] != 'valid' ) return false;

    // Recupération de la liste des inscrits
    $sql = "SELECT sub.email
            FROM ploopi_mod_newsletter_subscriber as sub
            WHERE sub.id_module = '{$_SESSION['ploopi']['moduleid']}'
              AND sub.active = '1'";
    $sqlResult = $db->query($sql);
    if($db->numrows($sqlResult) > 0)
    {
      $i = $j = 1;
      $sqlSend = '';
      while ($fields = $db->fetchrow($sqlResult))
      {
        // Découpage en bloc si besoin
        $arrTo[$i][] = array('name' => '', 'address' => $fields['email']);
        if($arrNewsletterParam['send_by'] != 0 && $j >= $arrNewsletterParam['send_by'])
        {
          $i++;
          $j = 1;
        }
        // Préparation de la requete pour intégrer toute les donnée d'un coup dans ploopi_mod_newsletter_send
        if($sqlSend != '') $sqlSend .= ',';
        $sqlSend .= '(\''.$fields['email'].'\' , \''.$this->IntIdNewsletter.'\' , \''.ploopi_createtimestamp().'\')';
      }

      /*
       * Contenu du Mail
       */
      ob_start();

      global $ploopi_days;
      global $ploopi_months;

      $intIdNewsletter = $this->IntIdNewsletter;
      $strNewsletterMode = 'display';

      include_once './modules/newsletter/display.php';

      $content = ob_get_contents();
      ob_end_clean();

      /*
       * Envoi des mails
       */
      if($content != '')
      {
        $emailFrom[0] = array('address'   => $arrNewsletterParam['from_email'],
                             'name'  => $arrNewsletterParam['from_name']
                            );
        /*
         * Expédition par bloc si besoin
         */
        foreach($arrTo as $intBloc => $arrSendTo)
        {
          ploopi_send_mail($emailFrom,
                          '',
                          $objLetter->fields['subject'],
                          $content,
                          '',
                          $arrSendTo,
                          '',
                          '',
                          true);
        }
        /*
         * Met a jour la table ploopi_mod_newsletter_send avec les envois
         */
        if($sqlSend != '')
          $db->query('INSERT INTO `ploopi_mod_newsletter_send` (`email_subscriber`, `id_letter`, `timestp_send`) VALUES '.$sqlSend);

        return true;
      }
    }
    return false;
  }
}
?>