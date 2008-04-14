<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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

switch($_REQUEST['ploopi_op'])
{
    case 'webedit_selectlink':
    case 'webedit_detail_heading';
        ob_start();
        include_once './modules/webedit/fck_link.php';
        $main_content = ob_get_contents();
        @ob_end_clean();

        $template_body->assign_vars(array(
            'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
            'ADDITIONAL_JAVASCRIPT' => $additional_javascript,
            'PAGE_CONTENT'          => $main_content
            )
        );

        $template_body->assign_block_vars('module_css',array(
                                                    'PATH' => "./modules/webedit/include/styles.css"
                                                )
                                        );

        $template_body->assign_block_vars('module_css_ie',array(
                                                    'PATH' => "./modules/webedit/include/styles_ie.css"
                                                )
                                        );


        $template_body->pparse('body');
        ploopi_die();
    break;


    case 'webedit_getbackup':
        include_once './modules/webedit/class_article_backup.php';

        $article_backup = new webedit_article_backup();
        if (!empty($_GET['backup_id_article']) && !empty($_GET['backup_timestp']) && is_numeric($_GET['backup_id_article']) && is_numeric($_GET['backup_timestp']) && $article_backup->open($_GET['backup_id_article'],$_GET['backup_timestp']))
        {
            echo $article_backup->fields['content'];
        }
        ploopi_die();
    break;
}
