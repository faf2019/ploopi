<?php
/*
    Copyright (c) 2009 Ovensia
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
 * Interface de modification d'une requête
 *
 * @package dbreport
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Inclusion de la classe dbreport_query
 */
include_once './modules/dbreport/classes/class_dbreport_query.php';

global $arrDbReportTypes;

$objDbrQuery = new dbreport_query();

if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']) && $objDbrQuery->open($_GET['dbreport_query_id']))
{
    if ($objDbrQuery->fields['locked'] && !ploopi_isactionallowed(_DBREPORT_ACTION_LOCK)) ploopi_logout();
    
    echo $skin->open_simplebloc('Modification de la requête &laquo; '.htmlentities($objDbrQuery->fields['label']).' &raquo;');
    ?>
    <div class="ploopi_tabs">
        <a title="Ajouter des tables à la requête" style="font-weight:bold;" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(400, event, 'dbreport_querytable_add', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=dbreport_querytable_add&dbreport_query_id={$_GET['dbreport_query_id']}"); ?>', 'POST');"><img src="./modules/dbreport/img/ico_add_table.png" />Ajouter des tables</a> 
        <a title="Ajouter des champs à la requête" style="font-weight:bold;" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(400, event, 'dbreport_queryfield_add', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=dbreport_queryfield_add&dbreport_query_id={$_GET['dbreport_query_id']}"); ?>', 'POST');"><img src="./modules/dbreport/img/ico_add_field.png" />Ajouter des champs</a> 
        <a title="Paramètres de la requête" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(400, event, 'dbreport_query_modify', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=dbreport_query_modify&dbreport_query_id={$_GET['dbreport_query_id']}"); ?>', 'POST');"><img src="./modules/dbreport/img/ico_param.png" />Paramètres</a> 
        <a title="Exécuter / Exporter la requête" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(550, event, 'dbreport_query_export_popup', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=dbreport_query_export&dbreport_query_id={$_GET['dbreport_query_id']}");?>', 'POST');"><img src="./modules/dbreport/img/ico_execute.png" />Exécuter / Exporter</a> 
        <a title="Cloner la requête" href="javascript:void(0);" onclick="if (confirm('Êtes vous certains de vouloir cloner cette requête ?')) document.location.href='<? echo ploopi_urlencode("admin-light.php?ploopi_op=dbreport_query_clone&dbreport_query_id={$_GET['dbreport_query_id']}"); ?>';"><img src="./modules/dbreport/img/ico_clone.png" />Cloner</a> 
        <a title="Retour à la liste des requêtes" href="<?php echo ploopi_urlencode('admin.php'); ?>"><img src="./modules/dbreport/img/ico_back.png" />Retour à la liste des requêtes</a>
    </div>    

    <div id="dbreport_query_tables">
        <h1 class="dbreport_title">Tables</h1>
        <?
        // Sélection des relations de la requete courante
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('mbs.tablesrc, mbs.tabledest');
        $objQuery->add_from('ploopi_mod_dbreport_querytable as tablesrc');
        $objQuery->add_from('ploopi_mod_dbreport_querytable as tabledest');
        $objQuery->add_from('ploopi_mb_schema mbs');
        $objQuery->add_where('tablesrc.tablename = mbs.tablesrc');
        $objQuery->add_where('tabledest.tablename = mbs.tabledest');
        $objQuery->add_where('tablesrc.id_query = %d', $_GET['dbreport_query_id']);
        $objQuery->add_where('tabledest.id_query = %d', $_GET['dbreport_query_id']);
        $objRs = $objQuery->execute();
                
        // Tables de relations/dépendances
        $arrTableRelation = array();

        // Tables à garder 
        $arrTableToKeep = array();
        
        while ($row = $objRs->fetchrow())
        {
            $arrTableRelation[$row['tablesrc']][$row['tabledest']] = 1; 
            $arrTableRelation[$row['tabledest']][$row['tablesrc']] = 1; 
        }
        
        // Cas particulier, 2 tables ou moins, on peut supprimer toutes les tables 
        // Sinon on détecte les relations "fragiles" (celles qui n'ont qu'une table liée (on ne doit pas supprimer la table liée)
        if (sizeof($arrTableRelation) > 2) foreach($arrTableRelation as $arrRelation) if (sizeof($arrRelation) == '1') $arrTableToKeep[key($arrRelation)] = 1; 
        
        $arrTableRelation = $objRs->getarray();
        
        // Sélection des tables de la requête
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('drt.tablename, drt.id, mbt.label');
        $objQuery->add_from('ploopi_mod_dbreport_querytable drt');
        $objQuery->add_from('ploopi_mb_table mbt');
        $objQuery->add_where('id_query = %d', $_GET['dbreport_query_id']);
        $objQuery->add_where('drt.tablename = mbt.name');
        $objQuery->add_orderby('drt.tablename');
        
        $arrTableReq = $objQuery->execute()->getarray();
        
        // pour toutes les tables de la requête (peut on les supprimer ?)
        foreach($arrTableReq as $arrTable)
        {
            ?>
            <div class="dbreport_table">
                <div class="dbreport_table_title">
                    <?
                    // Peut-on supprimer la table ?
                    if (!isset($arrTableToKeep[$arrTable['tablename']]))
                    {
                        ?>
                        <a title="Supprimer la table &laquo; <? echo htmlentities($arrTable['label']);?> &raquo;" href="javascript:void(0);" onclick="javascript:if (confirm('Êtes-vous certain de vouloir supprimer cette table ?')) document.location.href='<? echo ploopi_urlencode("admin-light.php?ploopi_op=dbreport_querytable_delete&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_querytable_id={$arrTable['id']}"); ?>';"><img src="./modules/dbreport/img/ico_close.png" /></a>
                        <?
                    }
                    ?>
        
                    <span><? echo $arrTable['label'];?></span>
                </div>
                <div class="dbreport_table_fields">
                    <?
                    $objQuery = new ploopi_query_select();
                    $objQuery->add_from('ploopi_mb_field');
                    $objQuery->add_where('tablename = %s', $arrTable['tablename']);
                    $objQuery->add_where('visible = 1');
                    $objRs = $objQuery->execute();
            
                    while($row = $objRs->fetchrow())
                    {
                        $strColor = (isset($strColor) && $strColor == $skin->values['bgline1']) ? $skin->values['bgline2'] : $skin->values['bgline1']
                        ?>
                        <div class="dbreport_table_field" style="background-color:<? echo $strColor; ?>;"><strong><? echo htmlentities($row['label']); ?></strong><? if ($row['label'] != $row['name']) echo "<span>(".htmlentities($row['name']).")</span>"; ?><br /><em style="color:#888;"><? echo $arrDbReportTypes[dbreport_getbasictype($row['type'])]; ?></em></div>
                        <?
                    }
                    ?>
                </div>
            </div>
            <?
        }
        ?>
    </div>
    
    
    <?php

    $arrTableRelationDetail = array();
    
    // Sélection des relations de la requete courante
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('mbr.tablesrc, mbr.fieldsrc, mbr.tabledest, mbr.fielddest, qr.active');
    $objQuery->add_from('ploopi_mod_dbreport_queryrelation qr');
    $objQuery->add_from('ploopi_mb_relation mbr');
    $objQuery->add_where('qr.id_query = %d', $_GET['dbreport_query_id']);
    $objQuery->add_where('qr.tablename_src = mbr.tablesrc');
    $objQuery->add_where('qr.tablename_dest = mbr.tabledest');
    $objRs = $objQuery->execute();

    while ($row = $objRs->fetchrow())
    {
        $strRelationKey = "{$row['tablesrc']},{$row['tabledest']}";
        $strRelationKey2 = "{$row['tabledest']},{$row['tablesrc']}";
        
        $arrTableRelationDetail[$strRelationKey]['tablesrc'] = $row['tablesrc']; 
        $arrTableRelationDetail[$strRelationKey]['tabledest'] = $row['tabledest']; 
        $arrTableRelationDetail[$strRelationKey]['active'] = $row['active']; 
        $arrTableRelationDetail[$strRelationKey]['fields'][] = array('src' => $row['fieldsrc'], 'dest' => $row['fielddest']);
        
        // Détection des relations multiples entre tables
        if ($row['active'])
        {
            if (!isset($arrTableRelationCount[$strRelationKey])) $arrTableRelationCount[$strRelationKey] = 0;
            if (!isset($arrTableRelationCount[$strRelationKey2])) $arrTableRelationCount[$strRelationKey2] = 0;
            $arrTableRelationCount[$strRelationKey]++;
            $arrTableRelationCount[$strRelationKey2]++;
        }
    }
    
    ?>
    <div id="dbreport_query_fields">
        <h1 class="dbreport_title">Relations</h1>
        <?
        $arrColumns = array();
        $arrValues = array();
        
        $arrColumns['left']['src'] =
            array(
                'label' => 'Table source',
                'width' => 250,
                'options' => array('sort' => true)
            );
            
        $arrColumns['left']['dest'] =
            array(
                'label' => 'Table destination',
                'width' => 250,
                'options' => array('sort' => true)
            );
            
        $arrColumns['auto']['fields'] =
            array(
                'label' => 'Champs',
            );

        $arrColumns['right']['comment'] =
            array(
                'label' => 'Commentaire',
                'width' => 250,
            );
            
        $arrColumns['right']['active'] =
            array(
                'label' => 'Active',
                'width' => 70,
                'options' => array('sort' => true)
            );
            
            
        foreach($arrTableRelationDetail as $strRelationKey => $arrRelationDetail)
        {
            $arrRelationFields = array();
            foreach($arrRelationDetail['fields'] as $arrFields) $arrRelationFields[] = implode(' &raquo; ', $arrFields); 
            
            // Relation multiple ?
            $booMulti = isset($arrTableRelationCount[$strRelationKey]) && $arrTableRelationCount[$strRelationKey] > 1;
            $strComment = '';
            if ($booMulti) $strComment = 'Relation multiple, vous devez préciser si cette relation doit être utilisée';
            elseif ($arrRelationDetail['active']) $strComment = 'Cette relation ne peut pas être modifiée';
            
            $arrValues[] = array(
                'values' => array(
                    'src' => array('label' => $arrRelationDetail['tablesrc']),
                    'dest' => array('label' => $arrRelationDetail['tabledest']),
                    'fields' => array('label' => implode('<br />', $arrRelationFields)),
                    'active' => array('label' => $arrRelationDetail['active'] ? 'Oui' : 'Non', 'style' => 'font-weight:bold;color:#'.($arrRelationDetail['active'] ? '00a600' : 'a60000')),
                    'comment' => array('label' => $strComment, 'style' => $booMulti ? 'color:#a60000;' : 'color:#aaa;')
                ),
                'description' => $booMulti || !$arrRelationDetail['active'] ? 'Activer/Désactiver la relation' : 'Cette relation ne peut pas être modifiée',
                'link' => $booMulti || !$arrRelationDetail['active'] ? ploopi_urlencode("admin.php?ploopi_op=dbreport_queryrelation_modify&dbreport_query_id={$objDbrQuery->fields['id']}&dbreport_queryrelation_tablesrc={$arrRelationDetail['tablesrc']}&dbreport_queryrelation_tabledest={$arrRelationDetail['tabledest']}&dbreport_queryrelation_active=".($arrRelationDetail['active'] ? 0 : 1)) : ''
            );    
        }
            
        $skin->display_array($arrColumns, $arrValues, 'dbreport_relation_list', array('sortable' => true, 'orderby_default' => 'src', 'limit' => 25));
        ?>
    </div>
            
    
    <div id="dbreport_query_fields">
        <h1 class="dbreport_title">Champs / Propriétés</h1>
        <?
        $arrColumns = array();
        $arrValues = array();
        
        $arrColumns['auto']['field'] =
            array(
                'label' => 'Module / Table / Champ / Intitulé / Type',
            );
            
        $arrColumns['right']['position'] =
            array(
                'label' => 'Pos',
                'width' => 50,
                'options' => array('sort' => true)
            );
            
        $arrColumns['right']['or'] =
            array(
                'label' => 'Ou',
                'width' => 120,
            );
            
        $arrColumns['right']['criteria'] =
            array(
                'label' => 'Critère',
                'width' => 120,
            );
            
        /*$arrColumns['right']['series'] =
            array(
                'label' => 'Série',
                'width' => 50,
            );*/
            
        $arrColumns['right']['visible'] =
            array(
                'label' => 'Afficher',
                'width' => 80,
            );
            
        $arrColumns['right']['sort'] =
            array(
                'label' => 'Tri',
                'width' => 80,
            );
    
        $arrColumns['right']['operation'] =
            array(
                'label' => 'Opération',
                'width' => 100,
            );
            
        $arrColumns['right']['function'] =
            array(
                'label' => 'Fonction',
                'width' => 100,
            );
            
        $arrColumns['actions_right']['actions'] =
            array(
                'label' => 'Actions',
                'width' => 82
            );
        
    
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('
            dqf.*,
            mbf.name as field_name,
            mbf.label as field_label,
            mbf.type,
            mbt.name as table_name,
            mbt.label as table_label,
            mt.label as mt_label
        ');
        $objQuery->add_from('ploopi_mod_dbreport_queryfield dqf');
        $objQuery->add_from('ploopi_mb_field mbf');
        $objQuery->add_from('ploopi_mb_table mbt');
        $objQuery->add_from('ploopi_module_type mt');
        $objQuery->add_where('dqf.id_query = %d', $_GET['dbreport_query_id']);
        $objQuery->add_where('dqf.tablename = mbt.name');
        $objQuery->add_where('dqf.fieldname = mbf.name');
        $objQuery->add_where('dqf.tablename = mbf.tablename');
        $objQuery->add_where('mbt.id_module_type = mt.id');
        $objQuery->add_orderby('dqf.position');        
        $objRs = $objQuery->execute();
        
        $arrLabels = array();
        
        while ($row = $objRs->fetchrow())
        {
            $arrValues[] = array(
                'values' => array(
                    'field' => array('label' => "[{$row['mt_label']}] {$row['table_name']}.{$row['field_name']} : <strong>{$row['label']}</strong>". (isset($arrLabels[$row['label']]) ? "<em style=\"color:#a60000;\">&nbsp;(Doublon d'intitulé)</em>" : '') ."<br /><em style=\"color:#888;\">".$arrDbReportTypes[dbreport_getbasictype($row['type'])].'</em>'),
                    'label' => array('label' => isset($arrLabels[$row['label']]) ? "<em style=\"color:#a60000;\">{$row['label']} (Doublon d'intitulé)</em>" : $row['label']),
                    'function' => array('label' => $row['function']),
                    'operation' => array('label' => isset($arrDbReportOperations[$row['operation']]) ? $arrDbReportOperations[$row['operation']] : ''),
                    'sort' => array('label' => isset($arrDbReportSort[$row['sort']]) ? $arrDbReportSort[$row['sort']] : ''),
                    'visible' => array('label' => $row['visible'] ? 'Oui' : 'Non'),
                    'series' => array('label' => $row['series'] ? 'Oui' : 'Non'),
                    'criteria' => array('label' => (isset($arrDbReportCriteria[$row['type_criteria']]) ? $arrDbReportCriteria[$row['type_criteria']] : '')." {$row['criteria']}"),
                    'or' => array('label' => (isset($arrDbReportCriteria[$row['type_or']]) ? $arrDbReportCriteria[$row['type_or']] : '')." {$row['or']}"),
                    'actions' => array(
                        'label' => '
                            <a title="Descendre le champ" href="'.ploopi_urlencode("admin-light.php?ploopi_op=dbreport_queryfield_position&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_queryfield_id={$row['id']}&dbreport_fieldposition=lower").'"><img src="./modules/dbreport/img/arrow_down.png"></a>
                            <a title="Monter le champ" href="'.ploopi_urlencode("admin-light.php?ploopi_op=dbreport_queryfield_position&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_queryfield_id={$row['id']}&dbreport_fieldposition=upper").'"><img src="./modules/dbreport/img/arrow_up.png"></a>
                            <a title="Modifier le champ" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(\'500\', event, \'dbreport_queryfield_modify\', \'admin-light.php\', \''.ploopi_queryencode("ploopi_op=dbreport_queryfield_modify&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_queryfield_id={$row['id']}").'\',\'POST\');"><img src="./modules/dbreport/img/ico_modify.png" /></a>
                            <a title="Supprimer le champ" href="javascript:void(0);" onclick="javascript:if (confirm(\'Êtes vous certain de vouloir supprimer cette ligne ?\')) document.location.href=\''.ploopi_urlencode("admin-light.php?ploopi_op=dbreport_queryfield_delete&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_queryfield_id={$row['id']}").'\';"><img src="./modules/dbreport/img/ico_delete.png" /></a>
                        ' 
                    ),
                    'position' => array('label' => $row['position'], 'sort_label' => $row['position'])
                ),
                'description' => 'Modifier le champ',
                'link' => 'javascript:void(0);',
                'onclick' => "ploopi_xmlhttprequest_topopup('500', event, 'dbreport_queryfield_modify', 'admin-light.php', '".ploopi_queryencode("ploopi_op=dbreport_queryfield_modify&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_queryfield_id={$row['id']}")."','POST')",
                'style' => ''
            );    

            $arrLabels[$row['label']] = 1;
        }
            
        $skin->display_array($arrColumns, $arrValues, 'dbreport_field_list', array('sortable' => true, 'orderby_default' => 'position', 'limit' => 25));
        ?>
    </div>
    <?    
}
else
{
    echo $skin->open_simplebloc('Requête inconnue');
}

echo $skin->close_simplebloc();

/*
 * Conseils à ajouter :
 * 
 * NE pas mélanger "Fonction" avec Opération de type "Compte" "Somme" "Moyenne"
 * NE pas utiliser plusieurs fois le même intitulé
 */
?>