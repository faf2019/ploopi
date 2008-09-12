<?
if(isset($_GET['op']) && ($_GET['op'] == 'mess_add' || $_GET['op'] == 'mess_edit' || $_GET['op'] == 'subject_add' || $_GET['op'] == 'subject_edit'))
{
  ploopi_init_module('forum');
  ?>
  function form_validate(form) 
  {
    var fck_instance = $('fck_forum_content___Frame').contentWindow.FCKeditorAPI.GetInstance('fck_forum_content');
    // get fckeditor content
    $('fck_forum_content').value = fck_instance.GetData(true);    
    <?
    if($_GET['op'] == 'subject_add' || $_GET['op'] == 'subject_edit')
      echo 'if (ploopi_validatefield(\''._FORUM_MESS_LABEL_TITLE.'\',form.forum_title,\'string\'))'."\r\n"; 
    ?>
    if (ploopi_validatefield('<?php echo _FORUM_MESS_LABEL_MESSAGE; ?>',form.fck_forum_content,'string'))
      return(true); 
    
    return(false);
  }
<?
}
?>
