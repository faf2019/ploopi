<?php
/**
 * Enregistre la catégorie
 */
use ploopi\crypt;
use ploopi\news2;

if (ploopi\acl::isactionallowed(news2\tools::ACTION_MANAGECAT)) {

	$newscat = new news2\news2cat();
    if (!empty($_GET['id'])) {
    	if (!is_numeric($_GET['id'])) 
			ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));
		if (!$newscat->open($_GET['id']) || $newscat->fields['id_module'] != $this->getModuleId()) 
			ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));
    } else {
		$newscat->setuwm();
	}
    // Propriétaire et cloisonnement ne viennent pas du formulaire
    $newscat->setvalues(array_diff_key($_POST, array_flip(array('newscat_id', 'newscat_id_module', 'newscat_id_workspace', 'newscat_id_user'))), 'newscat_');
	if (empty($newscat->fields['title'])) {
		echo "<script nonce=\"".ploopi\csp::nonce()."\">
		alert('La catégorie ne comporte pas de titre !');
		{document.location.href='".crypt::urlencode("admin.php?entity=error")."';}
		</script>"; 
		ploopi\system::kill();
	}
    $newscat->save();
	ploopi\user_action_log::record(news2\tools::ACTION_MANAGECAT, $newscat->fields['id']);
    ploopi\output::redirect(crypt::urlencode("admin.php?entity=admin&action=catmodify&id={$news->fields['id']}"));
}

ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));

