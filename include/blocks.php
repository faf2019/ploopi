<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Chargement des blocs de menus en fonction de l'espace de travail sélectionné
 *
 * @package ploopi
 * @subpackage backoffice
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

global $block;
global $menu_moduleid;

$arrBlocks = array();

switch ($_SESSION['ploopi']['mainmenu'])
{
    case _PLOOPI_MENU_WORKSPACES:
        if (!empty($_SESSION['ploopi']['workspaceid']))
        {
            // left menu
            // admin menu always on the left menu
            //if ($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['grouptabid']]['system'])
            if (ovensia\ploopi\acl::ismanager())
            {
                $blockpath = "./modules/system/block.php";

                if (file_exists($blockpath))
                {
                    $arrBlocks[_PLOOPI_MODULE_SYSTEM] =
                        array(
                            'title'=> _PLOOPI_GENERAL_ADMINISTRATION,
                            'description' => 'Installation des Modules, Paramétrage, Monitoring',
                            'url' => ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM.'&ploopi_action=admin&system_level='.($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN ? 'system' : _SYSTEM_WORKSPACES)),
                            'admin' => true,
                            'file' => $blockpath
                        );

                    $block = new ovensia\ploopi\block();
                    include $blockpath;
                    $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'] = $block->getmenu();
                    $arrBlocks[_PLOOPI_MODULE_SYSTEM]['content'] = $block->getcontent();
                }
            }

            // Search displayable modules for the current group & menu (left/right)
            if (isset($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules']))
            {
                $modules = $_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'];

                foreach($modules as $key => $menu_moduleid)
                {
                    if ($_SESSION['ploopi']['modules'][$menu_moduleid]['active'] && $_SESSION['ploopi']['modules'][$menu_moduleid]['visible'])
                    {
                        $strmtype = $_SESSION['ploopi']['modules'][$menu_moduleid]['moduletype'];
                        $blockpath = "./modules/{$strmtype}/block.php";

                        if (file_exists($blockpath))
                        {
                            $strClassPath = "./modules/{$strmtype}/classes/{$strmtype}.php";

                            $booAllowed = true;
                            if (file_exists($strClassPath))
                            {
                                include_once $strClassPath;

                                if (method_exists($strmtype, 'isAllowed') && !$strmtype::isAllowed($_SESSION['ploopi']['workspaceid'], $menu_moduleid)) $booAllowed = false;
                            }

                            if ($booAllowed)
                            {
                                $arrBlocks[$menu_moduleid] =
                                    array(
                                        'title'=> $_SESSION['ploopi']['modules'][$menu_moduleid]['label'],
                                        'description' => '',
                                        'url' => ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"),
                                        'file' => $blockpath
                                    );

                                $block = new ovensia\ploopi\block();
                                include($blockpath);
                                $arrBlocks[$menu_moduleid]['menu'] = $block->getmenu();
                                $arrBlocks[$menu_moduleid]['content'] = $block->getcontent();
                            }
                        }
                    }
                }
            }
        }

    break;

    case _PLOOPI_MENU_MYWORKSPACE:

        $blockpath = "./modules/system/block_public.php";

        if (file_exists($blockpath))
        {
            $arrBlocks[_PLOOPI_MODULE_SYSTEM] =
                array(
                    'title'=> _PLOOPI_LABEL_MYWORKSPACE,
                    'description' => 'Profil, paramètres, annotations, tickets',
                    'url' => ovensia\ploopi\crypt::urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM.'&ploopi_action=public'),
                    'file' => $blockpath
                );

            $block = new block();
            include $blockpath;
            $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'] = $block->getmenu();
            $arrBlocks[_PLOOPI_MODULE_SYSTEM]['content'] = $block->getcontent();
        }
    break;

    //case _PLOOPI_MENU_TICKETS:
    //case _PLOOPI_MENU_ANNOTATIONS:
    case _PLOOPI_MENU_SEARCH:
    break;
}
