<?php
/*
    Copyright (c) 2008 Ovensia
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
 * Fonctions, constantes, variables globales
 *
 * @package webedit
 * @subpackage global
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Définition des constantes
 */

/**
 * Action : Gérer les types de ressources
 */
define ('_BOOKING_ACTION_ADMIN_TYPERESOURCE',   10);

/**
 * Action : Gérer les ressources
 */
define ('_BOOKING_ACTION_ADMIN_RESOURCE',       11);

/**
 * Action : Gérer les validations
 */
define ('_BOOKING_ACTION_VALIDATE',             20);

/**
 * Action : Faire une demande
 */
define ('_BOOKING_ACTION_ASKFOREVENT',          30);


global $arrBookingPeriodicity;

$arrBookingPeriodicity =
    array(
        'day' => 'Quotidienne',
        'week' => 'Hebdomadaire',
        'month' => 'Mensuelle',
        'year' => 'Annuelle'
    );

global $arrBookingSize;

$arrBookingSize =
    array(
        '800x500',
        '1000x625',
        '1200x750'
    );

global $arrBookingColor;

$arrBookingColor =
    array(
        'validated' => '#66ff66',
        'canceled'  => '#ff8844',
        'deleted'  => '#ff4444',
        'unknown' => '#ffff88'
    );


/**
 * Renvoie la liste des espaces de travail fils
 *
 * @param int $idw identifiant de l'espace de travail parent
 * @return array tableau des espaces fils : array('list' => array(), 'tree' => array())
 */

