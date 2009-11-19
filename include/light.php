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
 * Point d'entrée permettant de charger une version allégée du template (light.tpl).
 * L'habillage général et les blocs ne sont pas affichés, uniquement le contenu du module.
 *
 * @package ploopi
 * @subpackage index
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

if ($_SESSION['ploopi']['mode'] == 'backoffice')
{
    if ($_SESSION['ploopi']['connected'])
    {
        include_once './lib/template/template.php';
        include_once "{$_SESSION['ploopi']['template_path']}/class_skin.php";

        $skin = new skin();
        $template_body = new Template($_SESSION['ploopi']['template_path']);

        if (!file_exists("{$_SESSION['ploopi']['template_path']}/light.tpl") || ! is_readable("{$_SESSION['ploopi']['template_path']}/light.tpl")) {

            ploopi_die(
                str_replace(
                    array('<FILE>', '<TEMPLATE>'),
                    array('light.tpl', $_SESSION['ploopi']['template_path']),
                    _PLOOPI_ERROR_TEMPLATE_FILE
                )
            );

        }

        $template_body->set_filenames(
            array(
                'body' => 'light.tpl'
            )
        );

        // PLOOPI JS
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
        
        $ploopi_additional_head = '';
        $ploopi_additional_javascript = '';
        
        // GET MODULE ADDITIONAL JS
        ob_start();
        include './include/javascript.php';
        if (file_exists("./modules/{$_SESSION['ploopi']['moduletype']}/include/javascript.php")) include "./modules/{$_SESSION['ploopi']['moduletype']}/include/javascript.php";
        $ploopi_additional_javascript = ob_get_contents();
        @ob_end_clean();

        include_once './include/op.php';

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
        $main_content = ob_get_contents();
        @ob_end_clean();

        $template_body->assign_vars(array(
            'TEMPLATE_PATH'         => $_SESSION['ploopi']['template_path'],
            'ADDITIONAL_HEAD'       => $ploopi_additional_head,
            'ADDITIONAL_JAVASCRIPT' => $ploopi_additional_javascript,
            'PAGE_CONTENT'          => $main_content
            )
        );

        $template_body->pparse('body');

    }
    else
    {
        include_once './include/op.php';
    }
}
else // frontoffice
{
    include_once './include/frontoffice.php';
}
?>
