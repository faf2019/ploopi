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

$toolbar = array();

if ($_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN)
{
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

}

if (!empty($_GET['sysToolbarItem']))  $_SESSION['system']['sysToolbarItem'] = $_GET['sysToolbarItem'];
if (!isset($_SESSION['system']['sysToolbarItem'])) $_SESSION['system']['sysToolbarItem'] = '';
echo $skin->create_toolbar($toolbar,$_SESSION['system']['sysToolbarItem']);
?>

<div>
    <?
    switch($_SESSION['system']['sysToolbarItem'])
    {
        // ---------------------------------
        // ONGLET "INSTALLATION DE MODULES"
        // ---------------------------------
        case 'install':
            switch($op)
            {
                case 'update':
                    include("./modules/system/admin_system_installmodules_updateproc.php");
                break;

                case 'install':
                    include("./modules/system/admin_system_installmodules_installproc.php");
                break;

                case 'uninstall':
                    global $admin_redirect;
                    $admin_redirect = true;

                    include("./modules/system/admin_system_installmodules_uninstallproc.php");

                    if ($admin_redirect) ploopi_redirect("$scriptenv?reloadsession");
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
                    include("./modules/system/admin_system_addnewmodule.php");
                    //ploopi_redirect("$scriptenv");
                break;

                /*
                case 'uploadmodule':
                    // zip file ?
                    if (strstr($_FILES['system_modulefile']['name'],'.zip'))
                    {
                        ploopi_print_r($_FILES);
                        $install_path = realpath('.')._PLOOPI_SEP.'install'._PLOOPI_SEP;
                        $newpath = $install_path.$_FILES['system_modulefile']['name'];

                        if (move_uploaded_file($_FILES['system_modulefile']['tmp_name'],$newpath))
                        {
                            //unzip
                            exec("unzip -o -qq $newpath -d $install_path");
                            exec("chmod -R 755 $install_path");
                            //delete
                            unlink($newpath);
                        }
                    }
                    ploopi_redirect("$scriptenv");
                break;
                */

                // update metabase
                case 'updatemb':
                    $module_type = new module_type();
                    if (!empty($_GET['idmoduletype']) && is_numeric($_GET['idmoduletype']) && $module_type->open($_GET['idmoduletype']))
                    {
                        global $idmoduletype;
                        $idmoduletype = $_GET['idmoduletype'];

                        include_once ('./modules/system/xmlparser_mb.php');

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
                    include('./modules/system/admin_system_installmodules.php');
                break;

            }
        break;

        case 'tools':
            switch($op)
            {
                case "phpinfo":
                    ob_start();
                    phpinfo();

                    preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);

                    # $matches [1]; # Style information
                    # $matches [2]; # Body information

                    echo $skin->open_simplebloc(_SYSTEM_LABEL_PHPINFO);
                    echo "<div class='phpinfodisplay'><style type='text/css'>\n",
                        join( "\n",
                            array_map(
                                create_function(
                                    '$i',
                                    'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'
                                    ),
                                preg_split( '/\n/', $matches[1] )
                                )
                            ),
                        "</style>\n",
                        $matches[2],
                        "\n</div>\n";

                    echo $skin->close_simplebloc();


                break;

                case "diagnostic":
                    include("./modules/system/tools_diagnostic.php");
                break;

                case "sqldump":
                    include("./modules/system/tools_sqldump.php");
                break;

                case "zip":
                    include("./modules/system/tools_zip.php");
                break;

                case "backup":
                    include("./modules/system/tools_backup.php");
                break;

                case "cleandb":
                    include("./modules/system/tools_cleandb.php");
                    ploopi_redirect("$scriptenv");
                break;

                case "connectedusers":
                    include("./modules/system/logs_connectedusers.php");
                break;

                case "actionhistory":
                    include("./modules/system/logs_actionhistory.php");
                break;

                default:
                    include('./modules/system/admin_system_tools.php');
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
                case "save":
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
