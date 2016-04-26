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
 * Procédure de mise à jour d'un module
 *
 * @package system
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion des parsers XML
 */
include_once './modules/system/xmlparser_mb.php';
include_once './include/classes/xml2array.php';

if (empty($_GET['idmoduletype']) || empty($_GET['updatefrom']) || empty($_GET['updateto']) || !is_numeric($_GET['idmoduletype'])) ploopi_redirect("admin.php");

$objModuleType = new module_type();
if (!$objModuleType->open($_GET['idmoduletype'])) ploopi_redirect("admin.php");

$strModuleType = $objModuleType->fields['label'];

if (!ini_get('safe_mode')) ini_set('max_execution_time', 0);

echo $skin->open_simplebloc(_SYSTEM_LABEL_UPDATEREPORT.ploopi_htmlentities(" - {$strModuleType} {$_GET['updatefrom']} => {$_GET['updateto']}"));


$select = "SELECT version FROM ploopi_module_type WHERE id = '".$db->addslashes($strModuleType)."' AND version = '".$db->addslashes($_GET['updateto'])."'";
$db->query($select);
if ($db->numrows())
{
    ?>
    <div style="padding:4px;text-align:center;font-weight:bold;color:#a60000;">Module déjà mis à jour !</div>
    <?php
}
else
{
    ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMODULE, "{$strModuleType} {$_GET['updatefrom']} => {$strModuleType} {$_GET['updateto']}");

    $modpath = "./install/{$strModuleType}";

    $sqlpath =      "{$modpath}/update";
    $xmlfile_desc = "{$modpath}/description.xml";
    $xmlfile_ploopi = "{$modpath}/data_ploopi.xml";
    $xmlfile_mod =  "{$modpath}/data_mod.xml";
    $mbfile =       "{$modpath}/mb.xml";
    $srcfiles =     "{$modpath}/files";
    $destfiles =    "./modules/{$strModuleType}";

    $arrSqlUpdates = array();

    if (is_dir($sqlpath))
    {
        $dir = @opendir($sqlpath);
        while($file = readdir($dir))
        {
            if (is_file("{$sqlpath}/{$file}"))
            {
                $matches = array();
                if (preg_match("@^update_(.*).sql@i", $file, $matches))
                {
                    if (!empty($matches[1]) && version_compare($matches[1], $_GET['updatefrom']) > 0 && version_compare($matches[1], $_GET['updateto']) <= 0)
                    {
                        $arrSqlUpdates[$matches[1]] = $matches[0];
                    }
                }
            }
        }
    }

    uksort($arrSqlUpdates, 'version_compare');

    $critical_error = false;

    $rapport = array();

    // =============
    // OPERATION 1 : Copie des fichiers
    // =============
    $testok = true;
    $detail = '';
    if (file_exists($srcfiles))
    {
        if (is_writable(realpath($destfiles)))
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

        $module_type = new module_type();
        $module_type->open($_GET['idmoduletype']);

        $critical_error = $module_type->update_description($xmlfile_desc, $rapport);

        if (!$critical_error)
        {
            // =============
            // OPERATION 3 : Mise à jour SQL
            // =============
            $testok = true;
            $detail = array();

            foreach($arrSqlUpdates as $sqlfile)
            {
                if (file_exists("{$sqlpath}/{$sqlfile}"))
                {
                    $db->multiplequeries(file_get_contents("{$sqlpath}/{$sqlfile}"));
                    $detail[] = "Fichier '{$sqlfile}' importé";
                }
                else $detail[] = "Fichier '{$sqlfile}' non trouvé";
            }

            $rapport[] = array('operation' => 'Mise à jour des tables/champs', 'detail' => implode('<br />', $detail), 'res' => $testok);

            // =============
            // OPERATION 4 : Chargement de la métabase
            // =============
            $testok = true;
            $detail = '';

            ploopi_create_user_action_log(_SYSTEM_ACTION_UPDATEMETABASE, $strModuleType);

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
        $values[$c]['values']['operation'] = array('label' => $op_detail['operation']);
        $values[$c]['values']['detail'] = array('label' => $op_detail['detail']);
        $values[$c]['values']['result'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />");

        $c++;
    }

    $skin->display_array($columns, $values, 'array_updateproc');
}
?>
<div style="padding:4px;text-align:right;">
    <form action="<?php echo ploopi_urlencode("admin.php?sysToolbarItem=install&reloadsession"); ?>" method="post">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_CONTINUE; ?>">
    </form>
</div>
<?php echo $skin->close_simplebloc(); ?>
