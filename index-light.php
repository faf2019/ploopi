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

///////////////////////////////////////////////////////////////////////////
// START PLOOPI ENGINE
///////////////////////////////////////////////////////////////////////////

include_once './include/start.php';

if ($_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['mode'] == 'admin')
{
    include_once './lib/template/template.php';
    include_once "{$_SESSION['ploopi']['template_path']}/class_skin.php";

    $skin = new skin();
    $template_body = new Template($_SESSION['ploopi']['template_path']);

    $template_body->set_filenames(array('body' => "light.tpl"));

    // PLOOPI JS
    $template_body->assign_block_vars('ploopi_js',array('PATH' => "./lib/prototype/prototype.pack.js"));
    $template_body->assign_block_vars('ploopi_js',array('PATH' => "./js/functions.pack.js"));

    // GET MODULES STYLES & JS
    if ($_SESSION['ploopi']['connected'] && $_SESSION['ploopi']['mainmenu'] == _PLOOPI_MENU_MYGROUPS && $_SESSION['ploopi']['workspaceid'] != _PLOOPI_NOGROUP && isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules']))
    {
        foreach($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $key => $mid)
        {
            if (isset($_SESSION['ploopi']['modules'][$mid]['active']))
            {
                $modtype = $_SESSION['ploopi']['modules'][$mid]['moduletype'];

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
        }
    }

    // GET MODULE ADDITIONAL JS
    ob_start();
    include('./include/javascript.php');
    if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/include/javascript.php")) include("./modules/{$_SESSION['ploopi']['moduletype']}/include/javascript.php");
    $additional_javascript = ob_get_contents();
    @ob_end_clean();

    include_once './include/op.php';

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
    $main_content = ob_get_contents();
    @ob_end_clean();

    $template_body->assign_vars(array(
        'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
        'ADDITIONAL_JAVASCRIPT' => $additional_javascript,
        'PAGE_CONTENT'          => $main_content
        )
    );

    $template_body->pparse('body');
}

include_once './include/stats.php';

if ($ploopi_errors_level && _PLOOPI_MAIL_ERRORS && _PLOOPI_ADMINMAIL != '') echo mail(_PLOOPI_ADMINMAIL,"[{$ploopi_errorlevel[$ploopi_errors_level]}] sur [{$_SERVER['HTTP_HOST']}]", "$ploopi_errors_nb erreur(s) sur $ploopi_errors_msg\n\nDUMP:\n$ploopi_errors_vars");
if (defined('_PLOOPI_ACTIVELOG') && _PLOOPI_ACTIVELOG)  include './modules/system/hit.php';

session_write_close();
$db->close();
?>
