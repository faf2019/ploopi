<?php
/*
    Copyright (c) 2007-2011 Ovensia
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
include_once './modules/forms/jpgraph/jpgraph.php';


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
    private static $_arrDefaultOptions = array(
        // GLOBAL
        'transparency' => '0.2',        // Transparence des courbes, contours, sauf pie/pie3d
        'fill_transparency' => '0.5',   // Transparence des remplissages, sauf pie/pie3d
        'font' => FF_VERA,               // FF_VERDANA, FF_VERA, FF_VERAMONO, FF_VERASERIF, FF_DV_SANSSERIF, FF_DV_SERIF, FF_DV_SANSSERIFMONO, FF_DV_SERIFCOND, FF_DV_SANSSERIFCOND
        'font_size_title' => '15',
        'font_size_legend' => '8',
        'font_size_data' => '10',
        'margin_left' => '40',            // sauf pie/pie3d
        'margin_right' => '20',
        'margin_top' => '120',
        'margin_bottom' => '60',
        'label_angle' => '0',
        // PIE/RADAR
        'center_x' => '0.5',
        'center_y' => '0.6',
        // LINE/RADAR
        'mark_type' => MARK_SQUARE,
        'mark_width' => 3,               // Largeur des marqueurs
        'mark_transparency' => '0.5',    // Transparence des marqueurs
        'line_weight' => '5',            // ??
        // BAR
        'shadow' => true,
        'shadow_transparency' => '0.8',
    );


    /**
     * Calcule la différence entre 2 dates (version simple)
     * @param integer $intTs2
     * @param integer $intTs2
     * @param string $strType 'h' (hours), 'd' (days), 'w' (weeks), 'm' (months)
     * @return integer différence dans l'unité demandée
     */
    private static function __diffDate($intTs1, $intTs2, $strType = 'h')
    {
        switch($strType)
        {
            case 'h' : return floor(($intTs2 - $intTs1)/3600); break;
            case 'd' : return floor(($intTs2 - $intTs1)/86400); break;
            case 'm' : return (date('Y', $intTs2) - date('Y', $intTs1))*12 + (date('n', $intTs2) - date('n', $intTs1)); break;
            case 'w' : return 0; break;
        }
    }

    /**
     * Calcule le dégradé par étape entre 2 couleurs
     */
    private static function __gradient($HexFrom, $HexTo, $ColorSteps)
    {
        if (substr($HexFrom, 0, 1) == '#') $HexFrom = substr($HexFrom, 1, strlen($HexFrom) - 1);
        if (substr($HexTo, 0, 1) == '#') $HexTo = substr($HexTo, 1, strlen($HexTo) - 1);


        $FromRGB['r'] = hexdec(substr($HexFrom, 0, 2));
        $FromRGB['g'] = hexdec(substr($HexFrom, 2, 2));
        $FromRGB['b'] = hexdec(substr($HexFrom, 4, 2));

        $ToRGB['r'] = hexdec(substr($HexTo, 0, 2));
        $ToRGB['g'] = hexdec(substr($HexTo, 2, 2));
        $ToRGB['b'] = hexdec(substr($HexTo, 4, 2));

        $StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps - 1);
        $StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps - 1);
        $StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps - 1);

        $GradientColors = array();

        for($i = 0; $i <= $ColorSteps; $i++)
        {
                $RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
                $RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
                $RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));

                $HexRGB['r'] = sprintf('%02x', ($RGB['r']));
                $HexRGB['g'] = sprintf('%02x', ($RGB['g']));
                $HexRGB['b'] = sprintf('%02x', ($RGB['b']));

                $GradientColors[] = implode(NULL, $HexRGB);
        }

        foreach($GradientColors as &$Color) $Color = "#{$Color}";

        return $GradientColors;
    }

    /**
     * Affiche un message d'erreur graphique
     */

    private static function __renderError($strMsg, $intGraphWidth, $intGraphHeight)
    {
        // Affichage d'une image d'erreur
        $resImg = imagecreatetruecolor ($intGraphWidth, $intGraphHeight);
        $white = imagecolorallocate($resImg, 255, 255, 255);
        $black = imagecolorallocate($resImg, 100, 100, 100);

        imagefill($resImg, 0, 0, $white);
        imagerectangle($resImg, 0, 0, $intGraphWidth-1, $intGraphHeight-1, $black);
        imagettftext($resImg, 20, 0, 20, 200, $black, "./modules/forms/fonts/verdana.ttf",  $strMsg);

        ploopi_ob_clean();
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; Filename="erreur.png"');
        header('Cache-Control: private');
        header('Pragma: private');
        header('Content-Encoding: None');

        imagepng($resImg);
        ploopi_die();
    }

    /**
     * Constructeur de la classe
     *
     * @return formsGraphic
     */

    public function __construct() { parent::__construct('ploopi_mod_forms_graphic'); }

    /**
     * Gère le clone
     */

    public function __clone()
    {
        // Personnalisation du clone
        $this->new = true;
        $this->fields['id'] = null;
    }



    public function render($intGraphWidth = null, $intGraphHeight = null)
    {
        global $forms_graphic_operation;

        if (empty($intGraphHeight)) $intGraphHeight = 450;


        $arrOptions = array();

        // Recherche des paramètres
        foreach($this->fields as $strField => $strValue)
        {
            // C'est un paramètre de graphique
            if (substr($strField, 0, 6) == 'param_')
            {
                $arrOptions[substr($strField, 6-strlen($strField))] = $strValue;
            }
        }

        $arrOptions = array_merge(self::$_arrDefaultOptions, $arrOptions);

        $objForm = new formsForm();

        if (!empty($this->fields['id_form']) && $objForm->open($this->fields['id_form']))
        {
            // Lecture des données
            list($arrFormData) = $objForm->prepareData(true, true, false, true);

            // Jeu de données vide => erreur
            if (empty($arrFormData)) self::__renderError("Il n'y a pas de données à afficher", $intGraphWidth, $intGraphHeight);

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

                    // Création du graph
                    // On spécifie la largeur et la hauteur du graph
                    $objGraph = new PieGraph($intGraphWidth, $intGraphHeight);

                    $objGraph->title->Set($this->fields['label']);
                    $objGraph->title->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_title']);
                    $objGraph->SetFrame(false); // optional, if you don't want a frame border
                    $objGraph->legend->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_legend']);

                    $objGraph->SetAntiAliasing();

                    if ($this->fields['type'] == 'pie3d') $objPie = new PiePlot3D(array_values($arrData));
                    else $objPie = new PiePlot(array_values($arrData));

                    // Position du graphique (0.5=centré)
                    $objPie->SetCenter($arrOptions['center_x'], $arrOptions['center_y']);

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
                    $objPie->value->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_data']);

                    $objPie->SetLegends(array_keys($arrData));

                    $objPie->SetSliceColors(self::__gradient($this->fields['pie_color1'], $this->fields['pie_color2'], sizeof($arrData)));

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
                    else $strTimeField = $arrFormFields[$strTimeField]->fields['fieldname'];

                    /**
                     * Détermination du min/max de la base de temps en fonction du jeu de données
                     * @todo optimiser la recherche (en adaptant la requête ?)
                     */

                    $intTsMin = null;
                    $intTsMax = null;
                    foreach($arrFormData as $arrLine)
                    {
                        if (!empty($arrLine[$strTimeField]))
                        {
                            if (is_null($intTsMin) || $arrLine[$strTimeField] < $intTsMin) $intTsMin = $arrLine[$strTimeField];
                            if (is_null($intTsMax) || $arrLine[$strTimeField] > $intTsMax) $intTsMax = $arrLine[$strTimeField];
                        }
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

                            if (empty($intGraphWidth))
                            {
                                $intGraphWidth = $intI*30;
                                if ($intGraphWidth < 500) $intGraphWidth = 500;
                            }

                        break;

                        case 'month':
                            // Définition de l'intervalle de données
                            $intTsMin = substr($intTsMin, 0, 6).'01000000';
                            $intTsMax = substr($intTsMax, 0, 6).'31235959';

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

                    // Intervalle trop petit => erreur
                    if ($intI <= 1) self::__renderError("Il n'y a pas de données à afficher", $intGraphWidth, $intGraphHeight);

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
                                if ($this->fields["line{$intI}_filter"] != '' && $this->fields["line{$intI}_filter_op"] != '' && $this->fields["line{$intI}_filter_value"] != '')
                                {
                                    if (isset($arrFormFields[$this->fields["line{$intI}_filter"]]) && isset($arrLine[$arrFormFields[$this->fields["line{$intI}_filter"]]->fields['fieldname']]))
                                    {
                                        $strVal1 = strtoupper(ploopi_convertaccents(trim($arrLine[$arrFormFields[$this->fields["line{$intI}_filter"]]->fields['fieldname']])));
                                        $strVal2 = strtoupper(ploopi_convertaccents(trim($this->fields["line{$intI}_filter_value"])));

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

                                            case '<>':
                                                $booFilterOk = ($strVal1 <> $strVal2);
                                            break;

                                            case 'in':
                                                $booFilterOk = in_array($strVal1, explode(',', $strVal2));
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
                                            $intIndice = self::__diffDate(
                                                ploopi_timestamp2unixtimestamp($intTsMin),
                                                ploopi_timestamp2unixtimestamp(substr($arrLine[$strTimeField], 0, 10).'0000'),
                                                'h'
                                            );
                                        break;

                                        case 'day':
                                            $intIndice = self::__diffDate(
                                                ploopi_timestamp2unixtimestamp($intTsMin),
                                                ploopi_timestamp2unixtimestamp(substr($arrLine[$strTimeField], 0, 8).'000000'),
                                                'd'
                                            );
                                        break;

                                        case 'week':
                                            $intIndice = 0;
                                        break;

                                        case 'month':
                                            $intIndice = self::__diffDate(
                                                ploopi_timestamp2unixtimestamp($intTsMin),
                                                ploopi_timestamp2unixtimestamp(substr($arrLine[$strTimeField], 0, 6).'01000000'),
                                                'm'
                                            );
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
                                                $arrData[$intI][$intIndice] += floatval($arrLine[$arrFormFields[$this->fields["line{$intI}_field"]]->fields['fieldname']]);
                                                $arrCount[$intI][$intIndice]++;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

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
                        $objGraph->SetCenter($arrOptions['center_x'], $arrOptions['center_y']);

                        $objGraph->axis->title->Set($strTitleX);
                        $objGraph->axis->title->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_data']);
                        $objGraph->axis->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_data']);

                        $objGraph->grid->Show();
                        $objGraph->grid->SetColor("lightgray");

                        $objGraph->SetTitles($arrLabels);
                    }
                    else
                    {
                        $objGraph = new Graph($intGraphWidth, $intGraphHeight);
                        $objGraph->SetScale("textlin");

                        $objGraph->xaxis->title->Set($strTitleX);
                        $objGraph->xaxis->title->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_data']);

                        $objGraph->xaxis->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_data']);
                        $objGraph->xaxis->SetTickLabels($arrLabels);
                        $objGraph->xaxis->SetLabelAngle($arrOptions['label_angle']);
                        $objGraph->xaxis->SetPos("min");
                        $objGraph->xaxis->SetTitleMargin(30); // Marge pour le titre

                        if ($this->fields['percent']) $objGraph->yaxis->title->Set('%');
                        $objGraph->yaxis->title->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_data']);
                        $objGraph->yaxis->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_data']);
                        $objGraph->yaxis->SetLabelAngle($arrOptions['label_angle']);

                    }

                    // /!\ antialiasing non dispo dans la version de GD2 incluse dans debian etch
                    //$objGraph->img->SetAntiAliasing(true);

                    $objGraph->title->Set($this->fields['label']);
                    $objGraph->title->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_title']);

                    // Mise en forme de la légende
                    $objGraph->legend->SetFont($arrOptions['font'], FS_NORMAL, $arrOptions['font_size_legend']);
                    //$objGraph->legend->SetLayout(LEGEND_HOR); // Bloc de légende horizontal
                    $objGraph->legend->SetAbsPos(0, 40, "right", "top"); // Positionnement absolu de la légende

                    $objGraph->SetFrame(false); // Cadre
                    $objGraph->SetColor('white'); // Couleur de fond
                    $objGraph->img->SetTransparent('white');
                    $objGraph->img->SetMargin(
                        $arrOptions['margin_left'],
                        $arrOptions['margin_right'],
                        $arrOptions['margin_top'],
                        $arrOptions['margin_bottom']
                    );

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

                                // Chaque point de la courbe
                                // Type de point
                                $objPlots->mark->SetType($arrOptions['mark_type']);
                                // Couleur de remplissage
                                $objPlots->mark->SetFillColor("{$strColor}@{$arrOptions['mark_transparency']}");
                                // Taille
                                $objPlots->mark->SetWidth($arrOptions['mark_width']);

                            break;

                            case 'bar':
                            case 'barc':
                                // Création d'une série de barres
                                $arrObjPlots[] = $objPlots = new BarPlot($arrPlots);
                                if ($this->fields['type'] == 'bar' || $intC == sizeof($arrData)-1)
                                if ($arrOptions['shadow'])
                                {
                                    $objPlots->SetShadow("{$strColor}@{$arrOptions['shadow_transparency']}");
                                }

                                $intC++;
                            break;

                            case 'radar':
                            case 'radarc':
                                $arrObjPlots[] = $objPlots = new RadarPlot($arrPlots);

                                // Chaque point du radar
                                // Type de point
                                $objPlots->mark->SetType($arrOptions['mark_type']);
                                // Couleur de remplissage
                                $objPlots->mark->SetFillColor("{$strColor}@{$arrOptions['mark_transparency']}");
                                // Taille
                                $objPlots->mark->SetWidth($arrOptions['mark_width']);

                                $objPlots->SetLineWeight(3);

                            break;
                        }


                        // Couleur du trait/contour
                        $objPlots->SetColor("{$strColor}@{$arrOptions['transparency']}");

                        // Couleur de remplissage
                        if ($this->fields['filled']) $objPlots->SetFillColor("{$strColor}@{$arrOptions['fill_transparency']}");
                        else $objPlots->SetFillColor("{$strColor}@1");


                        $strLegend = $this->fields["line{$intI}_legend"];

                        if ($strLegend == '')
                        {
                            $objField = new formsField();
                            if ($objField->open($this->fields["line{$intI}_field"]))
                            {
                                $strLegend = trim($objField->fields['name']);

                                if ($this->fields['percent']) $strLegend .= ' (%)';
                                elseif (isset($forms_graphic_operation[$this->fields["line{$intI}_operation"]])) $strLegend .= ' ('.$forms_graphic_operation[$this->fields["line{$intI}_operation"]].')';

                            }

                            $objField = new formsField();
                            if ($objField->open($this->fields["line{$intI}_filter"]))
                            {
                                if (!empty($strLegend)) $strLegend .= ' | ';

                                $strLegend .= trim($objField->fields['name']);
                                if ($this->fields["line{$intI}_filter_value"] != '') $strLegend .= ' '.$this->fields["line{$intI}_filter_op"].' '.trim($this->fields["line{$intI}_filter_value"]);

                            }
                        }

                        $objPlots->SetLegend($strLegend);
                    }


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

            $strDisposition = 'attachment';

            header('Content-Type: image/png');
            header('Content-Disposition: '.$strDisposition.'; Filename="graphique.png"');
            header('Cache-Control: private');
            header('Pragma: private');
            header('Content-Encoding: None');

        }
    }
}
?>
