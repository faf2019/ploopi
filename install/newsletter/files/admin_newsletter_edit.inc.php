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
 * Edition des newsletters
 *
 * @package newsletter
 * @subpackage newsletter
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

$objLetter = new newsletter();

$wfusers = array();
// Liste des validateurs globaux en detail
$wfusers_tempo = ploopi_validation_get(_NEWSLETTER_OBJECT_NEWSLETTER);

//Ouverture de la newsletter si le id est connu
if(isset($_GET['id_newsletter']))
{
  // Ouvre la lettre en modification
  $objLetter->open($_GET['id_newsletter']); 
  // Ajout a l'action transmise au form de l'id en cours de modif
  $action_id_newsletter = "&id_newsletter={$_GET['id_newsletter']}";
  // Ajout $wfusers � de la liste des validateurs de la newsletter
  if(isset($_GET['id_newsletter'])) $wfusers_tempo += ploopi_validation_get(_NEWSLETTER_OBJECT_NEWSLETTER, $objLetter->fields['id']);
}
else
{
  // Initialise les champs en cr�ation
  $objLetter->init_description(); 
  $action_id_newsletter='';
}
//nettoyage de $wfusers pour ne garder que les id_user validateur
foreach($wfusers_tempo as $value) $wfusers[] = $value['id_validation'];
unset($wfusers_tempo); // Plus besoin, on vide

// Si id de newsletter connu, on est donc en modif ou en validation... verif si la modif/validation est autoris� au user courant sinon on vire
if(isset($_GET['id_newsletter']) && (!ploopi_isactionallowed(_NEWSLETTER_ACTION_MODIFY) && (!in_array($_SESSION['ploopi']['userid'],$wfusers) && $objLetter->fields['status'] == 'wait')))
  ploopi_redirect('admin.php?newsletterToolbarNewsletter=tabNewsletterList');

echo $skin->open_simplebloc(_NEWSLETTER_LABEL_NEWSLETTER_MANAGE);

// Si la lettre valid�e ou envoy�e on passe en consultation en force gr�ce a readonly = true sinon c'est en fonction des autorisations
if($objLetter->fields['status'] == 'valid' || $objLetter->fields['status'] == 'send')
  $readonly = true; 
else
  $readonly = (!ploopi_isactionallowed(_NEWSLETTER_ACTION_WRITE) && !ploopi_isactionallowed(_NEWSLETTER_ACTION_MODIFY));

if (!$readonly)
{
  ?>
  <form style="margin:0;" action="<? echo ploopi_urlencode('admin.php?op=newsletter_save'.$action_id_newsletter); ?>" method="post" onsubmit="javascript:return newsletter_letter_validate(this, <? echo (in_array($_SESSION['ploopi']['userid'],$wfusers)) ? 'true' : 'false'; ?>);">
  <?
}
?>
<div class="ploopi_form" style="float:left;width:50%;">
  <div style="padding:2px;">
    <p style="font-weight:bold;"><?php echo _NEWSLETTER_LABEL_PRINC_PROPRIETY; ?>:</p>
    <p>
        <label><? echo _NEWSLETTER_LABEL_TITLE; ?>:</label>
        <?php
        if (!$readonly)
        {
            ?>
            <input type="text" class="text" name="newsletter_title"  value="<? echo htmlentities($objLetter->fields['title']); ?>" tabindex="1" />
            <label>&nbsp;</label><span style="font-style:italic;"><?php echo _NEWSLETTER_LABEL_TITLE_EXPLAIN; ?></span>
            <?php
        }
        else echo '<span>'.htmlentities($objLetter->fields['title']).'</span>';
        ?>
    </p>
    <p>
        <label><? echo _NEWSLETTER_LABEL_SUBJECT; ?>:</label>
        <?php
        if (!$readonly)
        {
            ?>
            <input type="text" class="text" name="newsletter_subject"  value="<? echo htmlentities($objLetter->fields['subject']); ?>" tabindex="2" />
            <label>&nbsp;</label><span style="font-style:italic;"><?php echo _NEWSLETTER_LABEL_SUBJECT_EXPLAIN; ?></span>
            <?php
        }
        else echo '<span>'.htmlentities($objLetter->fields['subject']).'</span>';
        ?>
    </p>
    <p>
        <label><? echo _NEWSLETTER_LABEL_GABARIT; ?>:</label>
        <?php
        if (!$readonly)
        {
            ?>
            <select class="select" id="newsletter_template" name="newsletter_template" tabindex="4">
                <option value="" <? if ($objLetter->fields['template'] == '') echo 'selected="selected"'; ?>>---&nbsp;<? echo _NEWSLETTER_DEFAULT; ?>&nbsp;---</option>
                <?php
                $newsletter_templates = newsletter_gettemplates();
                foreach ($newsletter_templates as $tpl)
                {
                    ?>
                    <option value="<? echo $tpl; ?>" <? if ($objLetter->fields['template'] == $tpl) echo 'selected="selected"'; ?>><? echo $tpl; ?></option>
                    <?
                }
            ?>
            </select>
            <label>&nbsp;</label><span style="font-style:italic;"><?php echo _NEWSLETTER_LABEL_GABARIT_EXPLAIN; ?></span>
            <?php
        }
        else
        {
          $templ = ($objLetter->fields['template'] !== '') ? $objLetter->fields['template'] : '--- '._NEWSLETTER_DEFAULT.' ---';
          echo '<span>'.$templ.'</span>';
        }
        ?>
    </p>
  </div>
