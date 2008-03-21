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

include_once './modules/system/xmlparser_mb.php';
include_once './include/classes/class_xml2array.php';

if (empty($_GET['installmoduletype']) || empty($_GET['idmoduletype']) || empty($_GET['updatefrom']) || empty($_GET['updateto']) || !is_numeric($_GET['idmoduletype'])) ploopi_redirect("{$scriptenv}");

global $idmoduletype;
$idmoduletype = $_GET['idmoduletype'];

if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);

echo $skin->open_simplebloc(_SYSTEM_LABEL_UPDATEREPORT.htmlentities(" - {$_GET['installmoduletype']} {$_GET['updatefrom']} => {$_GET['installmoduletype']} {$_GET['updateto']}"));

$select = "SELECT version FROM ploopi_module_type WHERE id = ".$db->addslashes($_GET['idmoduletype'])." AND version = '".$db->addslashes($_GET['updateto'])."'";
$db->query($select);
if ($db->numrows())
{
    ?>
    <div style="padding:4px;text-align:center;font-weight:bold;color:#a60000;">Module déjà mis à jour !</div>
    <?
}
else
{
    ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMODULE, "{$_GET['installmoduletype']} {$_GET['updatefrom']} => {$_GET['installmoduletype']} {$_GET['updateto']}");

    $modpath = "./install/{$_GET['installmoduletype']}";

    $sqlfile =      "{$modpath}/update/update_{$_GET['updatefrom']}_to_{$_GET['updateto']}.sql";
    $xmlfile_desc = "{$modpath}/description.xml";
    $xmlfile_ploopi = "{$modpath}/data_ploopi.xml";
    $xmlfile_mod =  "{$modpath}/data_mod.xml";
    $mbfile =       "{$modpath}/mb.xml";
    $srcfiles =     "{$modpath}/files";
    $destfiles =    "./modules/{$_GET['installmoduletype']}";

    $critical_error = false;

    $rapport = array();

    // =============
    // OPERATION 1 : Copie des fichiers
    // =============
    $testok = true;
    $detail = '';
    if (file_exists($srcfiles))
    {
        if (is_writable(realpath("./modules/")))
        {
            ploopi_copydir($srcfiles , $destfiles);
            $detail = 'Fichiers copiés';
        }
        else
        {
            $detail = "Impossible de copier les fichiers dans '{$destfiles}'.";
            $testok = false;
            $critical_error = true;
        }
    }
    else $detail = 'Aucun fichier à copier.';

    $rapport[] = array('operation' => 'Copie des fichiers', 'detail' => $detail, 'res' => $testok);

    if (!$critical_error)
    {
        // =============
        // OPERATION 2 : Chargement des paramètres/actions
        // =============
        $testok = true;
        $detail = '';

        if (file_exists($xmlfile_desc))
        {
            $fp = fopen($xmlfile_desc, 'r');
            $data = fread ($fp, filesize ($xmlfile_desc));
            fclose($fp);

            $x2a = new xml2array();
            $xmlarray = $x2a->parse($data);
            if ($xmlarray)
            {
                $pt = &$xmlarray['root']['ploopi'][0]['moduletype'][0];

                include_once './modules/system/class_module_type.php';
                include_once './modules/system/class_param_type.php';
                include_once './modules/system/class_param_choice.php';
                include_once './modules/system/class_param_default.php';
                include_once './modules/system/class_mb_action.php';
                include_once './modules/system/class_mb_cms_object.php';


                $module_type = new module_type();
                $module_type->open($_GET['idmoduletype']);

                $module_type->delete_params();

                $module_type->fields = array(   'id'            => $_GET['idmoduletype'],
                                                'label'         => $pt['label'][0],
                                                'version'       => $pt['version'][0],
                                                'author'        => $pt['author'][0],
                                                'date'          => $pt['date'][0],
                                                'description'   => $pt['description'][0]
                                            );

                $module_type->save();

                if (!empty($pt['paramtype']))
                {
                    foreach($pt['paramtype'] as $key => $value)
                    {
                        if (empty($value['default_value'][0])) $value['default_value'][0] = '';

                        $param_type = new param_type();
                        $param_type->fields = array(    'id_module_type'    => $module_type->fields['id'],
                                                        'name'              => $value['name'][0],
                                                        'label'             => $value['label'][0],
                                                        'default_value'     => $value['default_value'][0],
                                                        'public'            => $value['public'][0],
                                                        'description'       => $value['description'][0]
                                                    );

                        $param_type->save();

                        // on recherche les paramètres mal initialisés (ploopi_param_default manquant)
                        $sql =  "
                                SELECT      m.id

                                FROM        ploopi_module m

                                LEFT JOIN   ploopi_param_default pd
                                ON          pd.id_module = m.id
                                AND         pd.name = '".$db->addslashes($value['name'][0])."'

                                WHERE       m.id_module_type = {$module_type->fields['id']}
                                AND         ISNULL(pd.name)
                                ";

                        $rs_paramdefault = $db->query($sql);

                        while ($row = $db->fetchrow($rs_paramdefault))
                        {
                            $param_default = new param_default();
                            $param_default->fields = array( 'id_module'         => $row['id'],
                                                            'name'              => $value['name'][0],
                                                            'value'             => is_null($value['default_value'][0]) ? '' : $value['default_value'][0],
                                                            'id_module_type'    => $module_type->fields['id']
                                                        );

                            $param_default->save();
                        }

                        if (!empty($value['paramchoice']))
                        {
                            foreach($value['paramchoice'] as $ckey => $cvalue)
                            {
                                $param_choice = new param_choice();
                                $param_choice->fields = array(  'id_module_type'    => $module_type->fields['id'],
                                                                'name'              => $param_type->fields['name'],
                                                                'value'             => $cvalue['value'][0],
                                                                'displayed_value'   => $cvalue['displayed_value'][0]
                                                            );
                                $param_choice->save();
                            }
                        }
                    }
                }

                if (!empty($pt['cms_object']))
                {
                    foreach($pt['cms_object'] as $key => $value)
                    {
                        $mb_cms_object = new mb_cms_object();
                        $mb_cms_object->fields = array( 'id_module_type'    => $module_type->fields['id'],
                                                        'label' => $value['label'][0],
                                                        'script' => $value['script'][0],
                                                        'select_id' => $value['select_id'][0],
                                                        'select_label' => $value['select_label'][0],
                                                        'select_table' => $value['select_table'][0]
                                                    );
                        $mb_cms_object->save();
                    }
                }

                if (!empty($pt['action']))
                {
                    foreach($pt['action'] as $key => $value)
                    {
                        $mb_action = new mb_action();
                        $mb_action->fields = array( 'id_module_type'    => $module_type->fields['id'],
                                                    'id_action' => $value['id_action'][0],
                                                    'label' => $value['label'][0],
                                                    'id_object' => (isset($value['id_object'][0])) ? $value['id_object'][0] : 0,
                                                    'role_enabled' => (isset($value['role_enabled'][0])) ? $value['role_enabled'][0] : 1
                                                );
                        $mb_action->save();
                    }
                }

                $detail = "Fichier '{$xmlfile_desc}' importé.";
            }
            else
            {
                $detail = "Fichier '{$xmlfile_desc}' mal formé. Vérifiez la structure XML du document.";
                $testok = false;
                $critical_error = true;
            }
        }
        else
        {
            $detail = "Fichier '{$xmlfile_desc}' non trouvé.";
            $testok = false;
            $critical_error = true;
        }

        $rapport[] = array('operation' => 'Chargement des paramètres/actions', 'detail' => $detail, 'res' => $testok);

        if (!$critical_error)
        {
            // =============
            // OPERATION 3 : Mise à jour SQL
            // =============
            $testok = true;
            $detail = '';

            if (file_exists($sqlfile))
            {
                $db->multiplequeries(file_get_contents($sqlfile));
                $detail = "Fichier '{$sqlfile}' importé";
            }
            else $detail = "Fichier '{$sqlfile}' non trouvé";

            $rapport[] = array('operation' => 'Création des tables/champs', 'detail' => $detail, 'res' => $testok);


            // =============
            // OPERATION 4 : Chargement de la métabase
            // =============
            $testok = true;
            $detail = '';

            ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMETABASE, $_GET['installmoduletype']);

            $db->query("DELETE FROM ploopi_mb_field WHERE id_module_type = {$_GET['idmoduletype']}");
            $db->query("DELETE FROM ploopi_mb_relation WHERE id_module_type = {$_GET['idmoduletype']}");
            $db->query("DELETE FROM ploopi_mb_schema WHERE id_module_type = {$_GET['idmoduletype']}");
            $db->query("DELETE FROM ploopi_mb_table WHERE id_module_type = {$_GET['idmoduletype']}");
            $db->query("DELETE FROM ploopi_mb_object WHERE id_module_type = {$_GET['idmoduletype']}");
            $db->query("DELETE FROM ploopi_mb_wce_object WHERE id_module_type = {$_GET['idmoduletype']}");

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

            $rapport[] = array('operation' => 'Chargement de la métabase', 'detail' => $detail, 'res' => $testok);
        }
    }
    $columns = array();
    $values = array();

    $columns['left']['operation']   = array('label' => 'Opération', 'width' => '250', 'options' => array('sort' => true));
    $columns['auto']['detail']      = array('label' => 'Détail', 'options' => array('sort' => true));
    $columns['right']['result']     = array('label' => 'Etat', 'width' => '60', 'options' => array('sort' => true));

    $c = 0;


    foreach($rapport as $op_detail)
    {
        $bullet = ($op_detail['res']) ? 'green' : 'red';
        $values[$c]['values']['operation'] = array('label' => htmlentities($op_detail['operation']));
        $values[$c]['values']['detail'] = array('label' => htmlentities($op_detail['detail']));
        $values[$c]['values']['result'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />");

        $c++;
    }

    $skin->display_array($columns, $values, 'array_updateproc');
}
?>
<div style="padding:4px;text-align:right;">
    <form action="<? echo ploopi_urlencode("{$scriptenv}?sysToolbarItem=install"); ?>" method="post">
    <input type="submit" class="flatbutton" value="<? echo _PLOOPI_CONTINUE; ?>">
    </form>
</div>
<? echo $skin->close_simplebloc(); ?>
