<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Moteur de rendu de newsletter
 *
 * @package Newsletter
 * @subpackage display
 * @copyright Netlor, Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 *
 * @global Template $template_body template
 * @global string $template_path chemin vers le template
 */

// strNewsletterMode : Mode d'ouverture (edit / display / dompdf => mode édition / affichage frontoffice ou popup / affichage pour dompdf)

// readonly : true / false => article modifiable oui/non (charge fckeditor si false)
// newsletter_mode : edit / display / dompdf => mode édition / affichage frontoffice ou popup / affichage pour dompdf

/**
 * Inclusion de la collection de fonction de ploopi (pour ploopi_urlencode notament)
 */
include_once './include/start/functions.php';

/**
 * Inclusion de la classe template (moteur du template)
 */
include_once './lib/template/template.php';

/**
 * Inclusion des classes du module
 */
include_once './modules/newsletter/class_newsletter_letter.php';

global $template_body;
global $template_path;

$today = ploopi_createtimestamp();

// recupération des param $_GET
$type = (empty($_GET['type'])) ? '' : $_GET['type'];

// Récupération du mode d'ouverture de la newsletter
if(!empty($strNewsletterMode))
  $newsletter_mode = $strNewsletterMode;
elseif(ploopi_isactionallowed(_NEWSLETTER_ACTION_WRITE) || ploopi_isactionallowed(_NEWSLETTER_ACTION_MODIFY))
  $newsletter_mode = 'edit';
else
  $newsletter_mode = 'display';

// Etat de readonly
$readonly = ($newsletter_mode == 'display' || $newsletter_mode == 'dompdf');

// OUVERTURE DE LA NEWSLETTER
$objNewsletter = new newsletter();

if(isset($_GET['id_newsletter']) && $_GET['id_newsletter'] > 0)
  $objNewsletter->open($_GET['id_newsletter']);
elseif(!empty($intIdNewsletter))
  $objNewsletter->open($intIdNewsletter);
else
  $objNewsletter->init_description();

// Changement d'état de readonly si le status de la newsletter est validée ou expédiée
if($objNewsletter->fields['status'] == 'valid' || $objNewsletter->fields['status'] == 'send') $readonly = 1;

// CHARGEMENT DU TEMPLATE
// get template name
$template_name = (!empty($objNewsletter->fields['template'])) ? $objNewsletter->fields['template'] : '';
$template_path = './modules/newsletter/template_default/'; // ATTENTION => peut changer juste après !

if($template_name !== '' && file_exists(_NEWSLETTER_TEMPLATES_PATH."/$template_name")) // Template perso
{
  $template_path = _NEWSLETTER_TEMPLATES_PATH."/$template_name";
}
elseif($template_name !== '' && file_exists($template_path."/$template_name")) // Template par defaut
{
  $template_path = $template_path."/$template_name";
}else // Pas de template enregistré donc template par defaut avec banniere fixe
{
  // Si pas de template spécifique, on passe le template par defaut.
  $template_name = '';
  $template_path = './modules/newsletter/template_default/exemple_banniere_fixe/';
}

$template_newsletter = new Template($template_path);

// fichier template par défaut
$template_file = 'newsletter.tpl';

// Récupération des param (pour le host)
include_once './modules/newsletter/class_newsletter_param.php';
$objNewsletterParam = new newsletter_param();
$arrNewsletterParam = $objNewsletterParam->get_param($objNewsletter->fields['id_module']);
unset($objNewsletterParam);