</div>
<div class="ploopi_form" style="float:left;width:50%;">
  <div style="padding:2px;">
    <span>&nbsp;</span>
    <p>
        <label><? echo _NEWSLETTER_LABEL_STATUS; ?>:</label>
        <?php
        if (!$readonly)
        {
            ?>
            <select class="select" id="newsletter_status" name="newsletter_status" tabindex="5">
                <option value="draft" <? if ($objLetter->fields['status'] == 'draft') echo 'selected="selected"'; ?>><? echo _NEWSLETTER_LABEL_STATUS_DRAFT; ?></option>
                <option value="wait" <? if ($objLetter->fields['status'] == 'wait') echo 'selected="selected"'; ?>><? echo _NEWSLETTER_LABEL_STATUS_WAIT; ?></option>
            </select>
            <?php
        }
        else
        {
          ?>
          <input type="hidden" name="newsletter_status"  value="<? echo $objLetter->fields['status']; ?>"/>
          <span>
          <?php
          echo (defined('_NEWSLETTER_LABEL_STATUS_'.strtoupper($objLetter->fields['status']))) ? constant('_NEWSLETTER_LABEL_STATUS_'.strtoupper($objLetter->fields['status'])): '';
          ?>
          </span>
          <?php
        }
        ?>
    </p>
  </div>
  <?php
  /*
   * Affichage des dates de cr�ation / modif / valid
   */
  if (isset($_GET['id_newsletter']))
  {
    echo '<p>';
    
    // date time creation
    $arrNewsletterDate = ploopi_timestamp2local($objLetter->fields['timestp']);
    echo '<label>'._NEWSLETTER_LABEL_CREATE.':</label><span>'.$arrNewsletterDate['date'].' '.$arrNewsletterDate['time'].'</span>';
  
    // date time modif
    if($objLetter->fields['lastupdate_timestp'] > 0)
    {
      $arrNewsletterDateModif = ploopi_timestamp2local($objLetter->fields['lastupdate_timestp']);
      echo '<label>'._NEWSLETTER_LABEL_MODIF.':</label><span>'.$arrNewsletterDateModif['date'].' '.$arrNewsletterDateModif['time'].'</span>';
    }
    
    // date time valid
    if($objLetter->fields['validated_timestp'] > 0)
    {
      $arrNewsletterDateValid = ploopi_timestamp2local($objLetter->fields['validated_timestp']);
      echo '<label>'._NEWSLETTER_LABEL_VALID.':</label><span>'.$arrNewsletterDateValid['date'].' '.$arrNewsletterDateValid['time'].'</span>';
    }
    echo '</p>';
  }
  ?>  
