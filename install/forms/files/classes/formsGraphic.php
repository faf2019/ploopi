<?php
/*
    Copyright (c) 2007-2009 Ovensia
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
 * Gestion des Graphiques
 *
 * @package forms
 * @subpackage forms_graphic
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';


/**
 * Inclusion des dépendances
 */
include_once './modules/forms/classes/formsForm.php';
include_once './modules/forms/classes/formsField.php';

/**
 * Classe d'accès à la table ploopi_mod_forms_graphic
 *
 * @package forms
 * @subpackage forms_graphic
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class formsGraphic extends data_object
{

    /**
     *
     * Enter description here ...
     * @param unknown_type $intTs2
     * @param unknown_type $intTs2
     * @param unknown_type $strType 'h' (hours), 'd' (days), 'w' (weeks), 'm' (months)
     */
    private static function __diffDate($intTs1, $intTs2, $strType = 'h')
    {
        switch($strType)
        {
            case 'h' : return floor(($intTs2 - $intTs1)/3600); break;
            case 'd' : return floor(($intTs2 - $intTs1)/86400); break;
            case 'm' : return 1 + (date('Y', $intTs2) - date('Y', $intTs1))*12 + (date('n', $intTs2) - date('n', $intTs1)); break;
            case 'w' : return 0; break;
        }
    }


    /**
     * Constructeur de la classe
     *
     * @return formsGraphic
     */

    public function __construct() { parent::__construct('ploopi_mod_forms_graphic'); }


    public function render($intGraphWidth = null, $intGraphHeight = null)
    {
        if (empty($intGraphHeight)) $intGraphHeight = 450;

        $objForm = new formsForm();

        if (!empty($this->fields['id_form']) && $objForm->open($this->fields['id_form']))
        {
            include_once './modules/forms/jpgraph/jpgraph.php';

    		// Lecture des données
    		list($arrFormData) = $objForm->prepareData(true, true);

    		// Lecture des champs
    		$arrFormFields = $objForm->getFields();

            switch($this->fields['type'])
            {
                // Génération des secteurs + 3d
                case 'pie':
                case 'pie3d':
                    include_once './modules/forms/jpgraph/jpgraph_pie.php';
                    include_once './modules/forms/jpgraph/jpgraph_pie3d.php';

                    if (empty($intGraphWidth)) $intGraphWidth = 700;

                    $arrData = array();

                    foreach($arrFormData as $arrLine)
                    {
                        if (isset($arrFormFields[$this->fields["pie_field"]]) && isset($arrLine[$arrFormFields[$this->fields["pie_field"]]->fields['fieldname']]))
                        {
                            $strFieldName = $arrLine[$arrFormFields[$this->fields["pie_field"]]->fields['fieldname']];

                            if (empty($arrData[$strFieldName])) $arrData[$strFieldName] = 0;
                            $arrData[$strFieldName]++;
                        }
                    }

                    //ploopi_die($arrData);

                    // Création du graph
                    // On spécifie la largeur et la hauteur du graph
                    $objGraph = new PieGraph($intGraphWidth, $intGraphHeight);

                    $objGraph->title->Set($this->fields['label']);
                    $objGraph->title->SetFont(FF_VERDANA, FS_NORMAL, 15);
                    $objGraph->SetFrame(false); // optional, if you don't want a frame border
                    $objGraph->legend->SetFont(FF_VERDANA, FS_NORMAL, 8);

                    $objGraph->SetAntiAliasing();

                    if ($this->fields['type'] == 'pie3d') $objPie = new PiePlot3D(array_values($arrData));
                    else $objPie = new PiePlot(array_values($arrData));

                    // Position du graphique (0.5=centré)
                    $objPie->SetCenter(0.5, 0.62);

                    // Définition du format d'affichage
                    if ($this->fields['percent'])
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

                    $objPie->SetSliceColors(forms_gradient($this->fields['pie_color1'], $this->fields['pie_color2'], sizeof($arrData)));


                    $objGraph->Add($objPie);

                break;

                // Génération des histogrammes et des courbes + cumuls
                case 'bar':
                case 'barc':
                case 'line':
                case 'linec':
                case 'radar':
                case 'radarc':
                    include_once './modules/forms/jpgraph/jpgraph_line.php';
                    include_once './modules/forms/jpgraph/jpgraph_bar.php';
                    include_once './modules/forms/jpgraph/jpgraph_radar.php';

                    $arrLabels = array();        // tableau des libellés du graphique
                    $arrDataModel = array();     // tableau type d'un dataset (une courbe)
                    $arrData = array();          // Tableau des données
                    $arrCount = array();         // Tableau des compteurs (pour moyenne notamment)
                    $arrTotal = array();         // tableau contenant le total pour chaque indice (permet de calculer les valeurs en pourcentage)

                    $intTsNow = ploopi_createtimestamp();

                    /**
                     * Lecture du champ qui sert de référence pour la base de temps
                     */
                    $strTimeField = $this->fields['timefield'];
                    if ($strTimeField == '0') $strTimeField = 'date_validation';


                    /**
                     * Détermination du min/max de la base de temps en fonction du jeu de données
                     * @todo optimiser la recherche (en adaptant la requête ?)
                     */

                    $intTsMin = null;
                    $intTsMax = null;
                    foreach($arrFormData as $arrLine)
                    {
                        if (is_null($intTsMin) || $arrLine[$strTimeField] < $intTsMin) $intTsMin = $arrLine[$strTimeField];
                        if (is_null($intTsMax) || $arrLine[$strTimeField] > $intTsMax) $intTsMax = $arrLine[$strTimeField];
                    }

                    /**
                     * Initialisation des données
                     */
                    switch($this->fields['line_aggregation'])
                    {
                        case 'hour':
                            // Définition de l'intervalle de données
                            $intTsMin = substr($intTsMin, 0, 10).'0000';
                            $intTsMax = substr($intTsMax, 0, 10).'5959';

                            $intTs = $intTsMin;

                            $intI = 0;
                            while ($intTs < $intTsMax)
                            {

                                $arrDataModel[$intI] = 0;
                                $arrLabels[$intI] = date("H\h\n(d)", ploopi_timestamp2unixtimestamp($intTs));
                                $arrTotal[$intI] = 0; // Valeur totale pour chaque indice

                                $intTs = ploopi_timestamp_add($intTs, 1);

                                $intI++;
                            }

                            if (empty($intGraphWidth))
                            {
                                $intGraphWidth = $intI*30;
                                if ($intGraphWidth < 500) $intGraphWidth = 500;
                            }

                            /*
                            $intTsMin = ploopi_unixtimestamp2timestamp(mktime(date('G') - 23, 0, 0));
                            for ($intI = 0; $intI < 24; $intI++)
                            {
                                $arrDataModel[$intI] = 0;
                                $intTsLabel = mktime(date('G') - 23 + $intI, 0, 0);
                                $arrLabels[$intI] = date('d-H', $intTsLabel).'h';
                                $arrTotal[$intI] = 0; // Valeur totale pour chaque indice
                            }
                            */
                            $strTitleX = 'Heure (Jour)';
                        break;

                        case 'day':
                            // Définition de l'intervalle de données
                            $intTsMin = substr($intTsMin, 0, 8).'000000';
                            $intTsMax = substr($intTsMax, 0, 8).'235959';

                            $intTs = $intTsMin;

                            $intI = 0;
                            while ($intTs < $intTsMax)
                            {
                                $arrDataModel[$intI] = 0;
                                $arrLabels[$intI] = date("d\nm", ploopi_timestamp2unixtimestamp($intTs));
                                $arrTotal[$intI] = 0; // Valeur totale pour chaque indice

                                $intTs = ploopi_timestamp_add($intTs, 0, 0, 0, 0, 1);

                                $intI++;
                            }

                            if (empty($intGraphWidth))
                            {
                                $intGraphWidth = $intI*30;
                                if ($intGraphWidth < 500) $intGraphWidth = 500;
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
                            // Définition de l'intervalle de données
                            $intTsMin = substr($intTsMin, 0, 8).'000000';
                            $intTsMax = substr($intTsMax, 0, 8).'235959';

                            $intTs = $intTsMin;

                            $intI = 0;
                            while ($intTs < $intTsMax)
                            {
                                $arrDataModel[$intI] = 0;
                                $arrLabels[$intI] = date("  m\nY", ploopi_timestamp2unixtimestamp($intTs));
                                $arrTotal[$intI] = 0; // Valeur totale pour chaque indice

                                $intTs = ploopi_timestamp_add($intTs, 0, 0, 0, 1, 0);

                                $intI++;
                            }

                            if (empty($intGraphWidth))
                            {
                                $intGraphWidth = $intI*40;
                                if ($intGraphWidth < 500) $intGraphWidth = 500;
                            }

                            $strTitleX = 'Mois / Année';
                        break;
                    }


                    // Intervalle trop petit
                    if ($intI <= 1) ploopi_die();

                    // Initialisation des dataset avec 0
                    for ($intI = 1; $intI <= 5; $intI++) // Courbes
                    {
                        if (!empty($this->fields["line{$intI}_field"])) // Courbe valide
                        {
                            $arrData[$intI] = $arrDataModel; // Un tableau de données par courbe
                            $arrCount[$intI] = $arrDataModel; // Un tableau de données par courbe
                        }

                    }

                    foreach($arrFormData as $arrLine)
                    {
                        // 1. Filtrage sur la date par rapport à la période choisie
                        if ($arrLine[$strTimeField] >= $intTsMin && $arrLine[$strTimeField] <= $intTsMax)
                        {
                            // 2. Détermination de l'appartenance à la courbe en fonction du filtre
                            foreach(array_keys($arrData) as $intI)
                            {
                                $booFilterOk = false;
                                if ($this->fields["line{$intI}_filter_op"] != '' && $this->fields["line{$intI}_filter_value"] != '')
                                {
                                    if (isset($arrFormFields[$this->fields["line{$intI}_field"]]) && isset($arrLine[$arrFormFields[$this->fields["line{$intI}_field"]]->fields['fieldname']]))
                                    {
                                        $strVal1 = trim($arrLine[$arrFormFields[$this->fields["line{$intI}_field"]]->fields['fieldname']]);
                                        $strVal2 = trim($this->fields["line{$intI}_filter_value"]);

                                        switch($this->fields["line{$intI}_filter_op"])
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
                                }
                                else $booFilterOk = true;

                                if ($booFilterOk) // Filtre ok, la donnée appartient à cette courbe
                                {
                                    // Détermination de l'indice de la donnée sur la courbe
                                    $intIndice = 0;

                                    switch($this->fields['line_aggregation'])
                                    {
                                        case 'hour':
                                            echo '<br />'.$intTsMin;
                                            echo '<br />'.substr($arrLine[$strTimeField], 0, 10).'0000';

                                            echo '<br />'.$intIndice = self::__diffDate(
                                                ploopi_timestamp2unixtimestamp($intTsMin),
                                                ploopi_timestamp2unixtimestamp(substr($arrLine[$strTimeField], 0, 10).'0000'),
                                                'h'
                                            );
                                        break;

                                        case 'day':
                                            $intIndice = round((ploopi_timestamp2unixtimestamp(substr($arrLine[$strTimeField], 0, 8).'000000') - ploopi_timestamp2unixtimestamp($intTsMin)) / (3600*24));
                                        break;

                                        case 'week':
                                            $intIndice = 0;
                                        break;

                                        case 'month':
                                            $intIndice = 11 - (12 + date('n') - date('n', ploopi_timestamp2unixtimestamp($arrLine[$strTimeField]))) % 12;
                                        break;
                                    }

                                    if (isset($arrData[$intI][$intIndice]))
                                    {
                                        switch($this->fields["line{$intI}_operation"])
                                        {
                                            case 'count':
                                                $arrData[$intI][$intIndice]++;
                                            break;

                                            case 'sum':
                                            case 'avg':
                                                $arrData[$intI][$intIndice] += floatval($arrLine[$this->fields["line{$intI}_field"]]);
                                                $arrCount[$intI][$intIndice]++;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }


                    //ploopi_print_r($arrLabels);
                    //die();


                    // Post-traitement spécial pour calculer la moyenne
                    foreach($arrData as $intI => $arrDataDetail)
                    {
                        if ($this->fields["line{$intI}_operation"] == 'avg')
                        {
                            foreach($arrDataDetail as $intIndice => $mixVal)
                            {
                                if (!empty($arrCount[$intI][$intIndice])) $arrData[$intI][$intIndice] = round($arrData[$intI][$intIndice] / $arrCount[$intI][$intIndice], 2);
                            }
                        }
                    }

                    // Post-traitement spécial pour calculer les valeurs en pourcentage
                    if ($this->fields["percent"])
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
                    if ($this->fields['type'] == 'linec' || $this->fields['type'] == 'barc' || $this->fields['type'] == 'radarc')
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
                    if ($this->fields["percent"])
                    {
                        foreach($arrData as $intI => $arrDataDetail)
                        {
                            foreach($arrDataDetail as $intIndice => $mixVal)
                            {
                                $arrData[$intI][$intIndice] = empty($arrTotal[$intIndice]) ? 0 : round(($arrData[$intI][$intIndice] * 100) / $arrTotal[$intIndice], 2);
                            }
                        }
                    }

                    if (in_array($this->fields['type'], array('radar', 'radarc')))
                    {
                        $objGraph = new RadarGraph($intGraphWidth, $intGraphHeight, "auto");
                        $objGraph->SetScale("lin");
                        $objGraph->SetCenter(0.4, 0.55);

                        $objGraph->axis->title->Set($strTitleX);
                        $objGraph->axis->title->SetFont(FF_VERDANA, FS_NORMAL, 10);
                        $objGraph->axis->SetFont(FF_VERDANA, FS_NORMAL, 8);

                        $objGraph->grid->Show();
                        $objGraph->grid->SetColor("lightgray");

                        $objGraph->SetTitles($arrLabels);
                    }
                    else
                    {
                        $objGraph = new Graph($intGraphWidth, $intGraphHeight);
                        $objGraph->SetScale("textlin");

                        $objGraph->xaxis->title->Set($strTitleX);
                        $objGraph->xaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 10);

                        $objGraph->xaxis->SetFont(FF_VERDANA, FS_NORMAL, 8);
                        $objGraph->xaxis->SetTickLabels($arrLabels);
                        //$objGraph->xaxis->SetLabelAngle(45);
                        $objGraph->xaxis->SetPos("min");
                        $objGraph->xaxis->SetTitleMargin(30); // Marge pour le titre

                        if ($this->fields['percent']) $objGraph->yaxis->title->Set('%');
                        $objGraph->yaxis->title->SetFont(FF_VERDANA, FS_NORMAL, 10);
                        $objGraph->yaxis->SetFont(FF_VERDANA, FS_NORMAL, 8);
                        //$objGraph->yaxis->SetLabelAngle(45);

                    }

                    // /!\ antialiasing non dispo dans la version de GD2 incluse dans debian etch
                    //$objGraph->img->SetAntiAliasing(true);

                    $objGraph->title->Set($this->fields['label']);
                    $objGraph->title->SetFont(FF_VERDANA, FS_NORMAL, 15);

					// Mise en forme de la légende
                    $objGraph->legend->SetFont(FF_VERDANA, FS_NORMAL, 8);
                    //$objGraph->legend->SetLayout(LEGEND_HOR); // Bloc de légende horizontal
                    $objGraph->legend->SetAbsPos(0, 40, "right", "top"); // Positionnement absolu de la légende

                    $objGraph->SetFrame(false); // Cadre
                    $objGraph->SetColor('white'); // Couleur de fond
                    $objGraph->img->SetTransparent('white');
                    $objGraph->img->SetMargin(40, 20, 120, 60); // gauche, droite, haut, bas

                    $arrObjPlots = array();

                    $intC = 0;
                    foreach($arrData as $intI => $arrPlots)
                    {
                        $strColor = $this->fields["line{$intI}_color"];
                        if (empty($strColor)) $strColor = 'black';

                        switch($this->fields['type'])
                        {
                            case 'line':
                            case 'linec':
                                // Création d'une série de points avec une courbe
                                $arrObjPlots[] = $objPlots = new LinePlot($arrPlots);
                                //ploopi_print_r($arrPlots);

                                // Chaque point de la courbe
                                // Type de point
                                $objPlots->mark->SetType(MARK_FILLEDCIRCLE);
                                // Couleur de remplissage
                                $objPlots->mark->SetFillColor($strColor);
                                // Taille
                                $objPlots->mark->SetWidth(5);

                                // E
                                $objPlots->SetLineWeight(1);

                            break;

                            case 'bar':
                            case 'barc':
                                // Création d'une série de barres
                                $arrObjPlots[] = $objPlots = new BarPlot($arrPlots);
                                if ($this->fields['type'] == 'bar' || $intC == sizeof($arrData)-1)
                                $objPlots->SetShadow('gray');

                                $intC++;
                            break;

                            case 'radar':
                            case 'radarc':
                                $arrObjPlots[] = $objPlots = new RadarPlot($arrPlots);

                                // Chaque point du radar
                                // Type de point
                                $objPlots->mark->SetType(MARK_SQUARE);
                                // Couleur de remplissage
                                $objPlots->mark->SetFillColor($strColor);

                                $objPlots->SetLineWeight(1);

                            break;
                        }

                        // Valeurs: Apparence de la police
                        /*
                        $objPlots->value->SetFont(FF_VERDANA, FS_NORMAL, 10);
                        $objPlots->value->SetFormat('%d');
                        $objPlots->value->SetColor($strColor);
                        */


                        // Couleur de la courbe
                        $objPlots->SetColor($strColor);

                        if ($this->fields['filled'])
                        {
                            // Couleur de remplissage de la courbe
                            $objPlots->SetFillColor($strColor);
                        }

                        $objField = new formsField();
                        if ($objField->open($this->fields["line{$intI}_field"]))
                        {
                            $strLegend = trim($objField->fields['name']);
                            if ($this->fields["line{$intI}_filter_value"] != '') $strLegend .= ' - '.trim($this->fields["line{$intI}_filter_value"]);

                            if ($this->fields['percent']) $strLegend .= ' (%)';
                            elseif (isset($forms_graphic_operation[$this->fields["line{$intI}_operation"]])) $strLegend .= ' ('.$forms_graphic_operation[$this->fields["line{$intI}_operation"]].')';

                            $objPlots->SetLegend($strLegend);
                        }
                    }


                   //ploopi_die($arrData);


                    if (in_array($this->fields['type'], array('linec', 'barc', 'radarc')))
                    {
                        // Inversion de l'ordre d'affchage des courbes (notamment pour gérer correctement l'affichage des courbes cumulées)
                        $arrObjPlots = array_reverse($arrObjPlots);
                    }

                    if (in_array($this->fields['type'], array('line', 'linec', 'barc', 'radar', 'radarc')))
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
            header('Content-disposition: inline; filename="graphique.png"');

        }
    }
}
?>