<?php
/**
 * Enregistre la catégorie
 */
use ploopi\news2;

if (ploopi\acl::isactionallowed(news2\tools::ACTION_MANAGECAT)) {

	$newscat = new news2\news2cat();
    if (!empty($_GET['id'])) {
    	if (!is_numeric($_GET['id'])) 
			ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));
		if (!$newscat->open($_GET['id'])) 
			ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));
    } else {
		$newscat->setuwm();
	}
    $newscat->setvalues($_POST,'newscat_');
	if (empty($newscat->fields['title'])) {
		echo "<script>
		alert('La catégorie ne comporte pas de titre !');
		{document.location.href='".ploopi\crypt::urlencode("admin.php?entity=error")."';}
		</script>"; 
		ploopi\system::kill();
	}
    $newscat->save();
	ploopi\user_action_log::record(news2\tools::ACTION_MANAGECAT, $newscat->fields['id']);
    ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=admin&action=catmodify&id={$news->fields['id']}"));
}

ploopi\output::redirect(ploopi\crypt::urlencode("admin.php?entity=error"));

