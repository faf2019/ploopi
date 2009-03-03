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
 * Chargement des blocs de menus en fonction de l'espace de travail sélectionné
 *
 * @package ploopi
 * @subpackage backoffice
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

$arrBlocks = array();

switch ($_SESSION['ploopi']['mainmenu'])
{
    case _PLOOPI_MENU_WORKSPACES:
        if (!empty($_SESSION['ploopi']['workspaceid']))
        {
            // left menu
            // admin menu always on the left menu
            //if ($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['grouptabid']]['system'])

            if (ploopi_ismanager())
            {
                if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER)
                {
                    $arrBlocks[_PLOOPI_MODULE_SYSTEM]['title'] = _PLOOPI_GENERAL_ADMINISTRATION;
                    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
                    {
                        $arrBlocks[_PLOOPI_MODULE_SYSTEM]['url'] = ploopi_urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM.'&ploopi_action=admin&system_level=system');
                    }
                    else
                    {
                        $arrBlocks[_PLOOPI_MODULE_SYSTEM]['url'] = ploopi_urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM.'&ploopi_action=admin&system_level='._SYSTEM_WORKSPACES);
                    }

                    $arrBlocks[_PLOOPI_MODULE_SYSTEM]['description'] = 'Installation des Modules, Paramétrage, Monitoring';
                    $arrBlocks[_PLOOPI_MODULE_SYSTEM]['admin'] = true;

                    if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
                    {
                        $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'][] =
                            array(
                                'label' => _PLOOPI_ADMIN_SYSTEM,
                                'url' => ploopi_urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM."&ploopi_action=admin&system_level=system")
                            );
                    }

                    $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'][] = array(
                        'label' => _PLOOPI_ADMIN_WORKSPACES,
                        'url' => ploopi_urlencode("admin.php?ploopi_moduleid="._PLOOPI_MODULE_SYSTEM."&ploopi_action=admin&system_level="._SYSTEM_WORKSPACES)
                    );
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
                        $modtype = $_SESSION['ploopi']['modules'][$menu_moduleid]['moduletype'];
                        $blockpath = "./modules/{$modtype}/block.php";

                        if (file_exists($blockpath))
                        {
                            $arrBlocks[$menu_moduleid] = array(
                                                                                'title'=> $_SESSION['ploopi']['modules'][$menu_moduleid]['label'],
                                                                                'url' => ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public"),
                                                                                'file' => $blockpath
                                                                            );
                            $block = new block();
                            include($blockpath);
                            $arrBlocks[$menu_moduleid]['menu'] = $block->getmenu();
                            $arrBlocks[$menu_moduleid]['content'] = $block->getcontent();
                        }
                    }
                }
            }
        }

    break;

    case _PLOOPI_MENU_MYWORKSPACE:
        $arrBlocks[_PLOOPI_MODULE_SYSTEM] =
            array(
                'title'         =>_PLOOPI_LABEL_MYWORKSPACE,
                'url'           => ploopi_urlencode('admin.php?op=user', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
                'description'   => 'Profil utilisateur'
            );

        $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'][] =
            array(
                'label'     => _PLOOPI_LABEL_MYTICKETS,
                'url'       => ploopi_urlencode('admin.php?op=tickets', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
            );

        $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'][] =
            array(
                'label'     => _PLOOPI_LABEL_MYANNOTATIONS,
                'url'       => ploopi_urlencode('admin.php?op=annotation', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
            );

        $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'][] =
            array(
                'label'     => _PLOOPI_LABEL_MYPROFILE,
                'url'       => ploopi_urlencode('admin.php?op=profile', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
            );

        $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'][] =
            array(
                'label'     => _PLOOPI_LABEL_MYDATA,
                'url'       => ploopi_urlencode('admin.php?op=actions', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
            );

        $arrBlocks[_PLOOPI_MODULE_SYSTEM]['menu'][] =
            array(
                'label'     => _PLOOPI_LABEL_MYPARAMS,
                'url'       => ploopi_urlencode('admin.php?op=param', _PLOOPI_MENU_MYWORKSPACE, 0, _PLOOPI_MODULE_SYSTEM, 'public'),
            );
    break;

    //case _PLOOPI_MENU_TICKETS:
    //case _PLOOPI_MENU_ANNOTATIONS:
    case _PLOOPI_MENU_SEARCH:
    break;
}
?>
