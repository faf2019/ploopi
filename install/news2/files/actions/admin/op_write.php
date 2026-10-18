<?php
/**
 * Enregistre la news
 */
use ploopi\crypt;
use ploopi\news2;
use ploopi\news2\tools;

if (ploopi\acl::isactionallowed([tools::ACTION_WRITE, tools::ACTION_MODIFY])) {

	$news = new news2\news2();
    if (!empty($_GET['id'])) {
    	if (!is_numeric($_GET['id'])) {
			ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));
		}
		// L'identifiant doit désigner une news de l'instance courante
		if (!$news->open($_GET['id']) || $news->fields['id_module'] != $this->getModuleId())  {
			ploopi\output::redirect(crypt::urlencode("admin.php?entity=admin"));
		}
    } else {
		$news->init_description();
		$news->setuwm();
	}
	
    // Propriétaire et cloisonnement ne viennent pas du formulaire
    $news->setvalues(array_diff_key($_POST, array_flip(array('news_id', 'news_id_module', 'news_id_workspace', 'news_id_user'))), 'news_');
	if (empty($news->fields['title'])) {
		echo "<script nonce=\"".ploopi\csp::nonce()."\">
		alert('La nouvelle ne comporte pas de titre !');
		{document.location.href='".crypt::urlencode("admin.php?entity=error")."';}
		</script>"; 
		ploopi\system::kill();
	}

    if (isset($_POST['fck_news_content'])) {
		$news->fields['content'] = $_POST['fck_news_content'];
	}
    if (isset($_POST['news_date_publish'])) 
		$news->fields['date_publish'] = 
			ploopi\date::local2timestamp(
				$_POST['news_date_publish'], 
				$_POST['newsx_time_publish'], 
				_PLOOPI_DATEFORMAT_US
			);
    $news->save();

    if ($news->new) 
		ploopi\user_action_log::record(tools::ACTION_WRITE, $news->fields['id']);
    else 
		ploopi\user_action_log::record(tools::ACTION_MODIFY, $news->fields['id']);
    ploopi\output::redirect(crypt::urlencode("admin.php?entity=admin&action=default&id={$news->fields['id']}"));
}

ploopi\output::redirect(crypt::urlencode("admin.php?entity=error"));

