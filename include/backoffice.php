<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * inclusion des classes pour le moteur de template
 */

include_once './lib/template/template.php';

$template_filename = 'index.tpl';

if (!empty($_GET['ploopi_tpl']) && file_exists("{$_SESSION['ploopi']['template_path']}/{$_GET['ploopi_tpl']}.tpl")) $template_filename = "{$_GET['ploopi_tpl']}.tpl";

if (!file_exists("{$_SESSION['ploopi']['template_path']}/{$template_filename}") || ! is_readable("{$_SESSION['ploopi']['template_path']}/{$template_filename}")) {

    $_SESSION['ploopi']['template_path'] = './templates/backoffice/ploopi2';

    if (!file_exists("{$_SESSION['ploopi']['template_path']}/{$template_filename}") || ! is_readable("{$_SESSION['ploopi']['template_path']}/{$template_filename}")) {
        ploopi\system::kill(
            str_replace(
                array('<FILE>', '<TEMPLATE>'),
                array($template_filename, $_SESSION['ploopi']['template_path']),
                _PLOOPI_ERROR_TEMPLATE_FILE
            )
        );
    }

}

// $template_body =
self::$template_body = new \Template($_SESSION['ploopi']['template_path']);

self::$template_body->set_filenames(
    array(
        'body' => $template_filename
    )
);

/* INCLUSION JS
 * inclusion des scripts JS
 * */

self::$template_body->assign_block_vars('ploopi_js', array(
    'PATH' => './vendor/components/jquery/jquery.min.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
));

self::$template_body->assign_block_vars('ploopi_js', array(
    'PATH' => './vendor/components/jqueryui/jquery-ui.min.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
));


self::$template_body->assign_block_vars('ploopi_js', array(
    'PATH' => './js/functions.pack.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
));

self::$template_body->assign_block_vars('ploopi_js', array(
        'PATH' => './vendor/eastdesire/jscolor/jscolor.js?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)
));

$ploopi_additional_head = '';
$ploopi_additional_javascript = '';

ob_start();
include './include/javascript.php';
$ploopi_additional_javascript = ob_get_contents();
@ob_end_clean();

include_once './include/op.php';

