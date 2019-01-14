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

if (!file_exists("{$_SESSION['ploopi']['template_path']}/{$template_filename}") || ! is_readable("{$_SESSION['ploopi']['template_path']}/{$template_filename}")) {

    ploopi_die(
        str_replace(
            array('<FILE>', '<TEMPLATE>'),
            array($template_filename, $_SESSION['ploopi']['template_path']),
            _PLOOPI_ERROR_TEMPLATE_FILE
        )
    );

}

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
        'PATH' => './lib/protoaculous/protoaculous.min.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$template_body->assign_block_vars('ploopi_js',
    array(
        'PATH' => './js/functions.pack.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$template_body->assign_block_vars('ploopi_js',
    array(
        'PATH' => './lib/jscolor/jscolor.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
    )
);

$ploopi_additional_head = '';
$ploopi_additional_javascript = '';

ob_start();
include './include/javascript.php';
$ploopi_additional_javascript = ob_get_contents();
@ob_end_clean();

include_once './include/op.php';

if ($_SESSION['ploopi']['connected'])
{
    $template_body->assign_block_vars('switch_user_logged_in', array());

    // GET WORKSPACES
    foreach ($_SESSION['ploopi']['workspaces_allowed'] as $key)
    {
        $template_body->assign_block_vars('switch_user_logged_in.workspace',array(
                'TITLE' => ploopi_htmlentities($_SESSION['ploopi']['workspaces'][$key]['label']),
                'URL' => $key == $_SESSION['ploopi']['workspaceid'] && $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES && $_SESSION['ploopi']['moduleid'] != -1 ? ploopi_urlencode('admin.php') : ploopi_urlencode('admin.php?ploopi_switch_workspace', _PLOOPI_MENU_WORKSPACES, $key, '', ''),
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
                    'TITLE' => ploopi_htmlentities($mod['title']),
                    'URL' => $mod['url'],
                    'DESCRIPTION' => '',
                    'SELECTED' => ($idmod == $_SESSION['ploopi']['moduleid']) ? 'selected' : '',
                    'TYPE' => isset($mod['type']) ? $mod['type'] : ''
            ));

            if (!empty($mod['content']))
            {
                $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block.switch_content',array(
                        'CONTENT' => $mod['content']
                    )
                );
            }

            if (isset($mod['menu']))
            {
                if ($idmod == $_SESSION['ploopi']['moduleid']) // Module sélectionné
                {
                    $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_blocksel',array(
                            'ID' => $idmod,
                            'TITLE' => ploopi_htmlentities($mod['title']),
                            'URL' => $mod['url'],
                            'DESCRIPTION' => '',
                            'TYPE' => isset($mod['type']) ? $mod['type'] : ''
                    ));

                    if (!empty($mod['content']))
                    {
                        $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_blocksel.switch_content',array(
                                'CONTENT' => $mod['content']
                            )
                        );
                    }
                }


                foreach($mod['menu'] as $menu)
                {
                    if ($idmod == $_SESSION['ploopi']['moduleid']) // Module sélectionné
                    {
                        $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_blocksel.menu',array(
                                'LABEL' => $menu['label'],
                                'CLEANED_LABEL' => $menu['cleaned_label'],
                                'URL' => $menu['url'],
                                'SELECTED' => (!empty($menu['selected']) && $menu['selected']) ? 'selected' : '',
                                'TARGET' => (!empty($menu['target'])) ? $menu['target'] : ''
                            )
                        );
                    }

                    $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block.menu',array(
                            'LABEL' => $menu['label'],
                            'CLEANED_LABEL' => $menu['cleaned_label'],
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

        'USER_LOGIN'            => ploopi_htmlentities($_SESSION['ploopi']['login']),
        'USER_PASSWORD'         => ploopi_htmlentities($_SESSION['ploopi']['password']),
        'USER_FIRSTNAME'        => ploopi_htmlentities($_SESSION['ploopi']['user']['firstname']),
        'USER_LASTNAME'         => ploopi_htmlentities($_SESSION['ploopi']['user']['lastname']),
        'USER_EMAIL'            => ploopi_htmlentities($_SESSION['ploopi']['user']['email']),

        'USER_WORKSPACE_LABEL'  => ploopi_htmlentities(_PLOOPI_LABEL_MYWORKSPACE),
        'USER_WORKSPACE_URL'    => ploopi_urlencode("admin.php", _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'USER_WORKSPACE_SEL'    => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE) ? 'selected' : '',

        'MAINMENU_PROFILE'          => _PLOOPI_LABEL_MYPROFILE,
        'MAINMENU_ANNOTATIONS'      => _PLOOPI_LABEL_MYANNOTATIONS,
        'MAINMENU_TICKETS'          => _PLOOPI_LABEL_MYTICKETS,
        'MAINMENU_SEARCH'           => _PLOOPI_LABEL_SEARCH,
        'MAINMENU_DISCONNECTION'    => _PLOOPI_LABEL_DISCONNECTION,

        'POPUP_PROFLE'              => ploopi_queryencode("ploopi_op=system_update_profile"),

        'MAINMENU_SHOWPROFILE_URL'      => ploopi_urlencode('admin.php?op=profile', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWANNOTATIONS_URL'  => ploopi_urlencode('admin.php?op=annotation', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWTICKETS_URL'      => ploopi_urlencode('admin.php?op=tickets', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWDATA_URL'         => ploopi_urlencode('admin.php?op=actions', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWSEARCH_URL'       => ploopi_urlencode('admin.php?op=search', _PLOOPI_MENU_WORKSPACES, null, _PLOOPI_MODULE_SEARCH, 'public'),

        'MAINMENU_SHOWPROFILE_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'profile') ? 'selected' : '',
        'MAINMENU_SHOWANNOTATIONS_SEL'  => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'annotation') ? 'selected' : '',
        'MAINMENU_SHOWTICKETS_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'tickets') ? 'selected' : '',
        'MAINMENU_SHOWTICKETS_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'actions') ? 'selected' : '',
        'MAINMENU_SHOWSEARCH_SEL'       => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES) ? 'selected' : '',

        'SEARCH_KEYWORDS'               => (!empty($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'])) ? ploopi_htmlentities($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords']) : '',

        'NEWTICKETS'                => $newtickets,
        'LAST_NEWTICKET'            => $lastticket,
        'SHOW_BLOCKMENU'            => (!empty($_SESSION['ploopi']['switchdisplay']['block_modules'])) ? $_SESSION['ploopi']['switchdisplay']['block_modules'] : 'block',

        'USER_DECONNECT'        => ploopi_urlencode("admin.php?ploopi_logout", null, null, null, null, false)
    ));

    if ($newtickets) $template_body->assign_block_vars('switch_user_logged_in.switch_newtickets', array());

    if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES)
    {
        $template_body->assign_block_vars('switch_user_logged_in.switch_search', array());
        $template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_search', array());
    }

}
else
{
    $template_body->assign_block_vars('switch_user_logged_out', array(
        'FORM_URL' => ploopi_urlencode('admin.php')
    ));

    if (!empty($_SESSION['ploopi']['errorcode']))
    {
        $template_body->assign_block_vars('switch_user_logged_out.switch_ploopierrormsg', array());

        // Cas particulier : demande de changement de mot de passe
        if (in_array($_SESSION['ploopi']['errorcode'], array( _PLOOPI_ERROR_PASSWORDRESET, _PLOOPI_ERROR_PASSWORDERROR, _PLOOPI_ERROR_PASSWORDINVALID)))
        {
            $template_body->assign_block_vars('switch_user_logged_out.switch_passwordreset', array());

            if (_PLOOPI_USE_COMPLEXE_PASSWORD) {
                $template_body->assign_block_vars('switch_user_logged_out.switch_passwordreset.switch_cp', array('MIN_SIZE' => _PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE));
            } else {
                $template_body->assign_block_vars('switch_user_logged_out.switch_passwordreset.switch_np', array());
            }

            $template_body->assign_vars(array(
                'USER_LOGIN'            => ploopi_htmlentities($_SESSION['ploopi']['login']),
                'USER_PASSWORD'         => ploopi_htmlentities($_SESSION['ploopi']['password'])
            ));
        }
    }

    if (!empty($_SESSION['ploopi']['msgcode']))
    {
        $template_body->assign_block_vars('switch_user_logged_out.switch_ploopimsg', array());
    }

    $template_body->assign_vars(array(
        'PASSWORDLOST_URL'              => ploopi_urlencode('admin.php?ploopi_op=ploopi_lostpassword')
        )
    );

}

