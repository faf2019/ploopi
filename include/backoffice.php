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

include_once './include/classes/class_block.php';
include_once './lib/template/template.php';

/* TEMPLATE / SKIN
 * Chargement de la classe skin liée au template
 * Initialisation du template
 * */

include_once "{$_SESSION['ploopi']['template_path']}/class_skin.php";
$skin = new skin();

$template_body = new Template($_SESSION['ploopi']['template_path']);

if (!empty($_GET['ploopi_tpl']) && file_exists("{$_SESSION['ploopi']['template_path']}/{$_GET['ploopi_tpl']}.tpl")) $template_body->set_filenames(array('body' => "{$_GET['ploopi_tpl']}.tpl"));
else
{
    if ($_SESSION['ploopi']['browser']['pda']) $template_body->set_filenames(array('body' => 'pda.tpl'));
    else $template_body->set_filenames(array('body' => 'index.tpl'));
}

/* INCLUSION JS
 * inclusion des scripts JS
 * */

$template_body->assign_block_vars('ploopi_js',array('PATH' => "./lib/prototype/prototype.pack.js"));
$template_body->assign_block_vars('ploopi_js',array('PATH' => "./js/functions.pack.js"));

ob_start();
include('./include/javascript.php');
$additional_javascript = ob_get_contents();
@ob_end_clean();


if ($_SESSION['ploopi']['connected'])
{
    // GET MODULE ADDITIONAL HEAD
    ob_start();
    if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/include/head.php")) include("./modules/{$_SESSION['ploopi']['moduletype']}/include/head.php");
    $additional_head = ob_get_contents();
    @ob_end_clean();

    include_once './include/op.php';

    $template_body->assign_block_vars('switch_user_logged_in', array());


    // GET WORKSPACES
    foreach ($_SESSION['ploopi']['workspaces_allowed'] as $key)
    {
        $template_body->assign_block_vars('switch_user_logged_in.workspace',array(
                                            'TITLE' => $_SESSION['ploopi']['workspaces'][$key]['label'],
                                            'URL' => ploopi_urlencode("{$scriptenv}?ploopi_workspaceid={$key}"),
                                            'SELECTED' => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYGROUPS && $key == $_SESSION['ploopi']['workspaceid']) ? 'selected' : ''
                                            )
                                    );
    }


    // GET BLOCKS
    include_once('./include/blocks.php');

    foreach($arrModules as $modtype)
    {
        // GET ADDITIONAL JS
        if (file_exists("./modules/{$modtype}/include/javascript.php"))
        {
            ob_start();
            include("./modules/{$modtype}/include/javascript.php");
            $additional_javascript .= ob_get_contents();
            @ob_end_clean();
        }

        if (file_exists("./modules/{$modtype}/include/styles.css"))
        {
            $template_body->assign_block_vars('module_css',array(
                                                        'PATH' => "./modules/{$modtype}/include/styles.css"
                                                    )
                                            );
        }

        if (file_exists("./modules/{$modtype}/include/styles_ie.css"))
        {
            $template_body->assign_block_vars('module_css_ie',array(
                                                        'PATH' => "./modules/{$modtype}/include/styles_ie.css"
                                                    )
                                            );
        }

        if (file_exists("./modules/{$modtype}/include/functions.js"))
        {
            $template_body->assign_block_vars('module_js',array(
                                                        'PATH' => "./modules/{$modtype}/include/functions.js"
                                                    )
                                            );
        }
    }


    if (!empty($arrBlock))
    {
        $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu',array());

        foreach($arrBlock as $blocktype => $blockmod)
        {
            foreach($blockmod as $idmod => $mod)
            {
                if (empty($mod['url'])) $mod['url'] = '';

                // CAS 1 : liste standard de modules
                $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block',array(
                                                'ID' => $idmod,
                                                'TITLE' => $mod['title'],
                                                'URL' => $mod['url'],
                                                'DESCRIPTION' => '',
                                                'SELECTED' => ($idmod == $_SESSION['ploopi']['moduleid']) ? 'selected' : ''
                                                )
                                        );
                if (!empty($mod['content']))
                {
                    $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block.switch_content',array(
                                                        'CONTENT' => $mod['content']
                                                        )
                                                );
                }


                if (isset($mod['menu']))
                {
                    foreach($mod['menu'] as $menu)
                    {
                        $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block.menu',array(
                                                        'LABEL' => $menu['label'],
                                                        'URL' => $menu['url'],
                                                        'SELECTED' => (!empty($menu['selected']) && $menu['selected']) ? 'selected' : '',
                                                        'TARGET' => (!empty($menu['target'])) ? $menu['target'] : ''
                                                        )
                                            );
                    }
                }
            }
        }
    }

    ob_start();
    if (!empty($_SESSION['ploopi']['moduletype']))
    {
        if ($_SESSION['ploopi']['action'] == 'admin')
        {
            if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/admin.php")) include_once("./modules/{$_SESSION['ploopi']['moduletype']}/admin.php");
        }
        else
        {
            if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/public.php")) include_once("./modules/{$_SESSION['ploopi']['moduletype']}/public.php");
        }

    }
    $page_content = ob_get_contents();
    @ob_end_clean();

    $template_body->assign_vars(array(
        'PAGE_CONTENT'          => $page_content,
        'ADDITIONAL_HEAD'       => $additional_head,

        'USER_LOGIN'            => $_SESSION['ploopi']['login'],
        'USER_FIRSTNAME'        => $_SESSION['ploopi']['user']['firstname'],
        'USER_LASTNAME'         => $_SESSION['ploopi']['user']['lastname'],
        'USER_EMAIL'            => $_SESSION['ploopi']['user']['email'],

        'MAINMENU_SHOWPROFILE_URL'      => ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_PROFILE),
        'MAINMENU_SHOWANNOTATIONS_URL'  => ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_ANNOTATIONS),
        'MAINMENU_SHOWTICKETS_URL'      => ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_TICKETS),
        'MAINMENU_SHOWSEARCH_URL'       => ploopi_urlencode("{$scriptenv}?ploopi_mainmenu="._PLOOPI_MENU_SEARCH),

        'MAINMENU_SHOWPROFILE_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_PROFILE) ? 'selected' : '',
        'MAINMENU_SHOWANNOTATIONS_SEL'  => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_ANNOTATIONS) ? 'selected' : '',
        'MAINMENU_SHOWTICKETS_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_TICKETS) ? 'selected' : '',
        'MAINMENU_SHOWSEARCH_SEL'       => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_SEARCH) ? 'selected' : '',

        'SEARCH_KEYWORDS'               => (!empty($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'])) ? $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] : '',

        'NEWTICKETS'            => $_SESSION['ploopi']['newtickets'],
        'SHOW_BLOCKMENU'            => (!empty($_SESSION['ploopi']['switchdisplay']['block_modules'])) ? $_SESSION['ploopi']['switchdisplay']['block_modules'] : 'block',

        'USER_DECONNECT'        => ploopi_urlencode("$scriptenv?ploopi_logout")
        )
    );

    if ($_SESSION['ploopi']['newtickets']) $template_body->assign_block_vars('switch_user_logged_in.sw_newtickets', array());
}
else
{
    $template_body->assign_block_vars('switch_user_logged_out', array());
    if (!empty($_GET['ploopi_errorcode']))
    {
        $template_body->assign_block_vars('switch_user_logged_out.switch_ploopierrormsg', array());
    }
}



