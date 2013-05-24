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
include_once './include/classes/form.php';

$objDbrQuery = new dbreport_query();

if (isset($_GET['dbreport_query_id']) && is_numeric($_GET['dbreport_query_id']) && $objDbrQuery->open($_GET['dbreport_query_id']))
{
    if ($objDbrQuery->fields['locked'] && !ploopi_isactionallowed(dbreport::_ACTION_LOCK)) ploopi_logout();

    echo $skin->open_simplebloc('Modification de la requête &laquo; '.htmlentities($objDbrQuery->fields['label']).' &raquo;');
    ?>
    <div class="ploopi_tabs">
        <a title="Ajouter des tables à la requête" style="font-weight:bold;" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(400, event, 'dbreport_querytable_add', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=dbreport_querytable_add&dbreport_query_id={$_GET['dbreport_query_id']}"); ?>', 'POST');"><img src="./modules/dbreport/img/ico_add_table.png" /><span>Ajouter des tables</span></a>
        <a title="Ajouter des champs à la requête" style="font-weight:bold;" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(650, event, 'dbreport_queryfield_add', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=dbreport_queryfield_add&dbreport_query_id={$_GET['dbreport_query_id']}"); ?>', 'POST');"><img src="./modules/dbreport/img/ico_add_field.png" /><span>Ajouter des champs</span></a>
        <a title="Paramètres de la requête" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(400, event, 'dbreport_query_modify', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=dbreport_query_modify&dbreport_query_id={$_GET['dbreport_query_id']}"); ?>', 'POST');"><img src="./modules/dbreport/img/ico_param.png" /><span>Paramètres</span></a>
        <a title="Exécuter / Exporter la requête" href="javascript:void(0);" onclick="javascript:ploopi_xmlhttprequest_topopup(550, event, 'dbreport_query_export_popup', 'admin-light.php', '<? echo ploopi_queryencode("ploopi_op=dbreport_query_export&dbreport_query_id={$_GET['dbreport_query_id']}");?>', 'POST');"><img src="./modules/dbreport/img/ico_execute.png" /><span>Exécuter / Exporter</span></a>
        <a title="Cloner la requête" href="javascript:void(0);" onclick="if (confirm('Êtes vous certains de vouloir cloner cette requête ?')) document.location.href='<? echo ploopi_urlencode("admin-light.php?ploopi_op=dbreport_query_clone&dbreport_query_id={$_GET['dbreport_query_id']}"); ?>';"><img src="./modules/dbreport/img/ico_clone.png" /><span>Cloner</span></a>
        <a title="Retour à la liste des requêtes" href="<?php echo ploopi_urlencode('admin.php'); ?>"><img src="./modules/dbreport/img/ico_back.png" /><span>Retour à la liste des requêtes</span></a>
    </div>

    <div>
        <h1 class="dbreport_title">Tables</h1>
        <div style="box-shadow:inset 0 0 6px rgba(0,0,0,0.2);border-bottom:1px solid #aaa;overflow:auto;">
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
            $arrTableDetail = array();

            // pour toutes les tables de la requête (peut on les supprimer ?)
            foreach($arrTableReq as $intKey => $rowTable)
            {
                // ploopi_print_r($rowTable);
                // ploopi_print_r(dbreport::getTableInfo($rowTable['tablename']));
                // ploopi_print_r(dbreport::getTableFields($rowTable['tablename']));
                // ploopi_print_r(dbreport::getTableIndexes($rowTable['tablename']));

                $rowTableInfo = $arrTableDetail[$rowTable['tablename']]['info'] = dbreport::getTableInfo($rowTable['tablename']);
                $arrTableDetail[$rowTable['tablename']]['fields'] = dbreport::getTableFields($rowTable['tablename']);
                $arrTableDetail[$rowTable['tablename']]['indexes'] = dbreport::getTableIndexes($rowTable['tablename']);

                ?>
                <div class="dbreport_table">
                    <div class="dbreport_table_title">
                        <?
                        // Peut-on supprimer la table ?
                        if (!isset($arrTableToKeep[$rowTable['tablename']]))
                        {
                            ?>
                            <a title="Supprimer la table &laquo; <? echo htmlentities($rowTable['label']);?> &raquo;" href="javascript:void(0);" onclick="javascript:if (confirm('Êtes-vous certain de vouloir supprimer cette table ?')) document.location.href='<? echo ploopi_urlencode("admin-light.php?ploopi_op=dbreport_querytable_delete&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_querytable_id={$rowTable['id']}"); ?>';"><img src="./modules/dbreport/img/ico_close.png" /></a>
                            <?
                        }
                        ?>

                        <span><? echo $rowTable['label'];?></span>
                    </div>
                    <div class="dbreport_table_fields">
                        <?
                        $objQuery = new ploopi_query_select();
                        $objQuery->add_from('ploopi_mb_field');
                        $objQuery->add_where('tablename = %s', $rowTable['tablename']);
                        $objQuery->add_where('visible = 1');
                        $objRs = $objQuery->execute();

                        while($row = $objRs->fetchrow())
                        {
                            ?>
                            <div class="dbreport_table_field"><strong><? echo htmlentities($row['label']); ?></strong><? if ($row['label'] != $row['name']) echo "<span>(".htmlentities($row['name']).")</span>"; ?><br /><em style="color:#888;"><? echo dbreport::getType(dbreport::getBasicType($row['type'])); ?></em></div>
                            <?
                        }
                        ?>
                    </div>
                    <div class="dbreport_table_info">
                        <div><strong>Lignes : </strong><span><? echo number_format($rowTableInfo['Rows']/1000, 2, '.', ' '); ?> k&nbsp;&nbsp;</span><strong>Volume : </strong><span><? echo number_format($rowTableInfo['Data_length']/(1024*1024), 2, '.', ' '); ?> Mo</span></div>
                    </div>
                </div>
                <?
            }
            ?>
        </div>
    </div>


    <?php

    $arrTableRelationDetail = array();

    // Sélection des relations de la requete courante
    $objQuery = new ploopi_query_select();
    $objQuery->add_from('ploopi_mod_dbreport_queryrelation qr');
    $objQuery->add_where('qr.id_query = %d', $_GET['dbreport_query_id']);
    $objRs = $objQuery->execute();

    ?>
    <div>
        <h1 class="dbreport_title">Relations</h1>
        <?
        $arrColumns = array();
        $arrValues = array();

        $arrColumns['left']['tablesrc'] = array(
            'label' => 'Table source',
            'width' => 250,
            'options' => array('sort' => true)
        );

        $arrColumns['left']['fieldsrc'] = array(
            'label' => 'Champ Source',
            'width' => 150,
            'options' => array('sort' => true)
        );

        $arrColumns['left']['tabledest'] = array(
            'label' => 'Table destination',
            'width' => 250,
            'options' => array('sort' => true)
        );

        $arrColumns['left']['fielddest'] = array(
            'label' => 'Champ destination',
            'width' => 150,
            'options' => array('sort' => true)
        );


        $arrColumns['auto']['comment'] = array(
            'label' => 'Commentaire',
        );

        $arrColumns['right']['active'] = array(
            'label' => 'Active',
            'width' => 70,
            'options' => array('sort' => true)
        );

        while ($row = $objRs->fetchrow())
        {
            $strComment = '';
            $booMulti = 1;

            $arrValues[] = array(
                'values' => array(
                    'tablesrc' => array('label' => $row['tablename_src'], 'sort_label' => "{$row['tablename_dest']}.{$row['fieldname_dest']}"),
                    'tabledest' => array('label' => $row['tablename_dest']),
                    'fieldsrc' => array('label' => $row['fieldname_src']),
                    'fielddest' => array('label' => $row['fieldname_dest']),
                    'active' => array('label' => $row['active'] ? 'Oui' : 'Non', 'style' => 'font-weight:bold;color:#'.($row['active'] ? '00a600' : 'a60000')),
                    'comment' => array('label' => $strComment, 'style' => $booMulti ? 'color:#a60000;' : 'color:#aaa;')
                ),
                'description' => 'Activer/Désactiver la relation',
                'link' => ploopi_urlencode("admin.php?ploopi_op=dbreport_queryrelation_modify&dbreport_query_id={$objDbrQuery->fields['id']}&dbreport_queryrelation_src={$row['tablename_src']},{$row['fieldname_src']}&dbreport_queryrelation_dest={$row['tablename_dest']},{$row['fieldname_dest']}&dbreport_queryrelation_active=".($row['active'] ? 0 : 1))
            );
        }


        $skin->display_array($arrColumns, $arrValues, 'dbreport_relation_list', array('sortable' => true, 'orderby_default' => 'tablesrc', 'limit' => 25));

        if (!$objRs->numrows()) {
            ?>
            <div style="background-color:#eee;border-bottom:1px solid #aaa;padding:2px 4px;">
                Aucune relation pour cette requête
            </div>
            <?
        }
        ?>
    </div>


    <div class="dbreport_query_block">
        <a name="dbreport_fields"></a>
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
                'label' => 'Filtre (Ou)',
                'width' => 120,
            );

        $arrColumns['right']['criteria'] =
            array(
                'label' => 'Filtre',
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
                'width' => 130,
            );

        $arrColumns['actions_right']['actions'] =
            array(
                'label' => '',
                'width' => 30
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

        // Tableau des champs visibles (pour paramétrage de transformation)
        $arrVisibleFields = array();

        while ($row = $objRs->fetchrow())
        {
            // Indexé ?
            $booIndex = isset($arrTableDetail[$row['table_name']]['indexes'][$row['field_name']]);

            $strIndex = $booIndex ? ' <em style="color:green;">indexé</em>' : ' <em style="color:orange;">non indexé</em>';

            $strCriteria = dbreport::getCriteria($row['type_criteria']) ? dbreport::getCriteria($row['type_criteria'])." {$row['criteria']}" : '';
            $strOr = dbreport::getCriteria($row['type_or']) ? dbreport::getCriteria($row['type_or'])." {$row['or']}" : '';

            // Avertissement ?
            $strStyle = '';
            if (!$booIndex && ($strCriteria != '' || $strOr != '')) $strStyle = 'color:red;';

            $arrValues[] = array(
                'values' => array(
                    'field' => array('label' => "[{$row['mt_label']}] {$row['table_name']}.{$row['field_name']} : <strong>{$row['label']}</strong>". (isset($arrLabels[$row['label']]) ? "<em style=\"color:#a60000;\">&nbsp;(Doublon d'intitulé)</em>" : '') ."<br /><em style=\"color:#888;\">".dbreport::getType(dbreport::getBasicType($row['type'])).'</em>'.$strIndex, 'style' => $strStyle),
                    'label' => array('label' => isset($arrLabels[$row['label']]) ? "<em style=\"color:#a60000;\">{$row['label']} (Doublon d'intitulé)</em>" : $row['label']),
                    'function' => array('label' => $row['function']),
                    'operation' => array('label' => dbreport::getOperation($row['operation'])),
                    'sort' => array('label' => dbreport::getSort($row['sort'])),
                    'visible' => array('label' => $row['visible'] ? 'Oui' : 'Non'),
                    'series' => array('label' => $row['series'] ? 'Oui' : 'Non'),
                    'criteria' => array('label' => $strCriteria, 'style' => $strStyle),
                    'or' => array('label' => $strOr, 'style' => $strStyle),
                    'actions' => array(
                        'label' => '
                            <a title="Supprimer le champ" href="javascript:void(0);" onclick="javascript:if (confirm(\'Êtes vous certain de vouloir supprimer cette ligne ?\')) document.location.href=\''.ploopi_urlencode("admin-light.php?ploopi_op=dbreport_queryfield_delete&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_queryfield_id={$row['id']}").'\';"><img src="./modules/dbreport/img/ico_delete.png" /></a>
                        '
                    ),
                    'position' => array('label' => $row['position'], 'sort_label' => $row['position'])
                ),
                'description' => 'Modifier le champ',
                'link' => 'javascript:void(0);',
                'onclick' => "ploopi_xmlhttprequest_topopup('600', null, 'dbreport_queryfield_modify', 'admin-light.php', '".ploopi_queryencode("ploopi_op=dbreport_queryfield_modify&dbreport_query_id={$_GET['dbreport_query_id']}&dbreport_queryfield_id={$row['id']}")."','POST')",
                'style' => ''
            );

            $arrLabels[$row['label']] = 1;


            if ($row['visible']) $arrVisibleFields[$row['id']] = $row['label'];
        }

        $skin->display_array($arrColumns, $arrValues, 'dbreport_field_list', array('sortable' => true, 'orderby_default' => 'position', 'limit' => 25));

        if (!$objRs->numrows()) {
            ?>
            <div style="background-color:#eee;border-bottom:1px solid #aaa;padding:2px 4px;">
                Aucun champ pour cette requête
            </div>
            <?
        }
        ?>
    </div>

    <div>
        <a name="dbreport_trans"></a>
        <h1 class="dbreport_title">Transformation</h1>
        <div style="border-bottom:1px solid #aaa;padding:4px;">
            <?
            if (sizeof($arrVisibleFields) >= 3) {


                $objForm = new form(
                    'dbreport_form_pivot_modify',
                    ploopi_urlencode("admin-light.php?ploopi_op=dbreport_transformation_save&dbreport_query_id={$objDbrQuery->fields['id']}"),
                    'post'
                );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_pivot', 'Tableau croisé', array('style' => 'margin:0 4px;')));

                $objPanel->addField( new form_checkbox('Activé:', 'pivot_table', $objDbrQuery->fields['transformation'] == 'pivot_table', 'dbreport_query_transformation', 'dbreport_query_transformation') );
                $objPanel->addField( new form_select('Colonnes:', array('') + $arrVisibleFields, $objDbrQuery->fields['pivot_x'], 'dbreport_query_pivot_x', 'dbreport_query_pivot_x') );
                $objPanel->addField( new form_select('Lignes:', array('') + $arrVisibleFields, $objDbrQuery->fields['pivot_y'], 'dbreport_query_pivot_y', 'dbreport_query_pivot_y') );
                $objPanel->addField( new form_select('Données:', array('') + $arrVisibleFields, $objDbrQuery->fields['pivot_val'], 'dbreport_query_pivot_val', 'dbreport_query_pivot_val') );

                $objForm->addButton( new form_button('input:reset', 'Réinitialiser') );
                $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:2px;')) );

                echo $objForm->render();
            }
            else {
                // Impossibilité de traiter le tableau croisé
                if ($objDbrQuery->fields['transformation'] != '') {
                    $objDbrQuery->fields['transformation'] = '';
                    $objDbrQuery->save();
                }
                ?>
                Vous pourriez créer un tableau croisé avec au moins 3 champs visibles.
                <?
            }
            ?>
        </div>
    </div>

    <div>
        <a name="dbreport_chart"></a>
        <h1 class="dbreport_title">Représentation graphique</h1>
        <div style="border-bottom:1px solid #aaa;padding:4px;">
            <?
            if (sizeof($arrVisibleFields) >= 2) {

                $objForm = new form(
                    'dbreport_form_chart_modify',
                    ploopi_urlencode("admin-light.php?ploopi_op=dbreport_chart_save&dbreport_query_id={$objDbrQuery->fields['id']}"),
                    'post',
                    array(
                        'class' => 'ploopi_generate_form dbreport_panels',
                    )
                );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart', 'Forme'));

                $objPanel->addField( new form_select('Type:', array('(Choisissez un type de graphique)') + dbreport_query::getChartTypes(), $objDbrQuery->fields['chart'], 'dbreport_query_chart', 'dbreport_query_chart', array('style' => 'width:180px;', 'onchange' => "$('dbreport_query_chart_limit_y_form').style.display = $('dbreport_query_chart_sort_y_form').style.display = $('dbreport_query_chart_y_form').style.display = (this.value == 'pie' || this.value == 'doughnut') ? 'none' : 'block';")) );
                $objPanel->addField( new form_field('input:text', 'Largeur (px):', $objDbrQuery->fields['chart_width'], 'dbreport_query_chart_width', 'dbreport_query_chart_width', array('style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Hauteur (px):', $objDbrQuery->fields['chart_height'], 'dbreport_query_chart_height', 'dbreport_query_chart_height', array('style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Titre:', $objDbrQuery->fields['chart_title'], 'dbreport_query_chart_title', 'dbreport_query_chart_title') );
                $objPanel->addField( new form_field('input:text', 'Sous-titre:', $objDbrQuery->fields['chart_subtitle'], 'dbreport_query_chart_subtitle', 'dbreport_query_chart_subtitle') );
                $objPanel->addField( new form_checkbox('Animations:', 1, $objDbrQuery->fields['chart_animation'], 'dbreport_query_chart_animation', 'dbreport_query_chart_animation') );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart_data', 'Données'));

                $objPanel->addField( new form_select('Jeu de données:', array('(Choisissez un champ)') + $arrVisibleFields, $objDbrQuery->fields['chart_x'], 'dbreport_query_chart_x', 'dbreport_query_chart_x') );
                $objPanel->addField( new form_select('Tri (jeu):', array('' => '(par défaut)') + dbreport_query::getChartSorts(), $objDbrQuery->fields['chart_sort_x'], 'dbreport_query_chart_sort_x', 'dbreport_query_chart_sort_x', array('style' => 'width:150px;')) );
                $objPanel->addField( new form_field('input:text', 'Limité à (jeu):', $objDbrQuery->fields['chart_limit_x'], 'dbreport_query_chart_limit_x', 'dbreport_query_chart_limit_x', array('style' => 'width:50px;', 'datatype' => 'int')) );
                $objPanel->addField( new form_select('Valeurs:', array('(Choisissez un champ)') + $arrVisibleFields, $objDbrQuery->fields['chart_val'], 'dbreport_query_chart_val', 'dbreport_query_chart_val') );
                $objPanel->addField( new form_select('Séries de données (opt):', array('(Choisissez un champ)') + $arrVisibleFields, $objDbrQuery->fields['chart_y'], 'dbreport_query_chart_y', 'dbreport_query_chart_y', array('style_form' => in_array($objDbrQuery->fields['chart'], array('pie', 'doughnut')) ? 'display:none;' : 'display:block;' )) );
                $objPanel->addField( new form_select('Tri (séries):', array('' => '(par défaut)') + dbreport_query::getChartSorts(), $objDbrQuery->fields['chart_sort_y'], 'dbreport_query_chart_sort_y', 'dbreport_query_chart_sort_y', array('style' => 'width:150px;', 'style_form' => in_array($objDbrQuery->fields['chart'], array('pie', 'doughnut')) ? 'display:none;' : 'display:block;')) );
                $objPanel->addField( new form_field('input:text', 'Limité à (séries):', $objDbrQuery->fields['chart_limit_y'], 'dbreport_query_chart_limit_y', 'dbreport_query_chart_limit_y', array('style' => 'width:50px;', 'datatype' => 'int', 'style_form' => in_array($objDbrQuery->fields['chart'], array('pie', 'doughnut')) ? 'display:none;' : 'display:block;')) );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart_style', 'Style'));
                $objPanel->addField( new form_select('Thème de couleurs:', array('' => '(monochrome)') + array_combine(array_keys(dbreport_query::getChartColorSets()), array_keys(dbreport_query::getChartColorSets())), $objDbrQuery->fields['chart_colorset'], 'dbreport_query_chart_colorset', 'dbreport_query_chart_colorset', array('style' => 'width:150px;', 'onchange' => "$('dbreport_query_chart_color_form').style.display = (this.value == '') ? 'block' : 'none';")) );
                $objPanel->addField( new form_field('input:text', 'Couleur (monochrome):', $objDbrQuery->fields['chart_color'], 'dbreport_query_chart_color', 'dbreport_query_chart_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;', 'style_form' => $objDbrQuery->fields['chart_colorset'] == '' ? 'display:block;' : 'display:none;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur fond:', $objDbrQuery->fields['chart_background'], 'dbreport_query_chart_background', 'dbreport_query_chart_background', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Epaisseur bordure:', $objDbrQuery->fields['chart_border_width'], 'dbreport_query_chart_border_width', 'dbreport_query_chart_border_width', array('datatype' => 'int', 'style' => 'width:50px;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur bordure:', $objDbrQuery->fields['chart_border_color'], 'dbreport_query_chart_border_color', 'dbreport_query_chart_border_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );
                $objPanel->addField( new form_select('Famille de police:', dbreport_query::getChartFonts(), $objDbrQuery->fields['chart_font'], 'dbreport_query_chart_font', 'dbreport_query_chart_font', array('style' => 'width:150px;')) );
                $objPanel->addField( new form_field('input:text', 'Taille titre:', $objDbrQuery->fields['chart_title_font_size'], 'dbreport_query_chart_title_font_size', 'dbreport_query_chart_title_font_size', array('datatype' => 'int', 'style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur titre:', $objDbrQuery->fields['chart_title_font_color'], 'dbreport_query_chart_title_font_color', 'dbreport_query_chart_title_font_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Epaisseur tracés:', $objDbrQuery->fields['chart_line_thickness'], 'dbreport_query_chart_line_thickness', 'dbreport_query_chart_line_thickness', array('datatype' => 'int', 'style' => 'width:50px;')) );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart_axis', 'Axes'));
                $objPanel->addField( new form_field('input:text', 'Epaisseur x:', $objDbrQuery->fields['chart_axis_x_thickness'], 'dbreport_query_chart_axis_x_thickness', 'dbreport_query_chart_axis_x_thickness', array('datatype' => 'int', 'style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Epaisseur y:', $objDbrQuery->fields['chart_axis_y_thickness'], 'dbreport_query_chart_axis_y_thickness', 'dbreport_query_chart_axis_y_thickness', array('datatype' => 'int', 'style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur:', $objDbrQuery->fields['chart_axis_color'], 'dbreport_query_chart_axis_color', 'dbreport_query_chart_axis_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Taille texte:', $objDbrQuery->fields['chart_axis_font_size'], 'dbreport_query_chart_axis_font_size', 'dbreport_query_chart_axis_font_size', array('datatype' => 'int', 'style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur texte:', $objDbrQuery->fields['chart_axis_font_color'], 'dbreport_query_chart_axis_font_color', 'dbreport_query_chart_axis_font_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );


                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart_grid', 'Grille'));
                $objPanel->addField( new form_field('input:text', 'Epaisseur x:', $objDbrQuery->fields['chart_grid_x_thickness'], 'dbreport_query_chart_grid_x_thickness', 'dbreport_query_chart_grid_x_thickness', array('datatype' => 'int', 'style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Epaisseur y:', $objDbrQuery->fields['chart_grid_y_thickness'], 'dbreport_query_chart_grid_y_thickness', 'dbreport_query_chart_grid_y_thickness', array('datatype' => 'int', 'style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur:', $objDbrQuery->fields['chart_grid_color'], 'dbreport_query_chart_grid_color', 'dbreport_query_chart_grid_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart_interlaced', 'Intervalles'));
                $objPanel->addField( new form_checkbox('Afficher:', 1, $objDbrQuery->fields['chart_interlaced_display'], 'dbreport_query_chart_interlaced_display', 'dbreport_query_chart_interlaced_display') );
                $objPanel->addField( new form_field('input:text', 'Couleur colonnes:', $objDbrQuery->fields['chart_interlaced_x_color'], 'dbreport_query_chart_interlaced_x_color', 'dbreport_query_chart_interlaced_x_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur lignes:', $objDbrQuery->fields['chart_interlaced_y_color'], 'dbreport_query_chart_interlaced_y_color', 'dbreport_query_chart_interlaced_y_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart_legend', 'Légende'));
                $objPanel->addField( new form_checkbox('Afficher:', 1, $objDbrQuery->fields['chart_legend_display'], 'dbreport_query_chart_legend_display', 'dbreport_query_chart_legend_display') );
                $objPanel->addField( new form_field('input:text', 'Taille texte:', $objDbrQuery->fields['chart_legend_font_size'], 'dbreport_query_chart_legend_font_size', 'dbreport_query_chart_legend_font_size', array('datatype' => 'int', 'style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur texte:', $objDbrQuery->fields['chart_legend_font_color'], 'dbreport_query_chart_legend_font_color', 'dbreport_query_chart_legend_font_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );
                $objPanel->addField( new form_select('Position horizontale:', dbreport_query::getChartAligns(), $objDbrQuery->fields['chart_legend_align'], 'dbreport_query_chart_legend_align', 'dbreport_query_chart_legend_align', array('style' => 'width:150px;')) );
                $objPanel->addField( new form_select('Position verticale:', dbreport_query::getChartValigns(), $objDbrQuery->fields['chart_legend_valign'], 'dbreport_query_chart_legend_valign', 'dbreport_query_chart_legend_valign', array('style' => 'width:150px;')) );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart_format', 'Formatage des données'));
                $objPanel->addField( new form_field('input:text', 'Préfixe (x):', $objDbrQuery->fields['chart_value_x_prefix'], 'dbreport_query_chart_value_x_prefix', 'dbreport_query_chart_value_x_prefix', array('style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Suffixe (x):', $objDbrQuery->fields['chart_value_x_suffix'], 'dbreport_query_chart_value_x_suffix', 'dbreport_query_chart_value_x_suffix', array('style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Préfixe (y):', $objDbrQuery->fields['chart_value_y_prefix'], 'dbreport_query_chart_value_y_prefix', 'dbreport_query_chart_value_y_prefix', array('style' => 'width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Suffixe (y):', $objDbrQuery->fields['chart_value_y_suffix'], 'dbreport_query_chart_value_y_suffix', 'dbreport_query_chart_value_y_suffix', array('style' => 'width:100px;')) );
                $objPanel->addField( new form_field('textarea', 'Contenu popup:<br />Ex: <em>{x}, {y}, {total}, {percentage:.2f}, {point.x}, {point.y}, {point.name}, {point.color}, {series.color}, {series.name}</em>', $objDbrQuery->fields['chart_tooltip_format'], 'fck_dbreport_query_chart_tooltip_format', 'fck_dbreport_query_chart_tooltip_format', array('style' => 'height:80px;')) );

                $objForm->addPanel($objPanel = new form_panel('dbreport_panel_chart_indexes', 'Indices'));
                $objPanel->addField( new form_checkbox('Afficher:', 1, $objDbrQuery->fields['chart_indexes_display'], 'dbreport_query_chart_indexes_display', 'dbreport_query_chart_indexes_display') );
                $objPanel->addField( new form_field('textarea', 'Contenu:<br />Ex: <em>{x}, {y}, {total}, {percentage:.2f}, {point.x}, {point.y}, {point.name}, {point.color}, {series.color}, {series.name}</em>', $objDbrQuery->fields['chart_indexes_format'], 'fck_dbreport_query_chart_indexes_format', 'fck_dbreport_query_chart_indexes_format', array('style' => 'height:80px;')) );
                $objPanel->addField( new form_field('input:text', 'Taille texte (px):', $objDbrQuery->fields['chart_indexes_font_size'], 'dbreport_query_chart_indexes_font_size', 'dbreport_query_chart_indexes_font_size', array('datatype' => 'int', 'style' => 'width:50px;')) );
                $objPanel->addField( new form_field('input:text', 'Couleur texte:', $objDbrQuery->fields['chart_indexes_font_color'], 'dbreport_query_chart_indexes_font_color', 'dbreport_query_chart_indexes_font_color', array('class' => 'color {hash:true,required:true}', 'style' => 'cursor:pointer;width:100px;')) );
                $objPanel->addField( new form_field('input:text', 'Rotation (deg):', $objDbrQuery->fields['chart_indexes_rotation'], 'dbreport_query_chart_indexes_rotation', 'dbreport_query_chart_indexes_rotation', array('datatype' => 'int', 'style' => 'width:50px;')) );
                $objPanel->addField( new form_field('input:text', 'Décalage (x):', $objDbrQuery->fields['chart_indexes_x'], 'dbreport_query_chart_indexes_x', 'dbreport_query_chart_indexes_x', array('datatype' => 'int', 'style' => 'width:50px;')) );
                $objPanel->addField( new form_field('input:text', 'Décalage (y):', $objDbrQuery->fields['chart_indexes_y'], 'dbreport_query_chart_indexes_y', 'dbreport_query_chart_indexes_y', array('datatype' => 'int', 'style' => 'width:50px;')) );

                $objForm->addButton( new form_button('input:button', 'Voir', '', '', array('onclick' => "ploopi_openwin('".ploopi_urlencode("admin-light.php?ploopi_op=dbreport_chart_generate&dbreport_query_id={$objDbrQuery->fields['id']}")."', ".($objDbrQuery->fields['chart_width']+20).", ".($objDbrQuery->fields['chart_height']+30).");")) );
                $objForm->addButton( new form_button('input:submit', 'Enregistrer', null, null, array('style' => 'margin-left:2px;')) );

                echo $objForm->render();
            }
            else {
                // Impossibilité de traiter les graphiques
                if ($objDbrQuery->fields['chart'] != '') {
                    $objDbrQuery->fields['chart'] = '';
                    $objDbrQuery->save();
                }
                ?>
                Vous pourriez créer un graphique avec au moins 2 champs visibles.
                <?
            }
            ?>
        </div>

    </div>


    <?php
    // Génération du code SQL;
    $objDbrQuery->generate();

    $strSql = $objDbrQuery->getquery();
    if ($strSql != '') {
        ?>
        <div style="overflow:auto;">
            <h1 class="dbreport_title">Aperçu SQL</h1>
            <div style="background-color:#fff;border:1px solid #ccc;border-radius:5px;padding:0 10px;margin:5px;">
                <?
                include_once './modules/dbreport/lib/SqlFormatter.php';

                echo utf8_decode(SqlFormatter::format(utf8_encode($objDbrQuery->getquery())));
                ?>
            </div>
        </div>
        <?
    }
}
else
{
    echo $skin->open_simplebloc('Requête inconnue');
}

echo $skin->close_simplebloc();
?>
