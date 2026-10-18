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

    // Repli sur le gabarit par défaut de la configuration
    $_SESSION['ploopi']['template_path'] = './templates/backoffice/'._PLOOPI_DEFAULT_TEMPLATE;
    $_SESSION['ploopi']['template_palette'] = '';

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

// Déclinaison du gabarit (ploopi4.1…) : sa palette, chargée après la feuille du gabarit
if (!empty($_SESSION['ploopi']['template_palette'])) self::$template_body->assign_block_vars('template_palette', array('PATH' => $_SESSION['ploopi']['template_palette'].'?v='.urlencode(_PLOOPI_VERSION.','._PLOOPI_REVISION)));

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
        $strWorkspaceLabel = $_SESSION['ploopi']['workspaces'][$key]['label'];

        self::$template_body->assign_block_vars('switch_user_logged_in.workspace',array(
                'TITLE' => ploopi\str::htmlentities($strWorkspaceLabel),
                'URL' => $key == $_SESSION['ploopi']['workspaceid'] && $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES && $_SESSION['ploopi']['moduleid'] != -1 ? ploopi\crypt::urlencode('admin.php') : ploopi\crypt::urlencode('admin.php?ploopi_switch_workspace', _PLOOPI_MENU_WORKSPACES, $key, '', ''),
                'SELECTED' => ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES && $key == $_SESSION['ploopi']['workspaceid']) ? 'selected' : '',
                // Pastille de l'espace (gabarits qui en affichent une) : son initiale
                'INITIAL' => ploopi\str::htmlentities(mb_strtoupper(mb_substr(trim($strWorkspaceLabel), 0, 1)))
            )
        );
    }

    // GET BLOCKS
    include_once './include/blocks.php';

    if (!empty($arrBlocks) || $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_WORKSPACES)
    {
        self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu',array());
    }

    // Fil d'Ariane : espace, module, entrée de menu courante (gabarits qui l'affichent)
    $arrPloopiCrumbs = array();

    if ($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE)
    {
        $arrPloopiCrumbs[] = array('label' => _PLOOPI_LABEL_MYWORKSPACE, 'url' => ploopi\crypt::urlencode('admin.php', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'));
    }
    elseif (!empty($_SESSION['ploopi']['workspaceid']) && isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]))
    {
        $arrPloopiCrumbs[] = array('label' => $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['label'], 'url' => ploopi\crypt::urlencode('admin.php?ploopi_switch_workspace', _PLOOPI_MENU_WORKSPACES, $_SESSION['ploopi']['workspaceid'], '', ''));
    }

    if (!empty($arrBlocks))
    {
        foreach($arrBlocks as $idmod => $mod)
        {
            if (empty($mod['url'])) $mod['url'] = '';

            $strModuleType = isset($_SESSION['ploopi']['modules'][$idmod]['moduletype']) ? $_SESSION['ploopi']['modules'][$idmod]['moduletype'] : '';

            // CAS 1 : liste standard de modules
            self::$template_body->assign_block_vars('switch_user_logged_in.switch_blockmenu.block',array(
                    'ID' => $idmod,
                    'TITLE' => ploopi\str::htmlentities($mod['title']),
                    'URL' => $mod['url'],
                    'DESCRIPTION' => '',
                    'SELECTED' => ($idmod == $_SESSION['ploopi']['moduleid']) ? 'selected' : '',
                    'MODULETYPE' => ploopi\str::htmlentities($strModuleType),
                    // Icône du type de module, d'après le gabarit (skin.ini : icon_<type>)
                    'ICON' => ploopi\skin::get()->moduleicon($strModuleType)
                )
            );

            if ($idmod == $_SESSION['ploopi']['moduleid'] && $_SESSION['ploopi']['mainmenu'] != _PLOOPI_MENU_MYWORKSPACE)
            {
                $arrPloopiCrumbs[] = array('label' => $mod['title'], 'url' => $mod['url']);
            }

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


                $strSection = '';

                foreach($mod['menu'] as $menu)
                {
                    $arrMenuVars = array(
                        'LABEL' => $menu['label'],
                        'CLEANED_LABEL' => $menu['cleaned_label'],
                        'URL' => $menu['url'],
                        'SELECTED' => (!empty($menu['selected']) && $menu['selected']) ? 'selected' : '',
                        'TARGET' => (!empty($menu['target'])) ? $menu['target'] : '',
                        'ICON' => empty($menu['icon']) ? '' : $menu['icon']
                    );

                    // Sous-blocs facultatifs : icône de l'entrée, début d'une nouvelle section
                    $booSectionStart = !empty($menu['section']) && $menu['section'] != $strSection;
                    if (!empty($menu['section'])) $strSection = $menu['section'];

                    $arrMenuBlocks = array();
                    if ($idmod == $_SESSION['ploopi']['moduleid']) $arrMenuBlocks[] = 'switch_user_logged_in.switch_blockmenu.switch_blocksel.menu'; // Module sélectionné
                    $arrMenuBlocks[] = 'switch_user_logged_in.switch_blockmenu.block.menu';

                    foreach($arrMenuBlocks as $strMenuBlock)
                    {
                        self::$template_body->assign_block_vars($strMenuBlock, $arrMenuVars);
                        if (!empty($menu['icon'])) self::$template_body->assign_block_vars("{$strMenuBlock}.switch_icon", array('ICON' => $menu['icon']));
                        if ($booSectionStart) self::$template_body->assign_block_vars("{$strMenuBlock}.switch_section", array('TITLE' => ploopi\str::htmlentities($strSection)));
                    }

                    // Entrée courante du module courant : dernier maillon du fil d'Ariane
                    if ($idmod == $_SESSION['ploopi']['moduleid'] && !empty($menu['selected']))
                    {
                        // Libellé parfois échappé deux fois (par le module, puis par le bloc) : décodé jusqu'à stabilité
                        $strPloopiCrumb = $menu['cleaned_label'];
                        for ($intPloopiDecode = 0; $intPloopiDecode < 3 && ($strPloopiDecoded = html_entity_decode($strPloopiCrumb, ENT_QUOTES, 'UTF-8')) !== $strPloopiCrumb; $intPloopiDecode++) $strPloopiCrumb = $strPloopiDecoded;
                        $arrPloopiCrumbs[] = array('label' => $strPloopiCrumb, 'url' => $menu['url']);
                    }
                }
            }
        }
    }

    // Fil d'Ariane transmis au gabarit avant l'appel du module, qui partage cette portée
    foreach($arrPloopiCrumbs as $intPloopiCrumb => $arrPloopiCrumb)
    {
        self::$template_body->assign_block_vars('switch_user_logged_in.crumb', array(
            'LABEL' => ploopi\str::htmlentities($arrPloopiCrumb['label']),
            'URL' => $arrPloopiCrumb['url'],
            // Le dernier maillon est la page courante
            'CURRENT' => $intPloopiCrumb == count($arrPloopiCrumbs) - 1 ? 'page' : 'false'
        ));
    }
    unset($arrPloopiCrumbs, $intPloopiCrumb, $arrPloopiCrumb, $strPloopiCrumb, $strPloopiDecoded, $intPloopiDecode);

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

    // Initiales et niveau de l'utilisateur (pastille du menu utilisateur)
    $strUserInitials = mb_strtoupper(mb_substr(trim($_SESSION['ploopi']['user']['firstname']), 0, 1).mb_substr(trim($_SESSION['ploopi']['user']['lastname']), 0, 1));
    if ($strUserInitials == '') $strUserInitials = mb_strtoupper(mb_substr($_SESSION['ploopi']['login'], 0, 2));

    global $ploopi_system_levels;
    $strUserLevel = isset($ploopi_system_levels[$_SESSION['ploopi']['adminlevel']]) ? $ploopi_system_levels[$_SESSION['ploopi']['adminlevel']] : '';

    self::$template_body->assign_vars(array(
        'PAGE_CONTENT'          => $page_content,
        'ADDITIONAL_HEAD'       => $ploopi_additional_head,

        'USER_LOGIN'            => ploopi\str::htmlentities($_SESSION['ploopi']['login']),
        // Le mot de passe en clair n'est plus conservé en session : le champ reste vide
        'USER_PASSWORD'         => '',
        'USER_FIRSTNAME'        => ploopi\str::htmlentities($_SESSION['ploopi']['user']['firstname']),
        'USER_LASTNAME'         => ploopi\str::htmlentities($_SESSION['ploopi']['user']['lastname']),
        'USER_EMAIL'            => ploopi\str::htmlentities($_SESSION['ploopi']['user']['email']),
        'USER_INITIALS'         => ploopi\str::htmlentities($strUserInitials),
        'USER_LEVEL'            => ploopi\str::htmlentities($strUserLevel),

        'MODULE_TYPE'           => empty($_SESSION['ploopi']['moduletype']) ? '' : ploopi\str::htmlentities($_SESSION['ploopi']['moduletype']),

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
        // Profil incomplet à la connexion (ploopi4 : le shell suit alors le lien vers Mon profil)
        'MAINMENU_PROFILE_REQUIRED'     => '',
        'MAINMENU_SHOWDATA_URL'         => ploopi\crypt::urlencode('admin.php?op=actions', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
        'MAINMENU_SHOWSEARCH_URL'       => ploopi\crypt::urlencode('admin.php?op=search', _PLOOPI_MENU_WORKSPACES, null, _PLOOPI_MODULE_SEARCH, 'public'),
        // Suggestions à la frappe de la recherche (ploopi4, js/shell.js)
        'MAINMENU_SEARCHSUGGEST_URL'    => ploopi\crypt::urlencode('admin-light.php?ploopi_op=system_search_suggest', _PLOOPI_MENU_WORKSPACES, null, _PLOOPI_MODULE_SEARCH, 'public'),

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
                // Le mot de passe en clair n'est plus conservé en session : le champ reste vide
                'USER_PASSWORD'         => ''
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

if (!empty($_SESSION['ploopi']['updateprofile']) && ploopi\param::get('system_profile_edit_allowed', _PLOOPI_MODULE_SYSTEM) == '1' && ploopi\skin::get()->version() >= 4) {
    // ploopi4 : l'écran Mon profil, qui annonce une fois le profil incomplet. Pas de
    // redirection HTTP : la page demandée est servie entière (son environnement reste
    // lisible), le shell suit le lien du menu marqué data-p4-profile-required.
    $_SESSION['ploopi']['updateprofile'] = false;
    $_SESSION['ploopi']['updateprofile_notice'] = true;
    self::$template_body->assign_var('MAINMENU_PROFILE_REQUIRED', '1');
}
elseif (!empty($_SESSION['ploopi']['updateprofile']) && ploopi\param::get('system_profile_edit_allowed', _PLOOPI_MODULE_SYSTEM) == '1') {
    $ploopi_additional_javascript .= "
        jQuery(function() {
            ploopi.popup.show('', 950, null, true, 'system_popup_update_profile')
            ploopi.xhr.todiv('admin-light.php', '".ploopi\crypt::queryencode("ploopi_op=system_update_profile")."', 'system_popup_update_profile');
        });
    ";

    $_SESSION['ploopi']['updateprofile'] = false;
}

$wsp = self::get_workspace();

// Préférences d'affichage (thème, densité) : gabarits qui les gèrent (ploopi4)
list($strUserTheme, $strUserDensity) = ploopi\skin::getdisplayprefs();

self::$template_body->assign_vars(array(
    'USER_THEME'                    => $strUserTheme,
    'USER_DENSITY'                  => $strUserDensity,
    'TEMPLATE_PATH'                 => $_SESSION['ploopi']['template_path'],
    'TEMPLATE_NAME'                 => $_SESSION['ploopi']['template_name'],
    'WORKSPACE_LABEL'               => $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE ? ploopi\str::htmlentities(_PLOOPI_LABEL_MYWORKSPACE) : ploopi\str::htmlentities($wsp['label']),
    'WORKSPACE_INITIAL'             => ploopi\str::htmlentities(mb_strtoupper(mb_substr(trim($_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYWORKSPACE ? _PLOOPI_LABEL_MYWORKSPACE : $wsp['label']), 0, 1))),
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
    'CSP_NONCE'                     => ploopi\csp::nonce(),
    'PLOOPI_ERROR'                  => (!empty($_SESSION['ploopi']['errorcode']) && isset($ploopi_errormsg[$_SESSION['ploopi']['errorcode']])) ? $ploopi_errormsg[$_SESSION['ploopi']['errorcode']] : '',
    'PLOOPI_MSG'                    => (!empty($_SESSION['ploopi']['msgcode']) && isset($ploopi_msg[$_SESSION['ploopi']['msgcode']])) ? $ploopi_msg[$_SESSION['ploopi']['msgcode']] : '',
    'PLOOPI_VERSION'                => _PLOOPI_VERSION,
    'PLOOPI_REVISION'               => _PLOOPI_REVISION
));

/**
 * CSP stricte (§ 3.32) sous ploopi4 : page de connexion, écrans du module system
 * (construits sans script en ligne : ploopi\system\ui, p4.js) et écrans des modules qui
 * se déclarent prêts par la constante _<MODULE>_CSP de leur include/global.php (aucun
 * gestionnaire en ligne, ni dans leurs pages ni dans les fragments qu'elles chargent).
 * Le contrôle de fin de requête (csp::finalize) garde la politique de base à une page
 * qui en contiendrait malgré tout.
 */
if (ploopi\skin::get()->version() >= 4)
{
    $strCspModule = isset($_SESSION['ploopi']['moduletype']) ? (string) $_SESSION['ploopi']['moduletype'] : '';
    if (empty($_SESSION['ploopi']['connected']) || $strCspModule == 'system' || ploopi\csp::moduleready($strCspModule)) ploopi\csp::strict();
}

unset($_SESSION['ploopi']['errorcode']);

self::$template_body->pparse('body');