$template_body->assign_vars(array(
    'TEMPLATE_PATH'                 => $_SESSION['ploopi']['template_path'],
    'WORKSPACE_LABEL'               => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label']),
    'WORKSPACE_CODE'                => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['code']),
    'WORKSPACE_TITLE'               => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']),
    'WORKSPACE_META_DESCRIPTION'    => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_description']),
    'WORKSPACE_META_KEYWORDS'       => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_keywords']),
    'WORKSPACE_META_AUTHOR'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_author']),
    'WORKSPACE_META_COPYRIGHT'      => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_copyright']),
    'WORKSPACE_META_ROBOTS'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_robots']),
    'SITE_CONNECTEDUSERS'           => $_SESSION['ploopi']['connectedusers'],
    'ADDITIONAL_JAVASCRIPT'         => $additional_javascript,
    'PLOOPI_ERROR'                  => (!empty($_GET['ploopi_errorcode'])) ? $ploopi_errormsg[$_GET['ploopi_errorcode']] : '',
    'PLOOPI_VERSION'                    => _PLOOPI_VERSION
    )
);


$template_body->pparse('body');

$ploopi_content = trim(ob_get_contents());

// clean main buffer
ob_end_clean();
session_write_close();

include_once './include/stats.php';

$array_tags = array(    '<PLOOPI_PAGE_SIZE>',
                        '<PLOOPI_EXEC_TIME>',
                        '<PLOOPI_PHP_P100>',
                        '<PLOOPI_SQL_P100>',
                        '<PLOOPI_NUMQUERIES>',
                        '<PLOOPI_SESSION_SIZE>'
                    );

$array_values = array(  sprintf("%.02f",$ploopi_stats['pagesize']/1024),
                        $ploopi_stats['total_exectime'],
                        $ploopi_stats['php_ratiotime'],
                        $ploopi_stats['sql_ratiotime'],
                        $ploopi_stats['numqueries'],
                        sprintf("%.02f",$ploopi_stats['sessionsize']/1024)
                    );

$ploopi_content = str_replace($array_tags, $array_values, $ploopi_content);

// main buffer flushing
echo $ploopi_content;
?>
