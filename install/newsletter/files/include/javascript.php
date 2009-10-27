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
 * Fonctions javascript dynamiques
 *
 * @package newsletter
 * @subpackage javascript
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/*
 * Script de contrÙle des donnÈes de la newsletter en crÈation/Èdition
 */
if((isset($_GET['newsletterToolbarNewsletter']) && $_GET['newsletterToolbarNewsletter'] == 'tabNewsletterNew')
  || (isset($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter'])
  && $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterToolbarNewsletter'] == 'tabNewsletterNew'))
{
  if (!ploopi_ismoduleallowed('newsletter')) ploopi_init_module('newsletter',false,false,false);
  ?>
  function newsletter_letter_validate(form, validator)
  {
      if($(form.newsletter_background_color).value == '') $(form.newsletter_background_color).value = '#FFFFFF';
      if($(form.newsletter_content_color).value == '') $(form.newsletter_content_color).value = '#FFFFFF';
      if($(form.newsletter_text_color).value == '') $(form.newsletter_text_color).value = '#000000';

      next = true;

      if ($('newsletter_status').value == 'wait' && !validator)
      {
        // confirm sending tickets on waiting validation
        next = confirm('Cette action va envoyer\nune demande de publication\naux validateurs de cette rubrique\n\n tes-vous certain de vouloir continuer ?');
      }

      if(next)
      {
        var fck_instance = $('newsletter_frame_editor').contentWindow.FCKeditorAPI.GetInstance('fck_newsletter_content');

        // get fckeditor content
        $('fck_newsletter_content').value = fck_instance.GetData(true)

        if ($('newsletter_status').value == 'draft')
        {
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_TITLE; ?>", form.newsletter_title, 'string'))
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_BACKGROUND_COLOR; ?>", form.newsletter_background_color, 'color'))
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_CONTENT_COLOR; ?>", form.newsletter_content_color, 'color'))
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_TEXT_COLOR; ?>", form.newsletter_text_color, 'color'))
            return true;
        }
        else
        {
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_TITLE; ?>", form.newsletter_title, 'string'))
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_SUBJECT; ?>", form.newsletter_subject, 'string'))
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_SUBJECT; ?>", form.newsletter_subject, 'string'))
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_BACKGROUND_COLOR; ?>", form.newsletter_background_color, 'color'))
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_CONTENT_COLOR; ?>", form.newsletter_content_color, 'color'))
          if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_TEXT_COLOR; ?>", form.newsletter_text_color, 'color'))
          if ($('fck_newsletter_content').value == '')
          {
              if (confirm(<?php echo _NEWSLETTER_CONFIRM_STATUS_WAIT_NEWSLETTER; ?>)) return true;
          }
          else
          {
            return true;
          }
        }
      }
      return false;
  }
<?php
}

if((isset($_GET['newsletterTabAdmin']) && $_GET['newsletterTabAdmin'] == 'tabNewsletterParam')
  ||(isset($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin'])
  && $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin'] == 'tabNewsletterParam'))
{
  if (!ploopi_ismoduleallowed('newsletter')) ploopi_init_module('newsletter',false,false,false);
  ?>
  function newsletter_letter_validate_param(form)
  {
    if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_HOST; ?>", form.host, 'string'))
    if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_FROM_NAME; ?>", form.from_name, 'string'))
    if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_FROM_EMAIL; ?>", form.from_email, 'email'))
    if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_SEND_BY; ?>", form.send_by, 'emptyint'))
    {
      return true;
    }
    return false;
  }
  <?php
}

if((isset($_GET['newsletterTabAdmin']) && $_GET['newsletterTabAdmin'] == 'tabNewsletterSubscriber')
  || (isset($_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin']) && $_SESSION['newsletter'][$_SESSION['ploopi']['moduleid']]['newsletterTabAdmin'] == 'tabNewsletterSubscriber'))
{
  if (!ploopi_ismoduleallowed('newsletter')) ploopi_init_module('newsletter',false,false,false);
  ?>
  function newsletter_subscrib_validate(form)
  {
    if (ploopi_validatefield("<?php echo _NEWSLETTER_LABEL_EMAIL; ?>", form.subscrib_email, 'email'))
      return true;

    return false;
  }
  <?php
}
?>
