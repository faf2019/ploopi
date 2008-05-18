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
 * On compare la version des fichier (_PLOOPI_VERSION) avec celle de la base de données.
 * Si les version ne concordent pas, on propose une mise à jour du système.
 */
if (strcmp(_PLOOPI_VERSION, $row['version']))
{
    $strSysVersion = $row['version'];
    $toolbar['systemupdate'] = array(
                                        'title' => _SYSTEM_LABELICON_SYSTEMUPDATE,
                                        'url'   => "{$scriptenv}?sysToolbarItem=systemupdate",
                                        'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_systemupdate.png"
                                    );
}

$toolbar['install'] = array(
                                    'title' => _SYSTEM_LABELICON_INSTALLMODULES,
                                    'url'   => "{$scriptenv}?sysToolbarItem=install",
                                    'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_install_module.png"
                                );

$toolbar['params'] = array(
                                    'title' => _SYSTEM_LABELICON_PARAMS,
                                    'url'   => "{$scriptenv}?sysToolbarItem=params",
                                    'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_systemparams.png"
                                );

$toolbar['tools'] = array(
                                    'title' => _SYSTEM_LABELICON_TOOLS,
                                    'url'   => "{$scriptenv}?sysToolbarItem=tools",
                                    'icon'  => "{$_SESSION['ploopi']['template_path']}/img/system/icons/tab_tools.png"
                                );

if (!empty($_GET['sysToolbarItem']))  $_SESSION['system']['sysToolbarItem'] = $_GET['sysToolbarItem'];
if (!isset($_SESSION['system']['sysToolbarItem'])) $_SESSION['system']['sysToolbarItem'] = '';
echo $skin->create_toolbar($toolbar,$_SESSION['system']['sysToolbarItem']);
?>

<div>
    <?
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

                    if ($admin_redirect) ploopi_redirect("{$scriptenv}?reloadsession");
                    else
                    {
                        ?>
                                </TD>
                            </TR>
                            <TR>
                                <TD ALIGN="RIGHT">
                                <INPUT TYPE="Button" CLASS="flatbutton" VALUE="<? echo _PLOOPI_CONTINUE; ?>" OnClick="javascript:document.location.href='<? echo "$scriptenv?reloadsession"; ?>'">
                                </TD>
                            </TR>
                            </TABLE>
                        <?
                        echo $skin->close_simplebloc();
                    }

                break;

                case 'addnewmodule':
                    include './modules/system/admin_system_addnewmodule.php';
                    //ploopi_redirect("$scriptenv");
                break;

                // update metabase
                case 'updatemb':
                    $module_type = new module_type();
                    if (!empty($_GET['idmoduletype']) && is_numeric($_GET['idmoduletype']) && $module_type->open($_GET['idmoduletype']))
                    {
                        global $idmoduletype;
                        $idmoduletype = $_GET['idmoduletype'];

                        include './modules/system/xmlparser_mb.php';

                        ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMETABASE, $module_type->fields['label']);


                        $db->query("DELETE FROM ploopi_mb_field WHERE id_module_type = {$_GET['idmoduletype']}");
                        $db->query("DELETE FROM ploopi_mb_relation WHERE id_module_type = {$_GET['idmoduletype']}");
                        $db->query("DELETE FROM ploopi_mb_schema WHERE id_module_type = {$_GET['idmoduletype']}");
                        $db->query("DELETE FROM ploopi_mb_table WHERE id_module_type = {$_GET['idmoduletype']}");
                        $db->query("DELETE FROM ploopi_mb_object WHERE id_module_type = {$_GET['idmoduletype']}");
                        $db->query("DELETE FROM ploopi_mb_wce_object WHERE id_module_type = {$_GET['idmoduletype']}");


                        $mbfile = "./install/{$module_type->fields['label']}/mb.xml";

                        if (file_exists($mbfile))
                        {
                            $xml_parser = xmlparser_mb();
                            if (!xml_parse($xml_parser,  file_get_contents($mbfile)))
                            {
                                $stop = sprintf("Erreur XML: %s à la ligne %d dans '%s'\n", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser), $mbfile);
                                $testok = false;
                            }
                            else $detail = "Fichier '{$mbfile}' importé";

                            xml_parser_free($xml_parser);
                        }
                        else $detail = "Fichier '{$mbfile}' non trouvé";
                    }

                    ploopi_redirect($scriptenv);
                break;

                default:
                    include './modules/system/admin_system_installmodules.php';
                break;

            }
        break;

        case 'tools':
            switch($op)
            {
                case 'phpinfo':
                    echo $skin->open_simplebloc(_SYSTEM_LABEL_PHPINFO);
                    ?>
                    <iframe id="system_tools_phpinfo" style="border:0;width:100%;height:400px;margin:0;padding:0;" src="<? echo "admin-light.php?ploopi_op=system_tools_phpinfo"; ?>"></iframe>                    
                    <?
                    echo $skin->close_simplebloc();
                break;

                case 'serverload':
                    echo $skin->open_simplebloc(_SYSTEM_LABEL_DIAGNOSTIC);
                    ?>
                    <div id="system_serverload">
                    <? include './modules/system/tools_serverload.php'; ?>
                    </div>
                    <script type="text/javascript">system_serverload();</script>
                    <?
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
                    include "./modules/system/logs_connectedusers.php";
                break;

                case 'actionhistory':
                    include "./modules/system/logs_actionhistory.php";
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

                        ploopi_redirect("{$scriptenv}?idmodule={$_POST['idmodule']}&reloadsession");
                    }
                    else ploopi_redirect("$scriptenv");
                break;

                default:
                    include_once './modules/system/admin_system_param.php';
                break;
            }
        break;

    }

    ?>

</div>
