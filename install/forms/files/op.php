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
 * Opérations
 *
 * @package forms
 * @subpackage op
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Opérations pour les utilisateurs connectés uniquement
 */

if ($_SESSION['ploopi']['connected'])
{
    /**
     * On vérifie qu'on est bien dans le module FORMS.
     */

    if (ploopi_ismoduleallowed('forms'))
    {
        switch($ploopi_op)
        {
            case 'forms_xml_switchdisplay':
                if (!empty($_GET['display']))
                {
                    $switch = (!isset($_GET['switch'])) ? 'empty' : $_GET['switch'];
                    $_SESSION['forms'][$_SESSION['ploopi']['moduleid']][$switch] = $_GET['display'];
                }
                ploopi_die();
            break;
            
            case 'forms_export':
                ploopi_init_module('forms');
                if (ploopi_isactionallowed(_FORMS_ACTION_EXPORT) && !empty($_GET['forms_id']) && is_numeric($_GET['forms_id']))
                {
                    $id_module = $_SESSION['ploopi']['moduleid'];
                    include './modules/forms/op_export.php';
                }
            break;

            case 'forms_delete_data':
                include_once './modules/form/include/global.php';

                if (ploopi_isactionallowed(_FORMS_ACTION_BACKUP))
                {
                    ?>
                    <div style="background:#f0f0f0;border-bottom:1px solid #c0c0c0;font-weight:bold;padding:2px;">Suppression des données</div>
                    <div style="padding:2px;">
                    <?php
                    if (!empty($_GET['form_id']) && !empty($_GET['form_delete_date']))
                    {
                        $form_delete_date = ploopi_local2timestamp($_GET['form_delete_date']);

                        $form_delete_date = ploopi_timestamp_add($form_delete_date, 0, 0, 0, 0, 1, 0);

                        $sql = "SELECT COUNT(*) as c FROM ploopi_mod_forms_reply WHERE id_form = '".$db->addslashes($_GET['form_id'])."' AND date_validation < {$form_delete_date}";
                        $db->query($sql);
                        $row = $db->fetchrow();

                        echo "{$row['c']} enregistement(s) ont été supprimés";

                        $sql =  "
                                DELETE  r, rf
                                FROM    ploopi_mod_forms_reply r,
                                        ploopi_mod_forms_reply_field rf
                                WHERE   r.id_form = '".$db->addslashes($_GET['form_id'])."'
                                AND     r.date_validation < {$form_delete_date}
                                AND     rf.id_reply = r.id
                                ";
                        $db->query($sql);
                    }
                    ?>
                    </div>
                    <div style="background:#f0f0f0;border-top:1px solid #c0c0c0;text-align:right;padding:2px;"><a href="javascript:void(0);" onmouseup="javascript:ploopi_hidepopup('forms_deletedata');document.location.reload();">Fermer</a></div>
                    <?php
                }
                ploopi_die();
            break;
            
            case 'forms_graphic_display':
                ob_start();
                ?>
                <div style="background-color:#fff;">
                <?php
                if (isset($_POST['forms_graphic_id']))
                {
                    ?>
                    <img src="<?php echo ploopi_urlencode("admin-light.php?ploopi_op=forms_graphic_generate&forms_graphic_id={$_POST['forms_graphic_id']}&forms_rand=".microtime()); ?>" />
                    <?
                }
                else
                {
                    echo "erreur";
                }
                ?>
                </div>
                <?php
                $strContent = ob_get_contents();
                ob_end_clean();
                ploopi_die($skin->create_popup('Graphique', $strContent, 'forms_popup_graphic'));
            break;
            
            case 'forms_graphic_generate':
                include_once './modules/forms/class_forms_graphic.php';
                include_once './modules/forms/class_field.php';
                
                $objGraphic = new forms_graphic();
                if (isset($_GET['forms_graphic_id']) && is_numeric($_GET['forms_graphic_id']) && $objGraphic->open($_GET['forms_graphic_id']))
                {
                    include_once './modules/forms/jpgraph/jpgraph.php';
                    
                    $intGraphWidth = 700;
                    $intGraphHeight = 450;
                    
                    switch($objGraphic->fields['type'])
                    {
                        // Génération des secteurs + 3d
                        case 'pie':
                        case 'pie3d':
                            include_once './modules/forms/jpgraph/jpgraph_pie.php';
                            include_once './modules/forms/jpgraph/jpgraph_pie3d.php';
                            
                            $arrData = array();
                            
                            foreach($_SESSION['forms']['data'] as $arrLine)
                            {
                                if (empty($arrData[$arrLine[$objGraphic->fields['pie_field']]])) $arrData[$arrLine[$objGraphic->fields['pie_field']]] = 0;
                                $arrData[$arrLine[$objGraphic->fields['pie_field']]]++;
                            }
                            
                            //ploopi_die($arrData);
                            
                            // Création du graph
                            // On spécifie la largeur et la hauteur du graph
                            $objGraph = new PieGraph($intGraphWidth, $intGraphHeight);
                             
                            $objGraph->title->Set($objGraphic->fields['label']);
                            $objGraph->title->SetFont(FF_VERDANA, FS_NORMAL, 15);
                            $objGraph->SetFrame(false); // optional, if you don't want a frame border
                            $objGraph->legend->SetFont(FF_VERDANA, FS_NORMAL, 8);
                            
                            $objGraph->SetAntiAliasing();
                            
                            if ($objGraphic->fields['type'] == 'pie3d') $objPie = new PiePlot3D(array_values($arrData));
                            else $objPie = new PiePlot(array_values($arrData));
                            
                            // Position du graphique (0.5=centré)
                            $objPie->SetCenter(0.5, 0.62);       
                                                    
                            // Définition du format d'affichage
                            if ($objGraphic->fields['percent'])
                            {
                                $objPie->value->SetFormat('%d %%');
                            }
                            else
                            {
                                $objPie->value->SetFormat('%s');
                                $objPie->SetValueType(PIE_VALUE_ABS);
                            }
                            $objPie->value->SetFont(FF_VERDANA, FS_NORMAL, 10);
                            
                            $objPie->SetLegends(array_keys($arrData));
                            
                            $objPie->SetSliceColors(forms_gradient($objGraphic->fields['pie_color1'], $objGraphic->fields['pie_color2'], sizeof($arrData)));
                            
                                                        
                            $objGraph->Add($objPie);
                            
                        break;
                        
                        // Génération des histogrammes et des courbes + cumuls
                        case 'bar':
                        case 'barc':
                        case 'line':
                        case 'linec':
                            include_once './modules/forms/jpgraph/jpgraph_line.php';
                            include_once './modules/forms/jpgraph/jpgraph_bar.php';
                            
                            $arrLabels = array(); // tableau des libellés du graphique
                            $arrDataModel = array(); // tableau type d'un dataset (une courbe)
                            $arrData = array(); // Tableau des données
                            $arrCount = array(); // Tableau des compteurs (pour moyenne notamment)
                            $arrTotal = array(); // tableau contenant le total pour chaque indice (permet de calculer les valeurs en pourcentage) 
                            
                            $intTsNow = mktime();
                            
                            switch($objGraphic->fields['line_aggregation'])
                            {
                                case 'hour':
                                    $intTsMin = ploopi_unixtimestamp2timestamp(mktime(date('G') - 23, 0, 0));
                                    for ($intI = 0; $intI < 24; $intI++) 
                                    {
                                        $arrDataModel[$intI] = 0;
                                        $intTsLabel = mktime(date('G') - 23 + $intI, 0, 0);
                                        $arrLabels[$intI] = date('H', $intTsLabel).'h';
                                        $arrTotal[$intI] = 0; // Valeur totale pour chaque indice
                                    }
                                    $strTitleX = 'Heures';
                                break;
                                
                                case 'day':
                                    $intTsMin = ploopi_unixtimestamp2timestamp(mktime(0, 0, 0, date('n'), date('j') - 13));
                                    for ($intI = 0; $intI < 14; $intI++) 
                                    {
                                        $arrDataModel[$intI] = 0;
                                        $intTsLabel = mktime(0, 0, 0, date('n'), date('j') - 13 + $intI);
                                        $arrLabels[$intI] = date('d/m', $intTsLabel);
                                        $arrTotal[$intI] = 0; // Valeur totale pour chaque indice
                                    }
                                    $strTitleX = 'Jour / Mois';
                                break;
                                
                                case 'week':
                                    for ($intI = 0; $intI < 12; $intI++) 
                                    {
                                        $arrDataModel[$intI] = 0;
                                        $arrTotal[$intI] = 0; // Valeur totale pour chaque indice
                                    }
                                    $strTitleX = 'Semaines';
                                break;
                                
                                case 'month':
                                    $intTsMin = ploopi_unixtimestamp2timestamp(mktime(0, 0, 0, date('n') - 11, 1));
                                    for ($intI = 0; $intI < 12; $intI++) 
                                    {
                                        $arrDataModel[$intI] = 0;
                                        $intTsLabel = mktime(0, 0, 0, date('n') - 11 + $intI, 1);
                                        $arrLabels[$intI] = date('m/Y', $intTsLabel);
                                        $arrTotal[$intI] = 0; // Valeur totale pour chaque indice
                                    }
                                    $strTitleX = 'Mois / Année';
                                break;
                            }
                            
                            // Initialisation des dataset avec 0
                            for ($intI = 1; $intI <= 5; $intI++) // Courbes
                            {
                                if (!empty($objGraphic->fields["line{$intI}_field"])) // Courbe valide
                                {
                                    $arrData[$intI] = $arrDataModel; // Un tableau de données par courbe
                                    $arrCount[$intI] = $arrDataModel; // Un tableau de données par courbe
                                }
                                
                            }
                            
                            foreach($_SESSION['forms']['data'] as $arrLine)
                            {
                                // 1. Filtrage sur la date par rapport à la période choisie
                                if ($arrLine['datevalidation'] > $intTsMin)
                                {
                                    // 2. Détermination de l'appartenance à la courbe en fonction du filtre
                                    foreach(array_keys($arrData) as $intI)
                                    {
                                        $booFilterOk = false;
                                        if ($objGraphic->fields["line{$intI}_filter_op"] != '' && $objGraphic->fields["line{$intI}_filter_value"] != '')
                                        {
                                            $strVal1 = $arrLine[$objGraphic->fields["line{$intI}_field"]];
                                            $strVal2 = $objGraphic->fields["line{$intI}_filter_value"];
                                            
                                            switch($objGraphic->fields["line{$intI}_filter_op"])
                                            {
                                                case '=':
                                                    $booFilterOk = ($strVal1 == $strVal2);
                                                break;
                        
                                                case '>':
                                                    $booFilterOk = ($strVal1 > $strVal2);
                                                break;
                        
                                                case '<':
                                                    $booFilterOk = ($strVal1 < $strVal2);
                                                break;
                        
                                                case '>=':
                                                    $booFilterOk = ($strVal1 >= $strVal2);
                                                break;
                        
                                                case '<=':
                                                    $booFilterOk = ($strVal1 <= $strVal2);
                                                break;
                        
                                                case 'like':
                                                    $booFilterOk = strstr($strVal1, $strVal2);
                                                break;
                        
                                                case 'begin':
                                                    $booFilterOk = (strpos($strVal1, $strVal2) === 0);
                                                break;
                                                
                                            }
                                        }
                                        else $booFilterOk = true;
                                        
                                        if ($booFilterOk) // Filtre ok, la donnée appartient à cette courbe
                                        {
                                            // Détermination de l'indice de la donnée sur la courbe
                                            $intIndice = 0;
                                            
                                            switch($objGraphic->fields['line_aggregation'])
                                            {
                                                case 'hour':
                                                    $intIndice = round((ploopi_timestamp2unixtimestamp($arrLine['datevalidation']) - ploopi_timestamp2unixtimestamp($intTsMin)) / 3600);
                                                break;
                                                
                                                case 'day':
                                                    $intIndice = round((ploopi_timestamp2unixtimestamp($arrLine['datevalidation']) - ploopi_timestamp2unixtimestamp($intTsMin)) / (3600*24)) - 1;
                                                break;
                                                
                                                case 'week':
                                                    $intIndice = 0;
                                                break;
                                                
                                                case 'month':
                                                    $intIndice = 11 - (12 + date('n') - date('n', ploopi_timestamp2unixtimestamp($arrLine['datevalidation']))) % 12;
                                                break;
                                            }
                                            
                                            
                                            switch($objGraphic->fields["line{$intI}_operation"])
                                            {
                                                case 'count':
                                                    $arrData[$intI][$intIndice]++; 
                                                break;
                                                
                                                case 'sum':
                                                case 'avg':
                                                    $arrData[$intI][$intIndice] += floatval($arrLine[$objGraphic->fields["line{$intI}_field"]]);
                                                    $arrCount[$intI][$intIndice]++; 
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            
        
                            // Post-traitement spécial pour calculer la moyenne
                            foreach($arrData as $intI => $arrDataDetail)
                            {
                                if ($objGraphic->fields["line{$intI}_operation"] == 'avg')
                                {
                                    foreach($arrDataDetail as $intIndice => $mixVal)
                                    {
                                        if (!empty($arrCount[$intI][$intIndice])) $arrData[$intI][$intIndice] = round($arrData[$intI][$intIndice] / $arrCount[$intI][$intIndice], 2);
                                    }
                                }
                            }
                            
                            // Post-traitement spécial pour calculer les valeurs en pourcentage
                            if ($objGraphic->fields["percent"])
                            {
                                foreach($arrData as $intI => $arrDataDetail)
                                {
                                    foreach($arrDataDetail as $intIndice => $mixVal)
                                    {
                                        $arrTotal[$intIndice] += $mixVal;
                                    }
                                }
                            }
                                                        
                            
                            // Post-traitement spécial pour le calcul des cumuls
                            if ($objGraphic->fields['type'] == 'linec' || $objGraphic->fields['type'] == 'barc')
                            {
                                foreach(array_keys($arrDataModel) as $intIndice)
                                {
                                    $floTotal = 0;
                                    foreach($arrData as $intI => $arrDataDetail)
                                    {
                                        $floTotal += $arrData[$intI][$intIndice];
                                        $arrData[$intI][$intIndice] = $floTotal;
                                    }
                                }
                            }
                            
                            
                            // Post-traitement spécial pour calculer les valeurs en pourcentage
                            if ($objGraphic->fields["percent"])
                            {
                                foreach($arrData as $intI => $arrDataDetail)
                                {
                                    foreach($arrDataDetail as $intIndice => $mixVal)
                                    {
                                        $arrData[$intI][$intIndice] = empty($arrTotal[$intIndice]) ? 0 : round(($arrData[$intI][$intIndice] * 100) / $arrTotal[$intIndice], 2);
                                    }
                                }
                            }

                            $objGraph = new Graph($intGraphWidth, $intGraphHeight);
                            
                            // /!\ antialiasing non dispo dans la version de GD2 incluse dans debian etch
                            // $objGraph->img->SetAntiAliasing(true);
                            
                            $objGraph->SetScale("textlin");
                            $objGraph->title->Set($objGraphic->fields['label']);
                            $objGraph->title->SetFont(FF_VERDANA, FS_NORMAL, 15);
                            $objGraph->legend->SetFont(FF_VERDANA, FS_NORMAL, 8);
                            
                            $objGraph->xaxis->title->Set($strTitleX);
                            $objGraph->xaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 10);
                            $objGraph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 8);
                            $objGraph->xaxis->SetTickLabels($arrLabels);                
        
                            if ($objGraphic->fields['percent']) $objGraph->yaxis->title->Set('%');
                            $objGraph->yaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 10);
                            $objGraph->yaxis->SetFont(FF_VERDANA, FS_NORMAL, 8);
                            
                            $objGraph->SetFrame(false); // optional, if you don't want a frame border
                            $objGraph->SetColor('white'); // pick any color not in the graph itself
                            $objGraph->img->SetTransparent('white'); // must be same color as above  
                            $objGraph->img->SetMargin(40,0,150,0);
                            
                            $arrObjPlots = array();
                            
                            $intC = 0;
                            foreach($arrData as $intI => $arrPlots)
                            {
                                $strColor = $objGraphic->fields["line{$intI}_color"];
                                if (empty($strColor)) $strColor = 'black';
                                
                                switch($objGraphic->fields['type'])
                                {
                                    case 'line':
                                    case 'linec':
                                        // Création d'une série de points avec une courbe
                                        $arrObjPlots[] = $objPlots = new LinePlot($arrPlots);
                                
                                        // Chaque point de la courbe ****
                                        // Type de point
                                        $objPlots->mark->SetType(MARK_FILLEDCIRCLE);
                                        // Couleur de remplissage
                                        $objPlots->mark->SetFillColor($strColor);
                                        // Taille
                                        $objPlots->mark->SetWidth(5);
                                    break;
                                        
                                    case 'bar':
                                    case 'barc':
                                        // Création d'une série de barres
                                        $arrObjPlots[] = $objPlots = new BarPlot($arrPlots);
                                        if ($objGraphic->fields['type'] == 'bar' || $intC == sizeof($arrData)-1)
                                        $objPlots->SetShadow('gray');
                                        
                                        $intC++;
                                    break;
                                }
                                
                                // Valeurs: Apparence de la police
                                $objPlots->value->SetFont(FF_VERDANA, FS_NORMAL, 10);
                                $objPlots->value->SetFormat('%d');
                                $objPlots->value->SetColor($strColor);

                                
                                // Couleur de la courbe
                                $objPlots->SetColor($strColor);
                                if ($objGraphic->fields['filled'])
                                {
                                    // Couleur de remplissage de la courbe
                                    $objPlots->SetFillColor($strColor);
                                }
                                
                                $objPlots->SetCenter();            
        
                                $objField = new field();
                                if ($objField->open($objGraphic->fields["line{$intI}_field"]))
                                {
                                    $strLegend = $objField->fields['name'];
                                    if ($objGraphic->fields["line{$intI}_filter_value"] != '') $strLegend .= ' - '.$objGraphic->fields["line{$intI}_filter_value"]; 
                                    
                                    if ($objGraphic->fields['percent']) $strLegend .= ' (%)';
                                    elseif (isset($forms_graphic_operation[$objGraphic->fields["line{$intI}_operation"]])) $strLegend .= ' ('.$forms_graphic_operation[$objGraphic->fields["line{$intI}_operation"]].')';
                                    
                                    $objPlots->SetLegend($strLegend);
                                }
                            }
                            
                            if (in_array($objGraphic->fields['type'], array('linec', 'barc')))
                            {
                                // Inversion de l'ordre d'affchage des courbes (notamment pour gérer correctement l'affichage des courbes cumulées)
                                $arrObjPlots = array_reverse($arrObjPlots);
                            }
                            
                            if (in_array($objGraphic->fields['type'], array('line', 'linec', 'barc')))
                            {
                                // Attachement des courbes au conteneur
                                foreach($arrObjPlots as $objPlots) $objGraph->Add($objPlots);
                            }
                            else
                            {
                                $objGroupBarPlot = new GroupBarPlot($arrObjPlots);
                                $objGraph->Add($objGroupBarPlot);
                            }
                            
                            
                            
                        break;
                    }

                    // Vidage du buffer de Ploopi
                    ploopi_ob_clean();
                    
                    // Génération du graphique
                    $objGraph->Stroke();
                    
                }
                ploopi_die();
            break;
            
        }
    }

    /**
     * Autres opérations
     */

    switch($ploopi_op)
    {
        case 'forms_download_file':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                if (!empty($_GET['reply_id']) && !empty($_GET['field_id']) && is_numeric($_GET['reply_id']) && is_numeric($_GET['field_id']))
                {
                    include_once './modules/forms/class_reply_field.php';
                    $reply_field = new reply_field();
                    if ($reply_field->open($_GET['reply_id'], $_GET['field_id']))
                    {
                        $path = _PLOOPI_PATHDATA._PLOOPI_SEP.'forms-'.$id_module._PLOOPI_SEP.$reply_field->fields['id_form']._PLOOPI_SEP.$_GET['reply_id']._PLOOPI_SEP;
                        ploopi_downloadfile("{$path}{$reply_field->fields['value']}", $reply_field->fields['value']);
                    }
                }
            }
            ploopi_die();
        break;

        case 'forms_display':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/class_form.php';

                $forms_fuid = $_GET['forms_fuid'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                include_once './modules/forms/op_preparedata.php';
                include_once './modules/forms/op_viewlist.php';
            }
            ploopi_die();
        break;

        case 'forms_export':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/class_form.php';

                $forms_fuid = $_GET['forms_fuid'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                include_once './modules/forms/op_preparedata.php';
                include_once './modules/forms/public_forms_export.php';
            }
            ploopi_die();
        break;

        case 'forms_openreply':
            if (!empty($_GET['forms_fuid']) && isset($_SESSION['forms'][$_GET['forms_fuid']]))
            {
                ob_start();
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/class_form.php';

                $reply_id = $_GET['forms_reply_id'];
                $id_form = $_SESSION['forms'][$_GET['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_GET['forms_fuid']]['id_module'];

                $forms = new form();
                $forms->open($id_form);

                include_once './modules/forms/op_display.php';

                $content = ob_get_contents();
                ob_end_clean();

                echo $skin->create_popup($forms->fields['label'], $content, 'popup_forms_openreply');
            }
            ploopi_die();
        break;

        case 'forms_save':
            if (!empty($_POST['forms_fuid']) && isset($_SESSION['forms'][$_POST['forms_fuid']]))
            {
                ob_start();
                ploopi_init_module('forms', false, false, false);
                include_once './modules/forms/class_form.php';
                include_once './modules/forms/class_reply.php';
                include_once './modules/forms/class_reply_field.php';

                $reply_id = $_POST['forms_reply_id'];
                $id_form = $_SESSION['forms'][$_POST['forms_fuid']]['id_form'];
                $id_module = $_SESSION['forms'][$_POST['forms_fuid']]['id_module'];

                include_once './modules/forms/op_save.php';
                ?>
                <script type="text/javascript">
                    window.parent.document.location.reload();
                </script>
                <?php
            }
            ploopi_die();
        break;
    }
}
?>
