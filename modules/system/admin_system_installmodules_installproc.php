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
 * Procédure d'installation d'un module
 *
 * @package system
 * @subpackage system
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Inclusion des parsers XML
 */
include_once './modules/system/xmlparser_mod.php';
include_once './modules/system/xmlparser_mb.php';

if (empty($_GET['installmoduletype']) || !preg_match('@^([a-z0-9_\-])+$@i', $_GET['installmoduletype'])) ploopi\output::redirect('admin.php');

if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);

global $idmoduletype;
$idmoduletype = -1;

echo ploopi\skin::get()->open_simplebloc(_SYSTEM_LABEL_INSTALLREPORT);
?>

<?php
$select = "SELECT * FROM ploopi_module_type WHERE label = '".ploopi\db::get()->addslashes($_GET['installmoduletype'])."'";
ploopi\db::get()->query($select);
if (ploopi\db::get()->numrows())
{
    ?>
    <div style="padding:4px;text-align:center;font-weight:bold;color:#a60000;">Module déjà installé !</div>
    <?php
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

    /**
     * OPERATION 1 : Copie des fichiers
     */
    $testok = true;
    $detail = '';
    if (file_exists($srcfiles))
    {
        if (is_writable(realpath("./modules/")))
        {
            ploopi\fs::copydir($srcfiles , $destfiles);
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
        /**
         * OPERATION 2 : Chargement des paramètres/actions
         */
        $testok = true;
        $detail = '';

        if (file_exists($xmlfile_desc))
        {
            $fp = fopen($xmlfile_desc, 'r');
            $data = fread ($fp, filesize ($xmlfile_desc));
            fclose($fp);

            $x2a = new ploopi\xml2array();
            $xmlarray = $x2a->parse($data);
            if ($xmlarray)
            {
                $pt = &$xmlarray['root']['ploopi'][0]['moduletype'][0];

                //ploopi\output::print_r($pt);
                $module_type = new ploopi\module_type();
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

                        $param_type = new ploopi\param_type();
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
                                $param_choice = new ploopi\param_choice();
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
                        $mb_cms_object = new ploopi\mb_cms_object();
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
                        $mb_action = new ploopi\mb_action();
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
            ploopi\user_action_log::record(_SYSTEM_ACTION_INSTALLMODULE, $_GET['installmoduletype']);

            /**
             * OPERATION 3 : Création des tables/champs
             */
            $testok = true;
            $detail = '';

            if (file_exists($sqlfile))
            {
                ploopi\db::get()->multiplequeries(file_get_contents($sqlfile));
                $detail = "Fichier '{$sqlfile}' importé";
            }
            else $detail = "Fichier '{$sqlfile}' non trouvé";

            $rapport[] = array('operation' => 'Création des tables/champs', 'detail' => $detail, 'res' => $testok);

            /**
             * OPERATION 4 : Chargement des données spécifiques
             */
            $testok = true;
            $detail = '';

            if (file_exists($xmlfile_data))
            {
                $xml_parser = xmlparser_mod();
                if (!xml_parse($xml_parser, file_get_contents($xmlfile_data)))
                {
                    $detail = sprintf("Erreur XML: %s à la ligne %d dans '%s'\n", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser), $xmlfile_data);
                    $testok = false;
                }
                else $detail = "Fichier '{$xmlfile_data}' importé";

                xml_parser_free($xml_parser);
            }
            else $detail = "Fichier '{$xmlfile_data}' non trouvé";

            $rapport[] = array('operation' => 'Chargement des données spécifiques', 'detail' => $detail, 'res' => $testok);

            /**
             * OPERATION 5 : Chargement de la métabase
             */
            $testok = true;
            $detail = '';

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
        $values[$c]['values']['operation'] = array('label' => ploopi\str::htmlentities($op_detail['operation']));
        $values[$c]['values']['detail'] = array('label' => ploopi\str::htmlentities($op_detail['detail']));
        $values[$c]['values']['result'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />");

        $c++;
    }

    ploopi\skin::get()->display_array($columns, $values, 'array_installproc');
}
?>
<div style="padding:4px;text-align:right;">
    <form action="<?php echo ploopi\crypt::urlencode("admin.php?sysToolbarItem=install"); ?>" method="post">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_CONTINUE; ?>">
    </form>
</div>

<?php echo ploopi\skin::get()->close_simplebloc(); ?>
