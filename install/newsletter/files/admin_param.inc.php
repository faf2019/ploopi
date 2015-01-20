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
 * Entrée des paramètrage des Newsletters
 *
 * @package newsletter
 * @subpackage admin
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/*
 * Bloc de gestion des Paramètres du module newsletter
 */
if(ploopi_isactionallowed(_NEWSLETTER_ACTION_PARAM))
{
  include_once './modules/newsletter/class_newsletter_param.php';

  $objNewsletterParam = new newsletter_param();
  $arrNewsletterParam = $objNewsletterParam->get_param();
  unset($objNewsletterParam);

  echo $skin->open_simplebloc(_NEWSLETTER_LABEL_NEWSLETTER_PARAM);
  ?>
  <div style="padding:4px; background-color:#e0e0e0; clear:both; border-width: 1px 0;border-color:#c0c0c0; border-style:solid;">
    <?php echo _NEWSLETTER_LABEL_SEND_BY_INFO; ?>
  </div>

  <form style="margin:0;" action="<? echo ploopi_urlencode('admin.php?op=newsletter_param_save'); ?>" method="post" onsubmit="javascript:return newsletter_letter_validate_param(this);">

  <div class="ploopi_form">
    <div style="padding:2px;">
      <p><!-- Host de la newsletter (pour les liens image, la désinscrition, la vue en ligne) -->
        <label><? echo _NEWSLETTER_LABEL_HOST; ?>:</label>
        <input type="text" class="text" name="host"  value="<?php echo ploopi_htmlentities($arrNewsletterParam['host']); ?>" tabindex="1" />
        <label>&nbsp;</label><span style="font-style:italic;"><?php echo _NEWSLETTER_LABEL_HOST_EXPLAIN; ?></span>
      </p>
      <p><!-- Nom utilisé comme expéditeur au mail -->
        <label><? echo _NEWSLETTER_LABEL_FROM_NAME; ?>:</label>
        <input type="text" class="text" name="from_name"  value="<?php echo ploopi_htmlentities($arrNewsletterParam['from_name']); ?>" tabindex="2" />
        <label>&nbsp;</label><span style="font-style:italic;"><?php echo _NEWSLETTER_LABEL_FROM_NAME_EXPLAIN; ?></span>
      </p>
      <p><!-- Email utilisé comme expéditeur au mail -->
        <label><? echo _NEWSLETTER_LABEL_FROM_EMAIL; ?>:</label>
        <input type="text" class="text" name="from_email"  value="<?php echo ploopi_htmlentities($arrNewsletterParam['from_email']); ?>" tabindex="3" />
        <label>&nbsp;</label><span style="font-style:italic;"><?php echo _NEWSLETTER_LABEL_FROM_EMAIL_EXPLAIN; ?></span>
      </p>
      <p><!-- Pour l'envoi du mail par bloc de X email -->
        <label><? echo _NEWSLETTER_LABEL_SEND_BY; ?>:</label>
        <input type="text" class="text" name="send_by"  value="<?php echo ploopi_htmlentities($arrNewsletterParam['send_by']); ?>" tabindex="4" />
        <label>&nbsp;</label><span style="font-style:italic;"><?php echo _NEWSLETTER_LABEL_SEND_BY_EXPLAIN; ?></span>
        <label>&nbsp;</label><span style="font-style:italic;"><?php echo _NEWSLETTER_LABEL_SEND_BY_WARNING; ?></span>
      </p>
    </div>
  </div>
  <div style="padding:4px; background-color:#e0e0e0; clear:both; border-width: 1px 0;border-color:#c0c0c0; border-style:solid;">
    <div style="text-align:right;">
      <input type="submit" class="button" style="margin:1px;" value="<?php echo _PLOOPI_SAVE; ?>" />
    </div>
  </div>
  </form>
  <?php
  echo $skin->close_simplebloc();
}

/*
 * Bloc de gestion des validateurs "globaux"
 */
if(ploopi_isactionallowed(_NEWSLETTER_ACTION_MANAGE_VALIDATOR))
{
  echo $skin->open_simplebloc(_NEWSLETTER_LABEL_VALIDATOR_GLB_MANAGE);
  ?>
  <div style="clear:both;padding: 0;">
    <div style="border:1px solid #c0c0c0;overflow:hidden;">
      <?php ploopi_validation_selectusers(_NEWSLETTER_OBJECT_NEWSLETTER,'newsletter'); ?>
    </div>
    <div style="padding:4px;background-color:#e0e0e0;clear:both;border-width: 1px 0;border-color:#c0c0c0;border-style:solid;">
      <div style="text-align: right;" >
        <input type="button" class="button" value="<?php echo _NEWSLETTER_LABEL_VALIDATOR_GLB_SAVE; ?>"
          onclick="javascript:document.location.href='<?php echo ploopi_urlencode('admin.php?op=newsletter_save_global_validator'); ?>'" />
      </div>
    </div>
  </div>
  <?php
  echo $skin->close_simplebloc();
}
?>
