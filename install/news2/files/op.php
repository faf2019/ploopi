<?php
/**
 * Opérations accessibles pour les utilisateurs connectés
 */

 if ($_SESSION['ploopi']['connected']) {
	switch($_REQUEST['ploopi_op']) {

		case 'news2_selectredirect':
			// $webeditid = $_SESSION['ploopi']['webeditmoduleid'];
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
				$treeview = ploopi\news2\tools::news2_gettreeview(webedit_getheadings($webeditid), webedit_getarticles($webeditid));
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
		break;
		
        case 'news2_detail_heading':
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
        break;

	}
}
