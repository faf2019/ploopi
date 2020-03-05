<?php

if (ploopi\acl::isactionallowed([ploopi\news2\tools::ACTION_WRITE, ploopi\news2\tools::ACTION_MODIFY])) {

	ob_start();
	include_once './modules/news2/include/ck_link.php';
	$main_content = ob_get_contents();
	@ob_end_clean();

	$template_body->assign_vars(array(
		'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
		'ADDITIONAL_JAVASCRIPT' => $ploopi_additional_javascript,
		'PAGE_CONTENT'          => $main_content
		)
	);
	$template_body->assign_block_vars('module_css',
		array(
		    'PATH' => "./modules/webedit/include/styles.css"
		)
	);
	$template_body->assign_block_vars('module_css_ie',
		array(
		     'PATH' => "./modules/webedit/include/styles_ie.css"
		)
	);
	$template_body->pparse('body');
	ploopi\system::kill();

}

