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

/**
 * Initialisation du rendu backoffice. 
 * Initialisation du moteur de template.
 * Initialisation du moteur de skin.
 * Définition des variables templates générales.
 * Insertion des blocs.
 * Inclusions JS/CSS.
 * Appel du module.
 *  
 * @package ploopi
 * @subpackage backoffice
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * inclusion des classes pour le moteur de template 
 */

include_once './include/classes/template.php';
include_once './lib/template/template.php';

/**
 * Chargement de la classe skin liée au template.
 * Initialisation du template
 */

include_once "{$_SESSION['ploopi']['template_path']}/class_skin.php";
$skin = new skin();

$template_body = new Template($_SESSION['ploopi']['template_path']);

$template_filename = 'index.tpl';
 
if (!empty($_GET['ploopi_tpl']) && file_exists("{$_SESSION['ploopi']['template_path']}/{$_GET['ploopi_tpl']}.tpl")) $template_filename = "{$_GET['ploopi_tpl']}.tpl";
else if (isset($_SESSION['ploopi']['remote_pda']) && $_SESSION['ploopi']['remote_pda'] && file_exists("{$_SESSION['ploopi']['template_path']}/pda.tpl")) $template_filename = 'pda.tpl';

$template_body->set_filenames(
    array(
        'body' => $template_filename
    )
);

/* INCLUSION JS
 * inclusion des scripts JS
 * */