if ($_SESSION['ploopi']['connected'])
{
    self::$template_body->assign_block_vars('switch_user_logged_in', array());

    // GET WORKSPACES
    foreach ($_SESSION['ploopi']['workspaces_allowed'] as $key)
    {
        self::$template_body->assign_block_vars('switch_user_logged_in.workspace',array(
                'TITLE' => ploopi\str::htmlentities($_SESSION['ploopi']['workspaces'][$key]['label']),
                'URL' => $key == $_SESSION['ploopi']['workspaceid'] && $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES && $_SESSION['ploopi']['moduleid'] != -1 ? ploopi\crypt::urlencode('admin.php') : ploopi\crypt::urlencode('admin.php?ploopi_switch_workspace', _PLOOPI_MENU_WORKSPACES, $key, '', ''),
                'SELECTED' => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES && $key == $_SESSION['ploopi']['workspaceid']) ? 'selected' : ''
            )
        );
    }

    // GET BLOCKS
    include_once './include/blocks.php';

    if (!empty($arrBlocks) || $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES)
    {
        self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu',array());
    }

    if (!empty($arrBlocks))
    {
        foreach($arrBlocks as $idmod => $mod)
        {
            if (empty($mod['url'])) $mod['url'] = '';

            // CAS 1 : liste standard de modules
            self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block',array(
                    'ID' => $idmod,
                    'TITLE' => ploopi\str::htmlentities($mod['title']),
                    'URL' => $mod['url'],
                    'DESCRIPTION' => '',
                    'SELECTED' => ($idmod == $_SESSION['ploopi']['moduleid']) ? 'selected' : ''
                )
            );

            if (!empty($mod['content']))
            {
                self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block.switch_content',array(
                        'CONTENT' => $mod['content']
                    )
                );
            }

            if (isset($mod['menu']))
            {
                if ($idmod == $_SESSION['ploopi']['moduleid']) // Module sélectionné
                {
                    self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_blocksel',array(
                            'ID' => $idmod,
                            'TITLE' => ploopi\str::htmlentities($mod['title']),
                            'URL' => $mod['url'],
                            'DESCRIPTION' => ''
                        )
                    );

                    if (!empty($mod['content']))
                    {
                        self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_blocksel.switch_content',array(
                                'CONTENT' => $mod['content']
                            )
                        );
                    }
                }


                foreach($mod['menu'] as $menu)
                {
                    if ($idmod == $_SESSION['ploopi']['moduleid']) // Module sélectionné
                    {
                        self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_blocksel.menu',array(
                                'LABEL' => $menu['label'],
                                'CLEANED_LABEL' => $menu['cleaned_label'],
                                'URL' => $menu['url'],
                                'SELECTED' => (!empty($menu['selected']) && $menu['selected']) ? 'selected' : '',
                                'TARGET' => (!empty($menu['target'])) ? $menu['target'] : ''
                            )
                        );
                    }

                    self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block.menu',array(
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
        $strControllerFile = "ploopi\\{$_SESSION['ploopi']['moduletype']}\\controller";
        if (ploopi\loader::classExists($strControllerFile)) {
            $strControllerFile::get()->dispatch();
        }
        else {
            // Rétrocompatibilité
            if ($_SESSION['ploopi']['action'] == 'admin')
            {
                if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/admin.php")) include_once "./modules/{$_SESSION['ploopi']['moduletype']}/admin.php";
            }
            else
            {
                if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/public.php")) include_once "./modules/{$_SESSION['ploopi']['moduletype']}/public.php";
            }
        }

    }

    $page_content = ob_get_contents();
    ob_end_clean();

    list($newtickets, $lastticket) = ploopi\ticket::getnew();

    self::$template_body->assign_vars(array(
        'PAGE_CONTENT'          => $page_content,
        'ADDITIONAL_HEAD'       => $ploopi_additional_head,

        'USER_LOGIN'            => ploopi\str::htmlentities($_SESSION['ploopi']['login']),
        'USER_PASSWORD'         => ploopi\str::htmlentities($_SESSION['ploopi']['password']),
        'USER_FIRSTNAME'        => ploopi\str::htmlentities($_SESSION['ploopi']['user']['firstname']),
        'USER_LASTNAME'         => ploopi\str::htmlentities($_SESSION['ploopi']['user']['lastname']),
        'USER_EMAIL'            => ploopi\str::htmlentities($_SESSION['ploopi']['user']['email']),

        'USER_WORKSPACE_LABEL'  => ploopi\str::htmlentities(_PLOOPI_LABEL_MYWORKSPACE),
        'USER_WORKSPACE_URL'    => ploopi\crypt::urlencode("admin.php", _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'USER_WORKSPACE_SEL'    => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE) ? 'selected' : '',

        'MAINMENU_PROFILE'          => _PLOOPI_LABEL_MYPROFILE,
        'MAINMENU_ANNOTATIONS'      => _PLOOPI_LABEL_MYANNOTATIONS,
        'MAINMENU_TICKETS'          => _PLOOPI_LABEL_MYTICKETS,
        'MAINMENU_SEARCH'           => _PLOOPI_LABEL_SEARCH,
        'MAINMENU_DISCONNECTION'    => _PLOOPI_LABEL_DISCONNECTION,

        'POPUP_PROFLE'              => ploopi\crypt::queryencode("ploopi_op=system_update_profile"),

        'MAINMENU_SHOWPROFILE_URL'      => ploopi\crypt::urlencode('admin.php?op=profile', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWANNOTATIONS_URL'  => ploopi\crypt::urlencode('admin.php?op=annotation', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWTICKETS_URL'      => ploopi\crypt::urlencode('admin.php?op=tickets', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWDATA_URL'         => ploopi\crypt::urlencode('admin.php?op=actions', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWSEARCH_URL'       => ploopi\crypt::urlencode('admin.php?op=search', _PLOOPI_MENU_WORKSPACES, null, _PLOOPI_MODULE_SEARCH, 'public'),

        'MAINMENU_SHOWPROFILE_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'profile') ? 'selected' : '',
        'MAINMENU_SHOWANNOTATIONS_SEL'  => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'annotation') ? 'selected' : '',
        'MAINMENU_SHOWTICKETS_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'tickets') ? 'selected' : '',
        'MAINMENU_SHOWTICKETS_SEL'      => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE && !empty($_REQUEST['op']) && $_REQUEST['op'] == 'actions') ? 'selected' : '',
        'MAINMENU_SHOWSEARCH_SEL'       => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES) ? 'selected' : '',

        'SEARCH_KEYWORDS'               => (!empty($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'])) ? ploopi\str::htmlentities($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords']) : '',

        'NEWTICKETS'                => $newtickets,
        'LAST_NEWTICKET'            => $lastticket,
        'SHOW_BLOCKMENU'            => (!empty($_SESSION['ploopi']['switchdisplay']['block_modules'])) ? $_SESSION['ploopi']['switchdisplay']['block_modules'] : 'block',

        'USER_DECONNECT'        => ploopi\crypt::urlencode("admin.php?ploopi_logout", null, null, null, null, false)
    ));

    if ($newtickets) self::$template_body->assign_block_vars('switch_user_logged_in.switch_newtickets', array());

    if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES)
    {
        self::$template_body->assign_block_vars('switch_user_logged_in.switch_search', array());
        self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.switch_search', array());
    }

}
else
{
    self::$template_body->assign_block_vars('switch_user_logged_out', array(
        'FORM_URL' => ploopi\crypt::urlencode('admin.php')
    ));

    if (!empty($_SESSION['ploopi']['errorcode']))
    {
        self::$template_body->assign_block_vars('switch_user_logged_out.switch_ploopierrormsg', array());

        // Cas particulier : demande de changement de mot de passe
        if (in_array($_SESSION['ploopi']['errorcode'], array( _PLOOPI_ERROR_PASSWORDRESET, _PLOOPI_ERROR_PASSWORDERROR, _PLOOPI_ERROR_PASSWORDINVALID)))
        {
            self::$template_body->assign_block_vars('switch_user_logged_out.switch_passwordreset', array());

            if (_PLOOPI_USE_COMPLEXE_PASSWORD) {
                self::$template_body->assign_block_vars('switch_user_logged_out.switch_passwordreset.switch_cp', array('MIN_SIZE' => _PLOOPI_COMPLEXE_PASSWORD_MIN_SIZE));
            } else {
                self::$template_body->assign_block_vars('switch_user_logged_out.switch_passwordreset.switch_np', array());
            }

            self::$template_body->assign_vars(array(
                'USER_LOGIN'            => ploopi\str::htmlentities($_SESSION['ploopi']['login']),
                'USER_PASSWORD'         => ploopi\str::htmlentities($_SESSION['ploopi']['password'])
            ));
        }
    }

    if (!empty($_SESSION['ploopi']['msgcode']))
    {
        self::$template_body->assign_block_vars('switch_user_logged_out.switch_ploopimsg', array());
    }

    self::$template_body->assign_vars(array(
        'PASSWORDLOST_URL'              => ploopi\crypt::urlencode('admin.php?ploopi_op=ploopi_lostpassword')
        )
    );

}