</div>
<?php
/*
 * Affichage de la gestion des validateurs 
 */
?>
<div style="clear:both;padding:4px;background-color:#e8e8e8;border-top:1px solid #c0c0c0;">
  <?php
  if(!$readonly)
  {
    // Affichage des validateurs Globaux
    $arrValidateursGlb = newsletter_ListValid('newsletter',_NEWSLETTER_LABEL_VALIDATOR_GLB,true,$_SESSION['ploopi']['moduleid']);
    if(count($arrValidateursGlb) > 0)
    {
      foreach($arrValidateursGlb as $id => $data) $arrValidateur[] =  trim($data['lastname'].' '.$data['firstname']); 
      
      echo '<span style="font-style: italic;">'._NEWSLETTER_LABEL_VALIDATOR_GLB.':&nbsp;'.implode('&nbsp;/&nbsp;',$arrValidateur).'</span>';
    }
  }
  // Bloc de gestion des validateurs ploopi
  if(!$readonly && ploopi_isactionallowed(_NEWSLETTER_ACTION_MANAGE_VALIDATOR))
  {
  ?>
    <div style="border:1px solid #c0c0c0;overflow:hidden;">
      <?php ploopi_validation_selectusers(_NEWSLETTER_OBJECT_NEWSLETTER,(empty($objLetter->fields['id']) ? '0' : $objLetter->fields['id'])); ?>
    </div>
  <?php
  }
  ?>
</div>
<?
/*
 * Affichage des boutons
 */
?>
<div style="padding:4px; background-color:#e0e0e0; clear:both; border-width: 1px 0;border-color:#c0c0c0; border-style:solid;">
  <div style="text-align:right;">
  <?php
  if(isset($_GET['id_newsletter']))
  {
    ?>
      <input type="button" class="button" style="margin:1px;" value="<?php echo _NEWSLETTER_LABEL_GENERATE_PDF; ?>" 
        onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=newsletter_pdf&id_newsletter={$_GET['id_newsletter']}"); ?>'" />
    <?php
  }
  if(isset($_GET['id_newsletter']) && in_array($_SESSION['ploopi']['userid'],$wfusers) && $objLetter->fields['status'] == 'wait')
  {
    ?>
    <input type="button" class="button" style="margin:1px;" value="<?php echo _NEWSLETTER_VALIDATE; ?>" 
      onclick="javascript:document.location.href='<?php echo ploopi_urlencode("admin.php?op=newsletter_validate&id_newsletter={$_GET['id_newsletter']}"); ?>'" />
    <?php
  }
    
  if (!$readonly)
  {
    ?>
    <input type="submit" class="button" style="margin:1px;" value="<?php echo _PLOOPI_SAVE; ?>" />
    <?php
  }
  ?>
    <input type="button" class="button" style="margin:1px;" value="<?php echo _NEWSLETTER_RETURN ?>" onclick="javascript:document.location.href='admin.php?newsletterToolbarNewsletter=tabNewsletterList';">
  </div>
</div>

<div style="clear:both;">
  <?
  if (!$readonly)
  {
    ?>
    <div id="xToolbar"></div> <!-- Bloc d'affichage des barres d'outils de fckeditor -->
    <input type="hidden" id="fck_newsletter_content" name="fck_newsletter_content" value=""> <!-- Bloc utilis� en javascript pour ramener dans la page courante le contenu de l'iframe fckEditor et ainsi passer fck_newsletter_content en $_POST -->
    <?
  }
  ?>
  <!-- Iframe contenant soit un rendu avec fckEditor soit juste un rendu en readonly-->
  <iframe id="newsletter_frame_editor" style="border:0;width:100%;height:450px;margin:0;padding:0;" src="<? echo ploopi_urlencode("index-quick.php?id_module={$_SESSION['ploopi']['moduleid']}&ploopi_op=newsletter_tpl&id_newsletter={$objLetter->fields['id']}"); ?>"></iframe>
</div>
<?php
if (!$readonly)
{
  ?>
  </form>
  <?
}

echo $skin->close_simplebloc();
?>