// Si on est pas en lecture uniquement on charge fckeditor
if (!$readonly)
{
  ob_start();

  include_once './FCKeditor/fckeditor.php' ;

  $oFCKeditor = new FCKeditor('fck_newsletter_content') ;

  $oFCKeditor->BasePath = './FCKeditor/';

  // default value
  $oFCKeditor->Value= $objNewsletter->fields['content'];

  // width & height
  $oFCKeditor->Width='100%';
  $oFCKeditor->Height='500';

  $oFCKeditor->Config['CustomConfigurationsPath'] = _PLOOPI_BASEPATH.'/modules/newsletter/fckeditor/fckconfig.js';
  $oFCKeditor->Config['ToolbarLocation'] = 'Out:parent(xToolbar)';
  $oFCKeditor->Config['BaseHref'] = _PLOOPI_BASEPATH.'/';

  $oFCKeditor->ToolbarSet = 'Default' ;

  if (file_exists("{$template_path}/fckeditor/fck_editorarea.css")) $oFCKeditor->Config['EditorAreaCSS'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fck_editorarea.css';

  if (file_exists("{$template_path}/fckeditor/fcktemplates.xml")) $oFCKeditor->Config['TemplatesXmlPath'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fcktemplates.xml';

  if (file_exists("{$template_path}/fckeditor/fckstyles.xml")) $oFCKeditor->Config['StylesXmlPath'] = _PLOOPI_BASEPATH . substr($template_path,1) . '/fckeditor/fckstyles.xml';

  // render
  $oFCKeditor->Create('FCKeditor_1') ;
  $editor = ob_get_contents();
  ob_end_clean();
}

$template_newsletter->assign_block_vars('switch_content_newsletter', array());

if($newsletter_mode == 'dompdf')
  $content = $objNewsletter->fields['content'];
else // dans tous les autres $newsletter_mode
  $content = (!empty($editor)) ? $editor : $objNewsletter->convert_extern($objNewsletter->fields['content']);

$template_newsletter->assign_vars(
  array(
    'PAGE_CONTENT' => $content
  )
);

// DATES
if($objNewsletter->fields['status'] == 'send')
{
  $timestp = ploopi_timestamp2unixtimestamp($objNewsletter->fields['send_timestp']);
  $dateDay        = date('d',$timestp);
  $dateMonth      = date('m',$timestp);
  $dateYear       = date('Y',$timestp);
  $dateDayText    = $ploopi_days[date('w',$timestp)];
  $dateMonthText  = $ploopi_months[date('n',$timestp)];
}
else
{
  $dateDay        = date('d');
  $dateMonth      = date('m');
  $dateYear       = date('Y');
  $dateDayText    = $ploopi_days[date('w')];
  $dateMonthText  = $ploopi_months[date('n')];
}

$banniere = '';
if(!empty($objNewsletter->fields['banniere_id']))
{
  if($readonly && $newsletter_mode != 'dompdf') // Envois ou affichage en front ! On encode les url !
    $banniere = $arrNewsletterParam['host'].ploopi_urlencode('index-quick.php?ploopi_op=newsletter_display_banniere&banniere_id='.$objNewsletter->fields['banniere_id']);
  else
    $banniere = $arrNewsletterParam['host'].'index-quick.php?ploopi_op=newsletter_display_banniere&banniere_id='.$objNewsletter->fields['banniere_id'];
}

$background_color = (!empty($objNewsletter->fields['background_color'])) ? htmlentities($objNewsletter->fields['background_color']) : '#ffffff';
$content_color = (!empty($objNewsletter->fields['content_color'])) ? htmlentities($objNewsletter->fields['content_color']) : '#ffffff';
$text_color = (!empty($objNewsletter->fields['text_color'])) ? htmlentities($objNewsletter->fields['text_color']) : '#000000';

// Chargement des tag à appliquer au template
$template_newsletter->set_filenames(array('body' => $template_file));
$template_newsletter->assign_vars(
    array(
        'TEMPLATE_PATH'                 => $template_path,
        'TITLE'                         => htmlentities($objNewsletter->fields['title']),
        'SUBJECT'                       => htmlentities($objNewsletter->fields['subject']),
        'BANNIERE'                      => $banniere,
        'BACKGROUND_COLOR'              => $background_color,
        'CONTENT_COLOR'                 => $content_color,
        'TEXT_COLOR'                    => $text_color,
        'LINK'                          => ($objNewsletter->fields['id'] >0) ? $arrNewsletterParam['host'].ploopi_urlencode('index-quick.php?ploopi_op=newsletter_consult&id_newsletter='.$objNewsletter->fields['id']) : '.',
        'LINK_UNSUBSCRIB'               => $arrNewsletterParam['host'].ploopi_urlencode('index.php?switch_newsletter_unsubscrib=true'),
        'DATE_DAY'                      => $dateDay,
        'DATE_MONTH'                    => $dateMonth,
        'DATE_YEAR'                     => $dateYear,
        'DATE_DAYTEXT'                  => $dateDayText,
        'DATE_MONTHTEXT'                => $dateMonthText,
        'HOST'                          => $arrNewsletterParam['host']
    )
);
// Rendu du template
$template_newsletter->pparse('body');

?>