/**
 * Gestion du cas où on demande à l'utilisateur de compléter son profil
 */

if (!empty($_SESSION['ploopi']['updateprofile']) && ploopi_getparam('system_profile_edit_allowed', _PLOOPI_MODULE_SYSTEM) == '1') {
    $ploopi_additional_javascript .= "
        Event.observe(window, 'load', function() {
            ploopi_showpopup('', 750, null, true, 'system_popup_update_profile')
            ploopi_xmlhttprequest_todiv('admin-light.php', '".ploopi_queryencode("ploopi_op=system_update_profile")."', 'system_popup_update_profile');
        });
    ";

    $_SESSION['ploopi']['updateprofile'] = false;
}

$wsp = ploopi_loader::getworkspace();

$template_body->assign_vars(array(
    'TEMPLATE_PATH'                 => $_SESSION['ploopi']['template_path'],
    'TEMPLATE_NAME'                 => $_SESSION['ploopi']['template_name'],
    'WORKSPACE_LABEL'               => $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE ? ploopi_htmlentities(_PLOOPI_LABEL_MYWORKSPACE) : ploopi_htmlentities($wsp['label']),
    'WORKSPACE_CODE'                => ploopi_htmlentities($wsp['code']),
    'WORKSPACE_TITLE'               => ploopi_htmlentities($wsp['title']),
    'WORKSPACE_META_DESCRIPTION'    => ploopi_htmlentities($wsp['meta_description']),
    'WORKSPACE_META_KEYWORDS'       => ploopi_htmlentities($wsp['meta_keywords']),
    'WORKSPACE_META_AUTHOR'         => ploopi_htmlentities($wsp['meta_author']),
    'WORKSPACE_META_COPYRIGHT'      => ploopi_htmlentities($wsp['meta_copyright']),
    'WORKSPACE_META_ROBOTS'         => ploopi_htmlentities($wsp['meta_robots']),
    'SITE_CONNECTEDUSERS'           => $_SESSION['ploopi']['connectedusers'],
    'SITE_ANONYMOUSUSERS'           => $_SESSION['ploopi']['anonymoususers'],
    'ADDITIONAL_JAVASCRIPT'         => $ploopi_additional_javascript,
    'PLOOPI_ERROR'                  => (!empty($_SESSION['ploopi']['errorcode']) && isset($ploopi_errormsg[$_SESSION['ploopi']['errorcode']])) ? $ploopi_errormsg[$_SESSION['ploopi']['errorcode']] : '',
    'PLOOPI_MSG'                    => (!empty($_SESSION['ploopi']['msgcode']) && isset($ploopi_msg[$_SESSION['ploopi']['msgcode']])) ? $ploopi_msg[$_SESSION['ploopi']['msgcode']] : '',
    'PLOOPI_VERSION'                => _PLOOPI_VERSION,
    'PLOOPI_REVISION'               => _PLOOPI_REVISION
));

// Message "ok" envoyé par le module
if(isset($_GET['ploopi_mod_msg']) && defined($_GET['ploopi_mod_msg']))
{
    $template_body->assign_block_vars('switch_mod_message',array(
        'MSG'       => constant($_GET['ploopi_mod_msg']),
        'MSG4JS'    => addslashes(constant($_GET['ploopi_mod_msg'])),
        'MSG_ID'    => uniqid('ploopi_mod_mess_'),
        'MSG_CLASS' => 'ploopi_mod_mess_ok'
        )
    );
}

// Message "erreur" envoyé par le module
if(isset($_GET['ploopi_mod_error']) && defined($_GET['ploopi_mod_error']))
{
    $template_body->assign_block_vars('switch_mod_message',array(
        'MSG'       => constant($_GET['ploopi_mod_error']),
        'MSG4JS'    => addslashes(constant($_GET['ploopi_mod_error'])),
        'MSG_ID'    => uniqid('ploopi_mod_error_'),
        'MSG_CLASS' => 'ploopi_mod_mess_error'
        )
    );
}

unset($_SESSION['ploopi']['errorcode']);

$template_body->pparse('body');
