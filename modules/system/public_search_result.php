<?php
/*
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
 * Interface de résultat du moteur de recherche intégrale
 * 
 * @package system
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Récupération des paramètres de recherche
 */

if (isset($_REQUEST['system_search_keywords']))     $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] = $_REQUEST['system_search_keywords'];
if (isset($_REQUEST['system_search_workspace']))    $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'] = $_REQUEST['system_search_workspace'];
if (isset($_REQUEST['system_search_module']))       $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module'] = $_REQUEST['system_search_module'];
if (isset($_REQUEST['system_search_date1']))    $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'] = $_REQUEST['system_search_date1'];
if (isset($_REQUEST['system_search_date2']))    $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'] = $_REQUEST['system_search_date2'];

/**
 * Initialisation de la session
 */

if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'] = '';

if (!empty($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords']))
{
    /* Mini Algo
     *
     * 1. On extrait les racines (stem) et mots clés (keyword) de la requête de recherche
     * 2. On effectue deux recherches. Une sur les racines, l'autre sur les mots clés.
     * 3. On traite les résultats de recherche en indiquant pour chaque élément de réponse la pertinence (relevance) totale, le nombre de liens (mot clé  ou stem / élément)
     * 4. On calcule ensuite le ratio mots clés trouvés / mots clés cherchés
     * 5. On calcule ensuite la pertinence moyenne avec cette formule : ((pertinence totale)/(mots clés trouvés))*(ratio mots clés trouvés)
     *
     * */


    $arrObjectTypes = array();
    $arrRelevance = array();
    $arrStems = array();
    $arrSearch = array();

    // on parcourt la liste des modules de l'espace courant
    $arrAvailableModules = array();
    foreach ($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $modid)
    {
        if ($_SESSION['ploopi']['modules'][$modid]['active']) $arrAvailableModules[] = $modid;
    }
    

    // on construit $arrObjectTypes, la liste des objets ploopi
    $db->query( '
                SELECT      mbo.*,
                            m.id as module_id,
                            m.label as module_label,
                            mt.label as module_type

                FROM        ploopi_mb_object mbo

                INNER JOIN  ploopi_module m
                ON          m.id_module_type = mbo.id_module_type
                AND         m.id IN ('.implode(',', $arrAvailableModules).')

                INNER JOIN  ploopi_module_type mt
                ON          mt.id = mbo.id_module_type
                ');

    while ($row = $db->fetchrow())
    {
        if (empty($arrObjectTypes[$row['module_id']]))
        {
            $arrObjectTypes[$row['module_id']]['label'] = $row['module_label'];
            $arrObjectTypes[$row['module_id']]['type'] = $row['module_type'];
            $arrObjectTypes[$row['module_id']]['objects'] = array();
        }
        $arrObjectTypes[$row['module_id']]['objects'][$row['id']] = array('label' => $row['label'], 'script' => $row['script']);
    }

    $arrRelevance = ploopi_search($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'], -1, '', (empty($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module'])) ? $arrAvailableModules : $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module']);

    if (empty($arrRelevance))
    {
        ?>
        <div style="padding:4px;font-weight:bold;background-color:#f0f0f0;border-top:2px solid #c0c0c0;">
        Saisissez un mot clé puis cliquez sur "Rechercher" ou appuyez sur "Entrée"
        </div>
        <?php
    }
    else
    {
        $columns = array();
        $values = array();
        $c = 0;

        $columns['left']['relevance']       = array('label' => 'Pert.', 'width' => 65, 'options' => array('sort' => true));
        $columns['auto']['label']           = array('label' => 'Libellé', 'options' => array('sort' => true));
        $columns['right']['timestp_lastindex']      = array('label' => 'Indexé le', 'width' => '90', 'options' => array('sort' => true));
        $columns['right']['timestp_create']         = array('label' => 'Ajouté le', 'width' => '140', 'options' => array('sort' => true));
        $columns['right']['user']           = array('label' => 'Utilisateur', 'width' => '120', 'options' => array('sort' => true));
        $columns['right']['workspace']      = array('label' => 'Espace', 'width' => '120', 'options' => array('sort' => true));
        $columns['right']['module']         = array('label' => 'Module', 'width' => '120', 'options' => array('sort' => true));
        $columns['right']['object_type']    = array('label' => 'Type d\'Objet', 'width' => '120', 'options' => array('sort' => true));

        // on parcourt le tableau des réponses
        

        foreach ($arrRelevance as $row)
        {
            if (isset($arrObjectTypes[$row['id_module']]['objects'][$row['id_object']]))
            {
                $type = $arrObjectTypes[$row['id_module']]['type'];
    
                $objUser = new user();
                $strUserName = ($objUser->open($row['id_user'])) ? "{$objUser->fields['firstname']} {$objUser->fields['lastname']}" : '';
    
                // inclusion des fonctions/constantes proposées par le module
                ploopi_init_module($type, false, false, false);
    
                // on cherche si on fonction de validation d'objet existe pour ce module
                $boolRecordIsEnabled = true;
                $funcRecordIsEnabled = "{$type}_record_isenabled";
                if (function_exists($funcRecordIsEnabled))
                {
                    // si la fonction existe, on l'appelle pour chaque enregistrement
                    $boolRecordIsEnabled = $funcRecordIsEnabled($row['id_object'], $row['id_record'], $row['id_module']);
                }
    
                if ($boolRecordIsEnabled && !empty($arrObjectTypes[$row['id_module']]))
                {
                    $blue = 128;
                    if ($row['relevance']>=50)
                    {
                        $red = 255-($blue*($row['relevance']-50))/50;
                        $green = 255;
                    }
                    else
                    {
                        $red = 255;
                        $green = (255-$blue)+($blue*$row['relevance'])/50;
                    }
    
                    $color = sprintf("%02X%02X%02X",$red,$green,$blue);
    
    
                    $l_timestp_lastindex = ploopi_timestamp2local($row['timestp_lastindex']);
                    $l_timestp_create = ploopi_timestamp2local($row['timestp_create']);
    
                    $object_script = str_replace(
                                                    array(
                                                        '<IDRECORD>',
                                                        '<IDMODULE>',
                                                        '<IDWORKSPACE>'
                                                    ),
                                                    array(
                                                        $row['id_record'],
                                                        $row['id_module'],
                                                        $row['id_workspace']
                                                    ),
                                                    $arrObjectTypes[$row['id_module']]['objects'][$row['id_object']]['script']
                                                );
                                                
                    $objWorkspace = new workspace();
                    $strWorkspaceLabel = ($objWorkspace->open($row['id_workspace'])) ? $objWorkspace->fields['label'] : '';
    
                    $values[$c]['values']['relevance'] = array('label' => sprintf("<span style=\"width:12px;height:12px;float:left;border:1px solid #a0a0a0;background-color:#%s;margin-right:3px;\"></span>%d %%", $color, $row['relevance']), 'sort_label' => $row['relevance']);
                    $values[$c]['values']['label'] = array('label' => $row['label']);
                    $values[$c]['values']['timestp_lastindex'] = array('label' => $l_timestp_lastindex['date'], 'sort_label' => $row['timestp_lastindex']);
                    $values[$c]['values']['timestp_create'] = array('label' => $l_timestp_create['date'].' '.$l_timestp_create['time'], 'sort_label' => $row['timestp_create']);
                    $values[$c]['values']['user'] = array('label' => $strUserName);
                    $values[$c]['values']['workspace'] = array('label' => $strWorkspaceLabel);
                    $values[$c]['values']['module'] = array('label' => $arrObjectTypes[$row['id_module']]['label']);
                    $values[$c]['values']['object_type'] = array('label' => $arrObjectTypes[$row['id_module']]['objects'][$row['id_object']]['label']);
    
                    $values[$c]['description'] = $row['label'];
                    $values[$c]['link'] = ploopi_urlencode("admin.php?ploopi_mainmenu=1&{$object_script}");
                    $values[$c]['style'] = '';
                }
    
                $c++;
            }
        }
        ?>
        <div style="background-color:#f0f0f0;border-top:2px solid #c0c0c0;">
        <?php $skin->display_array($columns, $values, 'system_search', array('sortable' => true, 'orderby_default' => 'relevance', 'sort_default' => 'DESC')); ?>
        </div>
        <?php
    }
}
?>
