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
?>
<?
include_once './modules/system/xmlparser_mod.php';
include_once './modules/system/xmlparser_mb.php';
include_once './include/classes/class_xml2array.php';

if (empty($_GET['installmoduletype'])) ploopi_redirect("{$scriptenv}");

if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);

global $idmoduletype;
$idmoduletype = -1;

echo $skin->open_simplebloc(_SYSTEM_LABEL_INSTALLREPORT);
?>

<?
$select = "SELECT * FROM ploopi_module_type WHERE label = '".$db->addslashes($_GET['installmoduletype'])."'";
$db->query($select);
if ($db->numrows())
{
    ?>
    <div style="padding:4px;text-align:center;font-weight:bold;color:#a60000;">Module d�j� install� !</div>
    <?
}
else
{
    $modpath = "./install/{$_GET['installmoduletype']}";

    $sqlfile        = "{$modpath}/structure.sql";
    $xmlfile_desc   = "{$modpath}/description.xml";
    $xmlfile_data   = "{$modpath}/data.xml";
    $mbfile         = "{$modpath}/mb.xml";
    $srcfiles       = "{$modpath}/files";
    $destfiles      = "./modules/{$_GET['installmoduletype']}";

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
            $detail = 'Fichiers copi�s';
        }
        else
        {
            $detail = "Impossible de copier les fichiers dans '{$destfiles}'.";
            $testok = false;
            $critical_error = true;
        }
    }
    else $detail = 'Aucun fichier � copier.';

    $rapport[] = array('operation' => 'Copie des fichiers', 'detail' => $detail, 'res' => $testok);

    if (!$critical_error)
    {
        // =============
        // OPERATION 2 : Chargement des param�tres/actions
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
                include_once './modules/system/class_mb_action.php';
                include_once './modules/system/class_mb_cms_object.php';


                //ploopi_print_r($pt);
                $module_type = new module_type();
                $module_type->fields = array(   'label'         => $pt['label'][0],
                                                'version'       => $pt['version'][0],
                                                'author'        => $pt['author'][0],
                                                'date'          => $pt['date'][0],
                                                'description'   => $pt['description'][0]
                                            );

                $idmoduletype = $module_type->save();

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
                                                    'id_object' => (!empty($value['id_object'][0])) ? $value['id_object'][0] : 0
                                                );
                        $mb_action->save();
                    }
                }

                $detail = "Fichier '{$xmlfile_desc}' import�.";
            }
            else
            {
                $detail = "Fichier '{$xmlfile_desc}' mal form�. V�rifiez la structure XML du document.";
                $testok = false;
                $critical_error = true;
            }
        }
        else
        {
            $detail = "Fichier '{$xmlfile_desc}' non trouv�.";
            $testok = false;
            $critical_error = true;
        }

        $rapport[] = array('operation' => 'Chargement des param�tres/actions', 'detail' => $detail, 'res' => $testok);

        if (!$critical_error)
        {
            ploopi_create_user_action_log(_SYSTEM_ACTION_INSTALLMODULE, $_GET['installmoduletype']);

            // =============
            // OPERATION 3 : Cr�ation des tables/champs
            // =============
            $testok = true;
            $detail = '';

            if (file_exists($sqlfile))
            {
                $db->multiplequeries(file_get_contents($sqlfile));
                $detail = "Fichier '{$sqlfile}' import�";
            }
            else $detail = "Fichier '{$sqlfile}' non trouv�";

            $rapport[] = array('operation' => 'Cr�ation des tables/champs', 'detail' => $detail, 'res' => $testok);


            // =============
            // OPERATION 4 : Chargement des donn�es sp�cifiques
            // =============
            $testok = true;
            $detail = '';

            if (file_exists($xmlfile_data))
            {
                $xml_parser = xmlparser_mod();
                if (!xml_parse($xml_parser, file_get_contents($xmlfile_data)))
                {
                    $detail = sprintf("Erreur XML: %s � la ligne %d dans '%s'\n", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser), $xmlfile_data);
                    $testok = false;
                }
                else $detail = "Fichier '{$xmlfile_data}' import�";

                xml_parser_free($xml_parser);
            }
            else $detail = "Fichier '{$xmlfile_data}' non trouv�";

            $rapport[] = array('operation' => 'Chargement des donn�es sp�cifiques', 'detail' => $detail, 'res' => $testok);


            // =============
            // OPERATION 5 : Chargement de la m�tabase
            // =============
            $testok = true;
            $detail = '';

            if (file_exists($mbfile))
            {
                $xml_parser = xmlparser_mb();
                if (!xml_parse($xml_parser,  file_get_contents($mbfile)))
                {
                    $stop = sprintf("Erreur XML: %s � la ligne %d dans '%s'\n", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser), $mbfile);
                    $testok = false;
                }
                else $detail = "Fichier '{$mbfile}' import�";

                xml_parser_free($xml_parser);
            }
            else $detail = "Fichier '{$mbfile}' non trouv�";

            $rapport[] = array('operation' => 'Chargement de la m�tabase', 'detail' => $detail, 'res' => $testok);
        }
    }

    $columns = array();
    $values = array();

    $columns['left']['operation']   = array('label' => 'Op�ration', 'width' => '250', 'options' => array('sort' => true));
    $columns['auto']['detail']      = array('label' => 'D�tail', 'options' => array('sort' => true));
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

    $skin->display_array($columns, $values, 'array_installproc');
}
?>
<div style="padding:4px;text-align:right;">
    <form action="<? echo ploopi_urlencode("{$scriptenv}?sysToolbarItem=install"); ?>" method="post">
    <input type="submit" class="flatbutton" value="<? echo _PLOOPI_CONTINUE; ?>">
    </form>
</div>
</form>

<? echo $skin->close_simplebloc(); ?>
