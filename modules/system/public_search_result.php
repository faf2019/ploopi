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
 * Interface de résultat du moteur de recherche intégrale
 *
 * @package system
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Récupération des paramètres de recherche
 */

if (isset($_REQUEST['system_search_keywords']))     $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] = $_REQUEST['system_search_keywords'];
if (isset($_REQUEST['system_search_workspace']))    $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'] = $_REQUEST['system_search_workspace'];
// Identifiant d'instance (entier) ou vide : il entre dans la requête de recherche
if (isset($_REQUEST['system_search_module']))       $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module'] = is_numeric($_REQUEST['system_search_module']) ? (int) $_REQUEST['system_search_module'] : '';
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

    // Seulement un module de l'espace courant, actif (contrôlé par system_search_results)
    $arrRelevance = system_search_results($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'], $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module']);

    $booP4 = ploopi\skin::get()->version() >= 4;

    if (empty($arrRelevance) && $booP4) echo ploopi\system\ui::emptystate('search', _SYSTEM_MYS_SEARCH_NONE);
    elseif (empty($arrRelevance))
    {
        ?>
        <div class="system_bandeau system_bandeau_clair system_bandeau_titre">
        Saisissez un mot clé puis cliquez sur "Rechercher" ou appuyez sur "Entrée"
        </div>
        <?php
    }
    else
    {
        $columns = array();
        $values = array();
        $c = 0;

        $columns['left']['relevance']       = array('label' => 'Pert.', 'width' => 65, 'options' => array('sort' => true, 'sort_flag' => SORT_NUMERIC));
        $columns['auto']['label']           = array('label' => 'Libellé', 'options' => array('sort' => true));

        if (!empty($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_search_displayindexed']))
            $columns['right']['timestp_lastindex']      = array('label' => 'Indexé le', 'width' => '90', 'options' => array('sort' => true));

        if (!empty($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_search_displaydatetime']))
            $columns['right']['timestp_create']         = array('label' => 'Ajouté le', 'width' => '140', 'options' => array('sort' => true));

        if (!empty($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_search_displayuser']))
            $columns['right']['user']           = array('label' => 'Utilisateur', 'width' => '120', 'options' => array('sort' => true));

        if (!empty($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_search_displayworkspace']))
            $columns['right']['workspace']      = array('label' => 'Espace', 'width' => '120', 'options' => array('sort' => true));

        if (!empty($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_search_displaymodule']))
            $columns['right']['module']         = array('label' => 'Module', 'width' => '120', 'options' => array('sort' => true));

        if (!empty($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_search_displayobjecttype']))
            $columns['right']['object_type']    = array('label' => 'Type d\'Objet', 'width' => '120', 'options' => array('sort' => true));

        // on parcourt le tableau des réponses (modules, types d'objets et droits déjà contrôlés)
        foreach ($arrRelevance as $c => $row)
        {
            $objUser = new ploopi\user();
            $strUserName = ($objUser->open($row['id_user'])) ? "{$objUser->fields['firstname']} {$objUser->fields['lastname']}" : '';

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

            $l_timestp_lastindex = ploopi\date::timestamp2local($row['timestp_lastindex']);
            $l_timestp_create = ploopi\date::timestamp2local($row['timestp_create']);

            $objWorkspace = new ploopi\workspace();
            $strWorkspaceLabel = ($objWorkspace->open($row['id_workspace'])) ? $objWorkspace->fields['label'] : '';

            $values[$c]['values']['relevance'] = array('label' => sprintf("<span style=\"width:12px;height:12px;float:left;border:1px solid #a0a0a0;background-color:#%s;margin-right:3px;\"></span>%d %%", $color, $row['relevance']), 'sort_label' => intval($row['relevance']));
            // ploopi4 : jauge native plutôt que la pastille colorée
            if ($booP4) $values[$c]['values']['relevance']['label'] = sprintf('<span class="p4-relevance"><meter min="0" max="100" low="40" high="70" optimum="100" value="%1$d" aria-hidden="true"></meter>%1$d %%</span>', $row['relevance']);
            $values[$c]['values']['label'] = array('label' => ploopi\str::htmlentities($row['label']));
            $values[$c]['values']['timestp_lastindex'] = array('label' => $l_timestp_lastindex['date'], 'sort_label' => $row['timestp_lastindex']);
            $values[$c]['values']['timestp_create'] = array('label' => $l_timestp_create['date'].' '.$l_timestp_create['time'], 'sort_label' => $row['timestp_create']);
            $values[$c]['values']['user'] = array('label' => ploopi\str::htmlentities($strUserName));
            $values[$c]['values']['workspace'] = array('label' => ploopi\str::htmlentities($strWorkspaceLabel));
            $values[$c]['values']['module'] = array('label' => ploopi\str::htmlentities($row['module']));
            $values[$c]['values']['object_type'] = array('label' => ploopi\str::htmlentities($row['object']));

            $values[$c]['description'] = ploopi\str::htmlentities($row['label']);
            $values[$c]['link'] = $row['link'];
            $values[$c]['style'] = '';
        }
        ?>
        <div class="<?php echo $booP4 ? 'p4-mt-16' : 'system_bandeau_clair'; ?>">
        <?php system_display_array($columns, $values, 'system_search', array('sortable' => true, 'orderby_default' => 'relevance', 'sort_default' => 'DESC', 'limit' => 25)); ?>
        </div>
        <?php
    }
}
?>