/**
 * Gestion du cas où on demande à l'utilisateur de compléter son profil
 */

if (!empty($_SESSION['ploopi']['updateprofile']) && ploopi\param::get('system_profile_edit_allowed', _PLOOPI_MODULE_SYSTEM) == '1') {
    $ploopi_additional_javascript .= "
        jQuery(function() {
            ploopi.popup.show('', 950, null, true, 'system_popup_update_profile')
            ploopi.xhr.todiv('admin-light.php', '".ploopi\crypt::queryencode("ploopi_op=system_update_profile")."', 'system_popup_update_profile');
        });
    ";

    $_SESSION['ploopi']['updateprofile'] = false;
}

$wsp = self::get_workspace();

self::$template_body->assign_vars(array(
    'TEMPLATE_PATH'                 => $_SESSION['ploopi']['template_path'],
    'TEMPLATE_NAME'                 => $_SESSION['ploopi']['template_name'],
    'WORKSPACE_LABEL'               => $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE ? ploopi\str::htmlentities(_PLOOPI_LABEL_MYWORKSPACE) : ploopi\str::htmlentities($wsp['label']),
    'WORKSPACE_CODE'                => ploopi\str::htmlentities($wsp['code']),
    'WORKSPACE_TITLE'               => ploopi\str::htmlentities($wsp['title']),
    'WORKSPACE_META_DESCRIPTION'    => ploopi\str::htmlentities($wsp['meta_description']),
    'WORKSPACE_META_KEYWORDS'       => ploopi\str::htmlentities($wsp['meta_keywords']),
    'WORKSPACE_META_AUTHOR'         => ploopi\str::htmlentities($wsp['meta_author']),
    'WORKSPACE_META_COPYRIGHT'      => ploopi\str::htmlentities($wsp['meta_copyright']),
    'WORKSPACE_META_ROBOTS'         => ploopi\str::htmlentities($wsp['meta_robots']),
    'SITE_CONNECTEDUSERS'           => $_SESSION['ploopi']['connectedusers'],
    'SITE_ANONYMOUSUSERS'           => $_SESSION['ploopi']['anonymoususers'],
    'ADDITIONAL_JAVASCRIPT'         => $ploopi_additional_javascript,
    'PLOOPI_ERROR'                  => (!empty($_SESSION['ploopi']['errorcode']) && isset($ploopi_errormsg[$_SESSION['ploopi']['errorcode']])) ? $ploopi_errormsg[$_SESSION['ploopi']['errorcode']] : '',
    'PLOOPI_MSG'                    => (!empty($_SESSION['ploopi']['msgcode']) && isset($ploopi_msg[$_SESSION['ploopi']['msgcode']])) ? $ploopi_msg[$_SESSION['ploopi']['msgcode']] : '',
    'PLOOPI_VERSION'                => _PLOOPI_VERSION,
    'PLOOPI_REVISION'               => _PLOOPI_REVISION
));

unset($_SESSION['ploopi']['errorcode']);

self::$template_body->pparse('body');