function booking_get_workspaces($idw = 0)
{
    global $db;

    if (!$idw) $idw = $_SESSION['ploopi']['workspaceid'];

    $objWorkspace = new workspace();
    $objWorkspace->open($idw);
    $parents = "{$objWorkspace->fields['parents']};{$idw}";

    $arrWorkspaces = array(
        'list' => array(),
        'tree' => array()
    );

    // on recherche les groupes dont on est le groupe père (les fils en fait) et qui ont accès au module
    $result = $db->query("
        SELECT  w.*
        FROM    ploopi_workspace w,
                ploopi_module_workspace mw
        WHERE   (w.parents = '{$parents}' OR w.parents LIKE '{$parents};%' OR w.id = {$idw})
        AND     w.id = mw.id_workspace
        AND     mw.id_module = {$_SESSION['ploopi']['moduleid']}
        ORDER   BY w.depth, w.label
    ");

    // et on les trie pour les afficher sous forme d'un arbre
    while ($fields = $db->fetchrow($result))
    {
        $fields['children'] = 0;

        $arrWorkspaces['list'][$fields['id']] = $fields;

        // astuce pour trouver le premier noeud
        if (empty($arrWorkspaces['tree'])) $arrWorkspaces['tree'][0][] = $fields['id'];
        else $arrWorkspaces['tree'][$fields['id_workspace']][] = $fields['id'];

        // mise à jour du nombre de fils pour chaque espace
        $parent = $fields['id_workspace'];
        while (isset($arrWorkspaces['list'][$parent]))
        {
            $arrWorkspaces['list'][$parent]['children']++;
            $parent = $arrWorkspaces['list'][$parent]['id_workspace'];
        }
    }
    // si le groupe de l'utilisateur connecté a des groupes fils, ont doit en trouver la liste dans $workspaces['tree'][$_SESSION['ploopi']['workspaceid']]

    return($arrWorkspaces);
}

/**
 * Affiche l'arborescence des espaces fils avec des cases à cocher
 *
 * @param array $arrWorkspaces tableau des espaces
 * @param string $fieldname nom du champ à utiliser dans les checkbox
 * @param int $widsel identifiant de l'espace sélectionné
 * @param int $wid identifiant du noeud (espace) courant
 * @return string code html de l'arbre
 */

function booking_display_workspaces(&$arrWorkspaces, $fieldname, &$widsel = array(), $wid = 0)
{
    $treeview = '';

    if (!empty($arrWorkspaces['tree'][$wid]))
    {
        foreach($arrWorkspaces['tree'][$wid] as $wid_child)
        {
            //$checked = ($widsel == $wid_child) ? 'checked' : '';
            $checked = (in_array($wid_child, $widsel)) ? 'checked' : '';

            $subtree = booking_display_workspaces($arrWorkspaces, $fieldname, $widsel, $wid_child);

            $has_children = !empty($arrWorkspaces['tree'][$wid_child]);

            $is_opened = false;
            if (!empty($widsel))
            {
                foreach($widsel as $wsel)
                {
                    $parents = preg_split('/;/', $arrWorkspaces['list'][$wsel]['parents']);
                    $is_opened = $is_opened || (in_array($wid_child, $parents));
                    /*
                    $parents = preg_split('/;/', $arrWorkspaces['list'][$widsel]['parents']);
                    $is_opened = (in_array($wid_child, $parents));
                    */
                }
            }

            if ($has_children)
            {
                $link_start = "<a style=\"display:block;margin-left:20px;padding-left:4px;\" id=\"booking_tnode_{$wid_child}\" href=\"javascript:void(0);\" onclick=\"javascript:ploopi_switchdisplay('booking_subtree_{$wid_child}');\">";
                $link_stop = "</a>";
            }
            else
            {
                $link_start = "<div style=\"margin-left:20px;padding-left:4px;\">";
                $link_stop = "</div>";
            }

            $display = ($is_opened) ? 'block' : 'none';

            $option = (empty($arrWorkspaces['list'][$wid_child]['children'])) ? '' : " <sub>({$arrWorkspaces['list'][$wid_child]['children']})</sub>";

            $treeview .=    "
                            <div style=\"clear:both;line-height:20px;\">
                                <div style=\"float:left;width:20px;\"><input type=\"checkbox\" name=\"{$fieldname}\" value=\"{$wid_child}\" {$checked}></div>
                                    {$link_start}
                                    {$arrWorkspaces['list'][$wid_child]['label']}{$option}
                                    {$link_stop}
                            </div>
                            <div style=\"display:{$display};background:url(./modules/booking/img/line.gif) 3px 0 repeat-y;\" id=\"booking_subtree_{$wid_child}\">
                                <div style=\"margin-left:20px;\">
                                {$subtree}
                                </div>
                            </div>
                            ";
        }
    }

    return $treeview;
}


/**
 * Retourne les resources actives
 *
 * @param boolean $strict true si on ne veut que les resources gérées par l'espace courant
 * @param int $moduleid identifiant du module
 * @param int $moduleid identifiant de l'espace de travail
 * @return array tableau des resources actives
 */

function booking_get_resources($strict = false, $moduleid = -1, $workspaceid = -1)
{
    global $db;

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];
    if ($workspaceid == -1) $workspaceid = $_SESSION['ploopi']['workspaceid'];

    $arrResources = array();

    if (!$strict || ploopi_isactionallowed(_BOOKING_ACTION_VALIDATE, $workspaceid, $moduleid))
    {
        // Recherche des resources actives
        $db->query("
            SELECT      r.*,
                        rt.name as rt_name,
                        w.label as w_label

            FROM        (ploopi_mod_booking_resource r,
                        ploopi_mod_booking_resourcetype rt)

            LEFT JOIN   ploopi_workspace w
            ON          w.id = r.id_workspace

            WHERE       r.id_resourcetype = rt.id
            AND         r.active = 1
            AND         rt.active = 1
            AND         r.id_module = {$moduleid}

            GROUP BY    r.id

            ORDER BY    rt_name, name
        ");

        while ($row = $db->fetchrow()) $arrResources[$row['id']] = $row;

        // Récupération des espaces gestionnaires
        $db->query("
            SELECT      r.id,
                        w.id as w_id

            FROM        (ploopi_mod_booking_resource r,
                        ploopi_mod_booking_resourcetype rt,
                        ploopi_mod_booking_resource_workspace rw,
                        ploopi_workspace w)

            WHERE       r.id_resourcetype = rt.id
            AND         r.active = 1
            AND         rt.active = 1
            AND         rw.id_resource = r.id
            AND         w.id = rw.id_workspace
            AND         r.id_module = {$moduleid}

            ORDER BY    r.id, w.depth
        ");

        while ($row = $db->fetchrow())
        {
            $arrResources[$row['id']]['workspaces'][$row['w_id']] = $row['w_id'];
        }


        foreach($arrResources as $key => $res)
        {
            $booWorkspaceValidator = in_array($workspaceid, $res['workspaces']);

            // Validateur oui/non ?
            $arrResources[$key]['validator'] = ($booWorkspaceValidator && ploopi_isactionallowed(_BOOKING_ACTION_VALIDATE)) ? 1 : 0;

            // Application du filtre "strict" : On ne renvoit que les ressources gérées par l'espace courant
            if ($strict && !$booWorkspaceValidator) unset($arrResources[$key]);
        }
    }

    return $arrResources;
}

/**
 * Retourne les événements
 *
 * @param mixed $mixId identifiant de la ressource (entier/tableau d'entier)
 * @param boolean $extended true si la fonction doit retourne des informations complémentaires sur les événements
 * @param boolean $strict true si la fonction ne doit renvoyer que des éléments validés
 * @param int $validated vaut 0 : indéterminé, 1 : validé, -1 : refusé ou null
 * @param int $managed vaut 0 (non traité) ou 1 (traité) ou null
 * @param string $object objet de la recherche
 * @param string $requestedby initiateur de la demande de réservation
 * @param string $from date de début de la recherche
 * @param string $to date de fin de la recherche
 * @param int $moduleid identifiant du module
 * @return array tableau des événements
 */

function booking_get_events($mixId = null, $extended = false, $strict = false, $validated = null, $managed = null, $object = '', $requestedby = '', $from = '', $to = '', $moduleid = -1)
{
    global $db;

    if ($moduleid == -1) $moduleid = $_SESSION['ploopi']['moduleid'];

    $arrResources = booking_get_resources(true, $moduleid);

    $arrWhere = array();

    if (is_array($mixId)) $arrWhere[] = ' e.id_resource IN ('.implode(',', $mixId).')';
    elseif (is_numeric($mixId)) $arrWhere[] = " e.id_resource = '{$mixId}'";

    switch($validated)
    {
        case '0':   $arrWhere[] = " ed.canceled = 0 AND ed.validated = 0 "; break;
        case '1':   $arrWhere[] = " ed.validated = 1 "; break;
        case '-1':  $arrWhere[] = " ed.canceled = 1 "; break;
    }

    // On cherche les événements de l'utilisateur courant (sauf si pas connecté) et ceux qui sont validés et ceux dont l'utilisateur gère la resource :
    $arrWhereDetail = array();

    if ($_SESSION['ploopi']['modules'][$moduleid]['booking_eventfilter']) // Option du module permettant de passer outre le filtrage
    {
        if ($_SESSION['ploopi']['connected']) $arrWhereDetail[] = "e.id_user = {$_SESSION['ploopi']['userid']}";
        if (!$strict) $arrWhereDetail[] = 'ed.validated = 1';
    }
    else
    {
        $arrWhereDetail[] = 'ed.canceled != 1';
    }

    if (!empty($arrResources)) $arrWhereDetail[] = "e.id_resource IN (".implode(',', array_keys($arrResources)).")";
    if (!empty($arrWhereDetail)) $arrWhere[] = '('.implode(' OR ', $arrWhereDetail).')';

    // Recherche des événements
    if ($extended)
    {
        if ($managed == '1' || $managed == '0') $arrWhere[] = " e.managed = '".$db->addslashes($managed)."' ";
        if ($object != '') $arrWhere[] = " e.object LIKE '%".$db->addslashes($object)."%' ";
        if ($requestedby != '') $arrWhere[] = " (u.lastname LIKE '%".$db->addslashes($requestedby)."%' OR u.firstname LIKE '%".$db->addslashes($requestedby)."%' OR w.label LIKE '%".$db->addslashes($requestedby)."%')";
        if ($from != '') $arrWhere[] = " (ed.timestp_begin >= '".ploopi_local2timestamp($from)."' OR ed.timestp_end >= '".ploopi_local2timestamp($from)."') ";
        if ($to != '') $arrWhere[] = " (ed.timestp_begin <= '".substr(ploopi_local2timestamp($to), 0, 8)."235959' OR ed.timestp_end <= '".substr(ploopi_local2timestamp($to), 0, 8)."235959') ";

        $strWhere = ' AND '.implode(' AND ', $arrWhere);

        $db->query("
            SELECT      e.*,
                        ed.id as ed_id,
                        ed.timestp_begin,
                        ed.timestp_end,
                        ed.validated,
                        ed.canceled,
                        r.color,
                        r.reference,
                        r.name as r_name,
                        rt.name as rt_name,
                        w.label as w_label,
                        u.firstname as u_firstname,
                        u.lastname as u_lastname

            FROM        (ploopi_mod_booking_event e,
                        ploopi_mod_booking_event_detail ed,
                        ploopi_mod_booking_resource r,
                        ploopi_mod_booking_resourcetype rt,
                        ploopi_workspace w,
                        ploopi_user u)

            WHERE       e.id = ed.id_event
            AND         e.id_resource = r.id
            AND         e.id_module = {$moduleid}
            AND         r.id_resourcetype = rt.id
            AND         e.id_workspace = w.id
            AND         e.id_user = u.id
            {$strWhere}

            ORDER BY    ed.timestp_begin, ed.timestp_end
        ");
    }
    else
    {
       $strWhere = ' AND '.implode(' AND ', $arrWhere);

        $db->query("
            SELECT      e.*,
                        ed.id as ed_id,
                        ed.timestp_begin,
                        ed.timestp_end,
                        ed.validated,
                        ed.canceled,
                        r.color,
                        r.reference

            FROM        ploopi_mod_booking_event e,
                        ploopi_mod_booking_event_detail ed,
                        ploopi_mod_booking_resource r

            WHERE       e.id = ed.id_event
            AND         e.id_resource = r.id
            AND         e.id_module = {$moduleid}
            {$strWhere}

            ORDER BY    ed.timestp_begin, ed.timestp_end
        ");
    }

    return $db->getarray();
}
?>
