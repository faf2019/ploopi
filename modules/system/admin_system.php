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
 * Gestion des interfaces d'administration "système"
 *
 * @package system
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */


/**
 * Construction de la barre d'icones
 */

$toolbar = array();

$strSysVersion = '';

$db->query('SELECT version FROM ploopi_module_type WHERE id = 1');
$row = $db->fetchrow();

/**
 * On compare la version des fichiers (_PLOOPI_VERSION) avec celle de la base de données.
 * Si les versions ne concordent pas, on propose une mise à jour du système.
 */

if (strcmp(_PLOOPI_VERSION, $row['version']) > 0)
{
    $strSysVersion = $row['version'];
    $toolbar['systemupdate'] =
        array(
            'title' => _SYSTEM_LABELICON_SYSTEMUPDATE,
            'url'   => "admin.php?sysToolbarItem=systemupdate",
            'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_systemupdate.png"
        );
}

$toolbar['install'] =
    array(
        'title' => _SYSTEM_LABELICON_INSTALLMODULES,
        'url'   => "admin.php?sysToolbarItem=install",
        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_install_module.png"
    );

$toolbar['params'] =
    array(
        'title' => _SYSTEM_LABELICON_PARAMS,
        'url'   => "admin.php?sysToolbarItem=params",
        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_systemparams.png"
    );

$toolbar['directory'] =
    array(
        'title' => _SYSTEM_LABELICON_USERS,
        'url'   => "admin.php?sysToolbarItem=directory",
        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_directory.png"
    );

$toolbar['tools'] =
    array(
        'title' => _SYSTEM_LABELICON_TOOLS,
        'url'   => "admin.php?sysToolbarItem=tools",
        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_tools.png"
    );

if (!empty($_GET['sysToolbarItem']))  $_SESSION['system']['sysToolbarItem'] = $_GET['sysToolbarItem'];
if (!isset($_SESSION['system']['sysToolbarItem'])) $_SESSION['system']['sysToolbarItem'] = '';
echo $skin->create_toolbar($toolbar,$_SESSION['system']['sysToolbarItem']);
?>

<div>
    <?php
    switch($_SESSION['system']['sysToolbarItem'])
    {
        case 'systemupdate':
            if (!empty($strSysVersion)) include './modules/system/admin_system_update.php';
        break;

        // ---------------------------------
        // ONGLET "INSTALLATION DE MODULES"
        // ---------------------------------
        case 'install':
            switch($op)
            {
                case 'update':
                    include './modules/system/admin_system_installmodules_updateproc.php';
                break;

                case 'install':
                    include './modules/system/admin_system_installmodules_installproc.php';
                break;

                case 'uninstall':
                    global $admin_redirect;
                    $admin_redirect = true;

                    include './modules/system/admin_system_installmodules_uninstallproc.php';

                    if ($admin_redirect) ploopi_redirect("admin.php?reloadsession");
                    else
                    {
                        ?>
                                </TD>
                            </TR>
                            <TR>
                                <TD ALIGN="RIGHT">
                                <INPUT TYPE="Button" CLASS="flatbutton" VALUE="<?php echo _PLOOPI_CONTINUE; ?>" OnClick="javascript:document.location.href='<?php echo "admin.php?reloadsession"; ?>'">
                                </TD>
                            </TR>
                            </TABLE>
                        <?php
                        echo $skin->close_simplebloc();
                    }

                break;

                case 'addnewmodule':
                    include './modules/system/admin_system_addnewmodule.php';
                    //ploopi_redirect("admin.php");
                break;

                default:
                    include './modules/system/admin_system_installmodules.php';
                break;

            }
        break;

        case 'directory':
            include "./modules/system/admin_system_directory.php";
        break;

        case 'tools':
            switch($op)
            {
                case 'phpinfo':
                    echo $skin->open_simplebloc(_SYSTEM_LABEL_PHPINFO);
                    ?>
                    <iframe id="system_tools_phpinfo" style="border:0;width:100%;height:400px;margin:0;padding:0;" src="<?php echo "admin-light.php?ploopi_op=system_tools_phpinfo"; ?>"></iframe>
                    <?php
                    echo $skin->close_simplebloc();
                break;

                case 'serverload':
                    echo $skin->open_simplebloc(_SYSTEM_LABEL_SERVERLOAD);
                    ?>
                    <div id="system_serverload">
                    <?php include './modules/system/tools_serverload.php'; ?>
                    </div>
                    <script type="text/javascript">system_serverload();</script>
                    <?php
                    echo $skin->close_simplebloc();
                break;

                case 'diagnostic':
                    include "./modules/system/tools_diagnostic.php";
                break;

                case 'sqldump':
                    include "./modules/system/tools_sqldump.php";
                break;

                case 'backup':
                    include "./modules/system/tools_backup.php";
                break;

                case 'connectedusers':
                    include "./modules/system/tools_connectedusers.php";
                break;

                case 'actionhistory':
                    include "./modules/system/tools_actionhistory.php";
                break;

                case 'stats':
                    include "./modules/system/tools_stats.php";
                break;

                default:
                    include './modules/system/admin_system_tools.php';
                break;

            }
        break;

        // -------------------------------------------------
        // ONGLET DE GESTION DES PARAMETRES GENERAUX DE PLOOPI
        // -------------------------------------------------
        case 'params' :
            $param_module = new param();

            switch($op)
            {
                case 'save':
                    if (!empty($_POST['idmodule']) && is_numeric($_POST['idmodule']))
                    {
                        $module = new module();
                        $module->open($_POST['idmodule']);
                        ploopi_create_user_action_log(_SYSTEM_ACTION_PARAMMODULE, $module->fields['label']);

                        $param_module->open($_POST['idmodule']);
                        $param_module->setvalues($_POST);
                        $param_module->save();

                        ploopi_redirect("admin.php?idmodule={$_POST['idmodule']}&reloadsession");
                    }
                    else ploopi_redirect("admin.php");
                break;

                default:
                    include_once './modules/system/admin_system_param.php';
                break;
            }
        break;

    }

    ?>

</div>
