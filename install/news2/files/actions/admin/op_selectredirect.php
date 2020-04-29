<?php
use ploopi\news2;

if (ploopi\acl::isactionallowed([news2\tools::ACTION_WRITE, news2\tools::ACTION_MODIFY])) {

	$webeditid = 0;
	foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $idm) {
		if (!empty($_SESSION['ploopi']['modules'][$idm]['active']) && $_SESSION['ploopi']['modules'][$idm]['moduletype'] == 'webedit') $webeditid = $idm;
	}
	ob_start();
	ploopi\module::init('webedit');
	?>
	<div style="padding:4px;height:150px;overflow:auto;">
	<?php
	if ($webeditid > 0) {
		$treeview = news2\tools::news2_gettreeview(webedit_getheadings($webeditid), webedit_getarticles($webeditid));
		echo ploopi\skin::get()->display_treeview($treeview['list'], $treeview['tree'],$webeditid);
	} else {
		echo "Aucun module WEBEDIT disponible"; 
	}
	?>
	</div>
	<?php
	$content = ob_get_contents();
	ob_end_clean();

	echo ploopi\skin::get()->create_popup('Choix d\'une page', $content, 'news2_popup_selectredirect');
	ploopi\system::kill();
		
}