$template_body->assign_block_vars('ploopi_js', 
    array(
        'PATH' => './lib/protoculous/protoculous-packer.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$template_body->assign_block_vars('ploopi_js', 
    array(
        'PATH' => './js/functions.pack.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$template_body->assign_block_vars('ploopi_js', 
    array(
        'PATH' => './js/colorpicker.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$ploopi_additional_head = '';
$ploopi_additional_javascript = '';

ob_start();
include './include/javascript.php';
$ploopi_additional_javascript = ob_get_contents();
@ob_end_clean();

if ($_SESSION['ploopi']['connected'])
{
    include_once './include/op.php';
    
    $template_body->assign_block_vars('switch_user_logged_in', array());

    // GET WORKSPACES
    foreach ($_SESSION['ploopi']['workspaces_allowed'] as $key)
    {
        $template_body->assign_block_vars('switch_user_logged_in.workspace',array(
                                            'TITLE' => $_SESSION['ploopi']['workspaces'][$key]['label'],
                                            'URL' => ploopi_urlencode('admin.php', _PLOOPI_MENU_WORKSPACES, $key, '', ''),
                                            'SELECTED' => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES && $key == $_SESSION['ploopi']['workspaceid']) ? 'selected' : ''
                                            )
                                    );
    }

    // GET BLOCKS
    include_once './include/blocks.php';

    if (!empty($arrBlocks) || $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES)
    {
        $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu',array());
    }
    
    if (!empty($arrBlocks))
    {

        foreach($arrBlocks as $idmod => $mod)
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
    
    ob_start();
    
    if (!empty($_SESSION['ploopi']['moduletype']))
    {
        if ($_SESSION['ploopi']['action'] == 'admin')
        {
            if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/admin.php")) include_once "./modules/{$_SESSION['ploopi']['moduletype']}/admin.php";
        }
        else
        {
            if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/public.php")) include_once "./modules/{$_SESSION['ploopi']['moduletype']}/public.php";
        }

    }
    
    $page_content = ob_get_contents();
    ob_end_clean();
    
    list($newtickets, $lastticket) = ploopi_tickets_getnew();

    $template_body->assign_vars(array(
        'PAGE_CONTENT'          => $page_content,
        'ADDITIONAL_HEAD'       => $ploopi_additional_head,

        'USER_LOGIN'            => $_SESSION['ploopi']['login'],
        'USER_FIRSTNAME'        => $_SESSION['ploopi']['user']['firstname'],
        'USER_LASTNAME'         => $_SESSION['ploopi']['user']['lastname'],
        'USER_EMAIL'            => $_SESSION['ploopi']['user']['email'],
    
        'USER_WORKSPACE'        => ploopi_urlencode("admin.php?ploopi_mainmenu="._PLOOPI_MENU_MYWORKSPACE),
        'USER_WORKSPACE_SEL'    => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE) ? 'selected' : '',
    
        'MAINMENU_PROFILE'          => _PLOOPI_LABEL_MYPROFILE,
        'MAINMENU_ANNOTATIONS'      => _PLOOPI_LABEL_MYANNOTATIONS,
        'MAINMENU_TICKETS'          => _PLOOPI_LABEL_MYTICKETS,
        'MAINMENU_SEARCH'           => _PLOOPI_LABEL_SEARCH,
        'MAINMENU_DISCONNECTION'    => _PLOOPI_LABEL_DISCONNECTION,
    
    
        'MAINMENU_SHOWPROFILE_URL'      => ploopi_urlencode('admin.php?op=profile', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWANNOTATIONS_URL'  => ploopi_urlencode('admin.php?op=annotation', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWTICKETS_URL'      => ploopi_urlencode('admin.php?op=tickets', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWSEARCH_URL'       => ploopi_urlencode('admin.php?op=search', _PLOOPI_MENU_WORKSPACES, 0, _PLOOPI_MODULE_SYSTEM, 'public'),


        'MAINMENU_SHOWPROFILE_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'profile') ? 'selected' : '',
        'MAINMENU_SHOWANNOTATIONS_SEL'  => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'annotation') ? 'selected' : '',
        'MAINMENU_SHOWTICKETS_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'tickets') ? 'selected' : '',
        'MAINMENU_SHOWSEARCH_SEL'       => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES) ? 'selected' : '',

        'SEARCH_KEYWORDS'               => (!empty($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'])) ? $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] : '',

        'NEWTICKETS'                => $newtickets,
        'LAST_NEWTICKET'            => $lastticket,
        'SHOW_BLOCKMENU'            => (!empty($_SESSION['ploopi']['switchdisplay']['block_modules'])) ? $_SESSION['ploopi']['switchdisplay']['block_modules'] : 'block',

        'USER_DECONNECT'        => ploopi_urlencode("admin.php?ploopi_logout")
        )
    );

    if ($newtickets) $template_body->assign_block_vars('switch_user_logged_in.switch_newtickets', array());
    
    if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES) 
    {
        $template_body->assign_block_vars('switch_user_logged_in.switch_search', array());
        $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_search', array());
    }
    
}
else
{
    $template_body->assign_block_vars('switch_user_logged_out', array());
    if (!empty($_GET['ploopi_errorcode']))
    {
        $template_body->assign_block_vars('switch_user_logged_out.switch_ploopierrormsg', array());
    }

    if (!empty($_REQUEST['op']) && $_REQUEST['op'] == 'ploopi_passwordlost') 
    {
            // A DEVELOPPER (RENOUVELLEMENT DE MOT DE PASSE)
    }

    $template_body->assign_vars(array(
        'PASSWORDLOST_URL'              => ploopi_urlencode('admin.php?op=ploopi_passwordlost')
        )
    );
    
}


$template_body->assign_vars(array(
    'TEMPLATE_PATH'                 => $_SESSION['ploopi']['template_path'],
    'TEMPLATE_NAME'                 => $_SESSION['ploopi']['template_name'],
    'WORKSPACE_LABEL'               => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label']),
    'WORKSPACE_CODE'                => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['code']),
    'WORKSPACE_TITLE'               => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['title']),
    'WORKSPACE_META_DESCRIPTION'    => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_description']),
    'WORKSPACE_META_KEYWORDS'       => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_keywords']),
    'WORKSPACE_META_AUTHOR'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_author']),
    'WORKSPACE_META_COPYRIGHT'      => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_copyright']),
    'WORKSPACE_META_ROBOTS'         => htmlentities($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['meta_robots']),
    'SITE_CONNECTEDUSERS'           => $_SESSION['ploopi']['connectedusers'],
    'SITE_ANONYMOUSUSERS'           => $_SESSION['ploopi']['anonymoususers'],
    'ADDITIONAL_JAVASCRIPT'         => $ploopi_additional_javascript,
    'PLOOPI_ERROR'                  => (!empty($_GET['ploopi_errorcode'])) ? $ploopi_errormsg[$_GET['ploopi_errorcode']] : '',
    'PLOOPI_VERSION'                => _PLOOPI_VERSION
    )
);


$template_body->pparse('body');
?>
