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
 * Gestion des objets insérables dans une page de contenu (WebEdit)
 *
 * @package system
 * @subpackage wce
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

global $articleid;
global $headingid;
global $template_name;

$op = empty($_REQUEST['op']) ? '' : $_REQUEST['op'];

// Nb lignes max par pages (sinon index)
$intMaxLines = ploopi_getparam('system_trombi_maxlines', 1);

// Lettre sélectionnée dans l'index
$strIndexSel = '';

/**
 * Initialisation du moteur de template.
 * Chargement du template.
 */

$objTplDirectory = new Template("./templates/frontoffice/{$template_name}");

if (file_exists("./templates/frontoffice/{$template_name}/system_trombi.tpl"))
{
    $objTplDirectory->set_filenames(array('system_trombi' => 'system_trombi.tpl'));

    // Récupération des paramètres
    global $arrFilter;
    $arrFilter = array();

    // On ne veut pas les caractères % et | dans la recherche avec LIKE
    $pattern = '/%|_/';

    // Lecture SESSION
    if (isset($_SESSION['system']['wce_search'])) $arrFilter = $_SESSION['system']['wce_search'];

    // Lecture Params
    if (isset($_POST['system_lastname']) && !preg_match($pattern, $_POST['system_lastname'])) $arrFilter['system_lastname'] = $_POST['system_lastname'];
    if (isset($_POST['system_firstname']) && !preg_match($pattern, $_POST['system_firstname'])) $arrFilter['system_firstname'] = $_POST['system_firstname'];
    if (isset($_POST['system_entity']) && !preg_match($pattern, $_POST['system_entity'])) $arrFilter['system_entity'] = $_POST['system_entity'];
    if (isset($_POST['system_service']) && !preg_match($pattern, $_POST['system_service'])) $arrFilter['system_service'] = $_POST['system_service'];
    if (isset($_POST['system_service2']) && !preg_match($pattern, $_POST['system_service2'])) $arrFilter['system_service2'] = $_POST['system_service2'];
    if (isset($_POST['system_phone']) && !preg_match($pattern, $_POST['system_phone'])) $arrFilter['system_phone'] = $_POST['system_phone'];
    if (isset($_POST['system_fax']) && !preg_match($pattern, $_POST['system_fax'])) $arrFilter['system_fax'] = $_POST['system_fax'];
    if (isset($_POST['system_mobile']) && !preg_match($pattern, $_POST['system_mobile'])) $arrFilter['system_mobile'] = $_POST['system_mobile'];
    if (isset($_POST['system_login']) && !preg_match($pattern, $_POST['system_login'])) $arrFilter['system_login'] = $_POST['system_login'];
    if (isset($_POST['system_email']) && !preg_match($pattern, $_POST['system_email'])) $arrFilter['system_email'] = $_POST['system_email'];
    if (isset($_POST['system_workspace']) && !preg_match($pattern, $_POST['system_workspace'])) $arrFilter['system_workspace'] = $_POST['system_workspace'];
    if (isset($_POST['system_user']) && !preg_match($pattern, $_POST['system_user'])) $arrFilter['system_user'] = $_POST['system_user'];
    if (isset($_POST['system_office']) && !preg_match($pattern, $_POST['system_office'])) $arrFilter['system_office'] = $_POST['system_office'];
    if (isset($_POST['system_comments']) && !preg_match($pattern, $_POST['system_comments'])) $arrFilter['system_comments'] = $_POST['system_comments'];
    if (isset($_POST['system_function']) && !preg_match($pattern, $_POST['system_function'])) $arrFilter['system_function'] = $_POST['system_function'];
    if (isset($_POST['system_number']) && !preg_match($pattern, $_POST['system_number'])) $arrFilter['system_number'] = $_POST['system_number'];
    if (isset($_POST['system_rank']) && !preg_match($pattern, $_POST['system_rank'])) $arrFilter['system_rank'] = $_POST['system_rank'];
    if (isset($_POST['system_building']) && !preg_match($pattern, $_POST['system_building'])) $arrFilter['system_building'] = $_POST['system_building'];
    if (isset($_POST['system_floor']) && !preg_match($pattern, $_POST['system_floor'])) $arrFilter['system_floor'] = $_POST['system_floor'];
    if (isset($_POST['system_country']) && !preg_match($pattern, $_POST['system_country'])) $arrFilter['system_country'] = $_POST['system_country'];
    if (isset($_POST['system_city']) && !preg_match($pattern, $_POST['system_city'])) $arrFilter['system_city'] = $_POST['system_city'];
    if (isset($_POST['system_postalcode']) && !preg_match($pattern, $_POST['system_postalcode'])) $arrFilter['system_postalcode'] = $_POST['system_postalcode'];

    // Affectation de valeurs par défaut si non défini
    if (!isset($arrFilter['system_lastname'])) $arrFilter['system_lastname'] = '';
    if (!isset($arrFilter['system_firstname'])) $arrFilter['system_firstname'] = '';
    if (!isset($arrFilter['system_entity'])) $arrFilter['system_entity'] = '';
    if (!isset($arrFilter['system_service'])) $arrFilter['system_service'] = '';
    if (!isset($arrFilter['system_service2'])) $arrFilter['system_service2'] = '';
    if (!isset($arrFilter['system_phone'])) $arrFilter['system_phone'] = '';
    if (!isset($arrFilter['system_fax'])) $arrFilter['system_fax'] = '';
    if (!isset($arrFilter['system_mobile'])) $arrFilter['system_mobile'] = '';
    if (!isset($arrFilter['system_login'])) $arrFilter['system_login'] = '';
    if (!isset($arrFilter['system_email'])) $arrFilter['system_email'] = '';
    if (!isset($arrFilter['system_workspace'])) $arrFilter['system_workspace'] = '';
    if (!isset($arrFilter['system_user'])) $arrFilter['system_user'] = '';
    if (!isset($arrFilter['system_office'])) $arrFilter['system_office'] = '';
    if (!isset($arrFilter['system_comments'])) $arrFilter['system_comments'] = '';
    if (!isset($arrFilter['system_function'])) $arrFilter['system_function'] = '';
    if (!isset($arrFilter['system_number'])) $arrFilter['system_number'] = '';
    if (!isset($arrFilter['system_rank'])) $arrFilter['system_rank'] = '';
    if (!isset($arrFilter['system_building'])) $arrFilter['system_building'] = '';
    if (!isset($arrFilter['system_floor'])) $arrFilter['system_floor'] = '';
    if (!isset($arrFilter['system_country'])) $arrFilter['system_country'] = '';
    if (!isset($arrFilter['system_city'])) $arrFilter['system_city'] = '';
    if (!isset($arrFilter['system_postalcode'])) $arrFilter['system_postalcode'] = '';

    // Enregistrement SESSION
    $_SESSION['system']['wce_search'] = $arrFilter;

    /**
     * Construction de l'url de validation du formulaire de recherche
     */

    $arrFormActionParams = array();

    $arrFormActionParams[] = "op=search";
    if (!empty($_REQUEST['headingid'])) $arrFormActionParams[] = "headingid={$_REQUEST['headingid']}";
    if (!empty($_REQUEST['articleid'])) $arrFormActionParams[] = "articleid={$_REQUEST['articleid']}";
    if (!empty($_REQUEST['webedit_mode'])) $arrFormActionParams[] = "webedit_mode={$_REQUEST['webedit_mode']}";

    $strFormActionParams = (!empty($arrFormActionParams)) ? 'index.php?'.implode('&',$arrFormActionParams) : 'index.php';

    $objTplDirectory->assign_vars(
        array(
            'SYSTEM_TROMBI_HEADINGID' => (empty($_REQUEST['headingid'])) ? '' : $_REQUEST['headingid'],
            'SYSTEM_TROMBI_ARTICLEID' => (empty($_REQUEST['articleid'])) ? '' : $_REQUEST['articleid'],
            'SYSTEM_TROMBI_FORMACTION' => ploopi_urlencode($strFormActionParams)
        )
    );



    // Construction de la liste des espaces de travail
    $arrWorkspace = array('list' => array(), 'tree' => array());

    $result = $db->query("SELECT id, label, id_workspace FROM ploopi_workspace WHERE system = 0 ORDER BY depth, label");

    // On les trie pour les afficher sous forme d'un arbre
    while ($fields = $db->fetchrow($result))
    {
        $arrWorkspace['list'][$fields['id']] = $fields;

        // astuce pour trouver le premier noeud
        if (empty($arrWorkspace['tree'])) $arrWorkspace['tree'][0][] = $fields['id'];
        else $arrWorkspace['tree'][$fields['id_workspace']][] = $fields['id'];
    }

    // Affectation de la liste des espaces de travail
    // (Attention, fonction anonyme recursive)
    $funcWorkspaces = function(&$arrWorkspace, &$objTplDirectory, $idsel = 0, $depth = 1)use(&$funcWorkspaces, $arrFilter)
    {
        if (isset($arrWorkspace['tree'][$idsel]))
        {
            foreach($arrWorkspace['tree'][$idsel] as $id)
            {
                $objTplDirectory->assign_block_vars('system_trombi_workspace',
                    array(
                        'ID' => $id,
                        'LABEL' => $arrWorkspace['list'][$id]['label'],
                        'DEPTH' => $depth,
                        'GAP' => $depth > 1 ? str_repeat('&nbsp;|&nbsp;', $depth-1).'&nbsp;' : '',
                        'SELECTED' => $id == $arrFilter['system_workspace'] ? 'selected="selected"' : ''
                    )
                );

                $funcWorkspaces($arrWorkspace, $objTplDirectory, $id, $depth+1);
            }
        }
    };

    $funcWorkspaces($arrWorkspace, $objTplDirectory);

    // Construction de la liste des noms
    $db->query("SELECT lastname, firstname FROM ploopi_user GROUP BY lastname, firstname ORDER BY lastname, firstname");
    while ($row = $db->fetchrow())
    {
        $strUserName = sprintf("%s %s", $row['lastname'], $row['firstname']);

        $objTplDirectory->assign_block_vars('system_trombi_user',
            array(
                'ID' => $strUserName,
                'LABEL' => ploopi_htmlentities(ucfirst($strUserName)),
                'SELECTED' => $strUserName == $arrFilter['system_user'] ? 'selected="selected"' : ''
            )
        );
    }

    // Construction de la liste des données brutes pour traitement via JS
    $rs = $db->query("SELECT service, service2, office, function, number, rank, building, floor, country, city, postalcode FROM ploopi_user");
    $objTplDirectory->assign_var('SYSTEM_TROMBI_JSDATA', json_encode(ploopi_array_map('ploopi_utf8encode', ploopi_array_map('ucfirst', $db->getarray()))));

    // Construction des autres listes génériques
    foreach(array('service', 'service2', 'login', 'email', 'office', 'function', 'number', 'rank', 'building', 'floor', 'country', 'city', 'postalcode') as $strField)
    {
        // Construction de la liste des services
        $db->query("SELECT `{$strField}` FROM ploopi_user WHERE `{$strField}` <> '' GROUP BY `{$strField}` ORDER BY `{$strField}`");
        while ($row = $db->fetchrow())
        {
            $objTplDirectory->assign_block_vars("system_trombi_{$strField}",
                array(
                    'ID' => $row[$strField],
                    'LABEL' => ploopi_htmlentities(ucfirst($row[$strField])),
                    'SELECTED' => $row[$strField] == $arrFilter["system_{$strField}"] ? 'selected="selected"' : ''
                )
            );
        }
    }


    switch($op)
    {
        case 'search':
            $objTplDirectory->assign_block_vars('system_trombi_switch_result', array());

            $objTplDirectory->assign_vars(
                array(
                    'SYSTEM_TROMBI_LASTNAME' => ploopi_htmlentities($arrFilter['system_lastname']),
                    'SYSTEM_TROMBI_FIRSTNAME' => ploopi_htmlentities($arrFilter['system_firstname']),
                    'SYSTEM_TROMBI_ENTITY' => ploopi_htmlentities($arrFilter['system_entity']),
                    'SYSTEM_TROMBI_SERVICE' => ploopi_htmlentities($arrFilter['system_service']),
                    'SYSTEM_TROMBI_SERVICE2' => ploopi_htmlentities($arrFilter['system_service2']),
                    'SYSTEM_TROMBI_PHONE' => ploopi_htmlentities($arrFilter['system_phone']),
                    'SYSTEM_TROMBI_FAX' => ploopi_htmlentities($arrFilter['system_fax']),
                    'SYSTEM_TROMBI_MOBILE' => ploopi_htmlentities($arrFilter['system_mobile']),
                    'SYSTEM_TROMBI_LOGIN' => ploopi_htmlentities($arrFilter['system_login']),
                    'SYSTEM_TROMBI_EMAIL' => ploopi_htmlentities($arrFilter['system_email']),
                    'SYSTEM_TROMBI_WORKSPACE' => $arrFilter['system_workspace'],
                    'SYSTEM_TROMBI_OFFICE' => ploopi_htmlentities($arrFilter['system_office']),
                    'SYSTEM_TROMBI_COMMENTS' => ploopi_htmlentities($arrFilter['system_comments']),
                    'SYSTEM_TROMBI_FUNCTION' => ploopi_htmlentities($arrFilter['system_function']),
                    'SYSTEM_TROMBI_NUMBER' => ploopi_htmlentities($arrFilter['system_number']),
                    'SYSTEM_TROMBI_RANK' => ploopi_htmlentities($arrFilter['system_rank']),
                    'SYSTEM_TROMBI_BUILDING' => ploopi_htmlentities($arrFilter['system_building']),
                    'SYSTEM_TROMBI_FLOOR' => ploopi_htmlentities($arrFilter['system_floor']),
                    'SYSTEM_TROMBI_COUNTRY' => ploopi_htmlentities($arrFilter['system_country']),
                    'SYSTEM_TROMBI_CITY' => ploopi_htmlentities($arrFilter['system_city']),
                    'SYSTEM_TROMBI_POSTALCODE' => ploopi_htmlentities($arrFilter['system_postalcode'])
                )
            );

            $objTplDirectory->assign_vars(
                array(
                    'SYSTEM_TROMBI_JS_LASTNAME' => addslashes($arrFilter['system_lastname']),
                    'SYSTEM_TROMBI_JS_FIRSTNAME' => addslashes($arrFilter['system_firstname']),
                    'SYSTEM_TROMBI_JS_ENTITY' => addslashes($arrFilter['system_entity']),
                    'SYSTEM_TROMBI_JS_SERVICE' => addslashes($arrFilter['system_service']),
                    'SYSTEM_TROMBI_JS_SERVICE2' => addslashes($arrFilter['system_service2']),
                    'SYSTEM_TROMBI_JS_PHONE' => addslashes($arrFilter['system_phone']),
                    'SYSTEM_TROMBI_JS_FAX' => addslashes($arrFilter['system_fax']),
                    'SYSTEM_TROMBI_JS_MOBILE' => addslashes($arrFilter['system_mobile']),
                    'SYSTEM_TROMBI_JS_LOGIN' => addslashes($arrFilter['system_login']),
                    'SYSTEM_TROMBI_JS_EMAIL' => addslashes($arrFilter['system_email']),
                    'SYSTEM_TROMBI_JS_WORKSPACE' => addslashes($arrFilter['system_workspace']),
                    'SYSTEM_TROMBI_JS_OFFICE' => addslashes($arrFilter['system_office']),
                    'SYSTEM_TROMBI_JS_COMMENTS' => addslashes($arrFilter['system_comments']),
                    'SYSTEM_TROMBI_JS_FUNCTION' => addslashes($arrFilter['system_function']),
                    'SYSTEM_TROMBI_JS_NUMBER' => addslashes($arrFilter['system_number']),
                    'SYSTEM_TROMBI_JS_RANK' => addslashes($arrFilter['system_rank']),
                    'SYSTEM_TROMBI_JS_BUILDING' => addslashes($arrFilter['system_building']),
                    'SYSTEM_TROMBI_JS_FLOOR' => addslashes($arrFilter['system_floor']),
                    'SYSTEM_TROMBI_JS_COUNTRY' => addslashes($arrFilter['system_country']),
                    'SYSTEM_TROMBI_JS_CITY' => addslashes($arrFilter['system_city']),
                    'SYSTEM_TROMBI_JS_POSTALCODE' => addslashes($arrFilter['system_postalcode'])
                )
            );

            // Construction de la requête de recherche
            $arrWhere = array();
            $arrWhere[] = '1';

            if (!empty($arrFilter['system_lastname'])) $arrWhere[] = "u.lastname LIKE '".$db->addslashes($arrFilter['system_lastname'])."%'";
            if (!empty($arrFilter['system_firstname'])) $arrWhere[] = "u.firstname LIKE '".$db->addslashes($arrFilter['system_firstname'])."%'";
            if (!empty($arrFilter['system_user'])) $arrWhere[] = "CONCAT(u.lastname, ' ', u.firstname) = '".$db->addslashes($arrFilter['system_user'])."'";
            if (!empty($arrFilter['system_service'])) $arrWhere[] = "u.service LIKE '".$db->addslashes($arrFilter['system_service'])."%'";
            if (!empty($arrFilter['system_service2'])) $arrWhere[] = "u.service2 LIKE '".$db->addslashes($arrFilter['system_service2'])."%'";
            if (!empty($arrFilter['system_phone'])) $arrWhere[] = "u.phone LIKE '".$db->addslashes($arrFilter['system_phone'])."%'";
            if (!empty($arrFilter['system_fax'])) $arrWhere[] = "u.fax LIKE '".$db->addslashes($arrFilter['system_fax'])."%'";
            if (!empty($arrFilter['system_mobile'])) $arrWhere[] = "u.mobile LIKE '".$db->addslashes($arrFilter['system_mobile'])."%'";
            if (!empty($arrFilter['system_login'])) $arrWhere[] = "u.login LIKE '".$db->addslashes($arrFilter['system_login'])."%'";
            if (!empty($arrFilter['system_email'])) $arrWhere[] = "u.email LIKE '%".$db->addslashes($arrFilter['system_email'])."%'";
            if (!empty($arrFilter['system_office'])) $arrWhere[] = "u.office LIKE '".$db->addslashes($arrFilter['system_office'])."%'";
            if (!empty($arrFilter['system_comments'])) $arrWhere[] = "u.comments LIKE '%".$db->addslashes($arrFilter['system_comments'])."%'";
            if (!empty($arrFilter['system_function'])) $arrWhere[] = "u.function LIKE '".$db->addslashes($arrFilter['system_function'])."%'";
            if (!empty($arrFilter['system_number'])) $arrWhere[] = "u.number LIKE '".$db->addslashes($arrFilter['system_number'])."%'";
            if (!empty($arrFilter['system_rank'])) $arrWhere[] = "u.rank LIKE '".$db->addslashes($arrFilter['system_rank'])."%'";
            if (!empty($arrFilter['system_building'])) $arrWhere[] = "u.building LIKE '".$db->addslashes($arrFilter['system_building'])."%'";
            if (!empty($arrFilter['system_floor'])) $arrWhere[] = "u.floor LIKE '".$db->addslashes($arrFilter['system_floor'])."%'";
            if (!empty($arrFilter['system_country'])) $arrWhere[] = "u.country LIKE '".$db->addslashes($arrFilter['system_country'])."%'";
            if (!empty($arrFilter['system_city'])) $arrWhere[] = "u.city LIKE '".$db->addslashes($arrFilter['system_city'])."%'";
            if (!empty($arrFilter['system_postalcode'])) $arrWhere[] = "u.postalcode LIKE '".$db->addslashes($arrFilter['system_postalcode'])."%'";

            // Exécution de la requête principale permettant de lister les utilisateurs selon le filtre
            $ptrRs = $db->query("
                SELECT      u.id,
                            u.lastname,
                            u.firstname,
                            u.login,
                            u.email,
                            u.phone,
                            u.fax,
                            u.mobile,
                            u.service,
                            u.service2,
                            u.function,
                            u.rank,
                            u.number,
                            u.address,
                            u.postalcode,
                            u.city,
                            u.country,
                            u.building,
                            u.floor,
                            u.office,
                            u.comments,
                            g.id as groupid,
                            g.label

                FROM        ploopi_user u

                LEFT JOIN   ploopi_group_user gu
                ON          gu.id_user = u.id

                LEFT JOIN   ploopi_group g
                ON          g.id = gu.id_group

                WHERE       ".implode(' AND ', $arrWhere)."

                ORDER BY    u.lastname, u.firstname
            ");

            // Tableaux qui vont contenir les utilisateurs et les groupes
            $arrUser = array();
            $arrGroup = array();

            while ($row = $db->fetchrow($ptrRs))
            {
                if (!isset($arrUser[$row['id']]))
                {
                    $arrUser[$row['id']] = $row;
                    $arrUser[$row['id']]['groups'] = array();
                }

                // groupe lié
                if (!empty($row['groupid']))
                {
                    $arrUser[$row['id']]['groups'][$row['groupid']] = $row['label'];
                    $arrGroup[$row['groupid']] = $row['label'];
                }
            }

            // liste des groupes trouvés
            $strGroupList = implode(',', array_keys($arrGroup));

            // liste des utilisateurs trouvés
            $strUserList = implode(',', array_keys($arrUser));

            // tableau contenant les rôles pour les utilisateurs/groupes trouvés
            $arrRoles = array('groups' => array(), 'users' => array());

            if (!empty($strUserList))
            {
                if (!empty($strGroupList))
                {
                    // recherche des rôles "groupe"
                    $db->query("
                        SELECT      wgr.id_group,
                                    wgr.id_workspace,
                                    r.id,
                                    r.id_module,
                                    r.label as role_label,
                                    m.label as module_label

                        FROM        ploopi_role r,
                                    ploopi_workspace_group_role wgr,
                                    ploopi_module m

                        WHERE       wgr.id_role = r.id
                        AND         r.id_module = m.id
                        AND         wgr.id_group IN ({$strGroupList})
                    ");

                    while ($row = $db->fetchrow()) $arrRoles['groups'][$row['id_workspace']][$row['id_group']][$row['id']] = $row;
                }

                // recherche des rôles "utilisateur"
                $db->query("
                    SELECT      wur.id_user,
                                wur.id_workspace,
                                r.id,
                                r.id_module,
                                r.label as role_label,
                                m.label as module_label

                    FROM        ploopi_role r,
                                ploopi_workspace_user_role wur,
                                ploopi_module m

                    WHERE       wur.id_role = r.id
                    AND         r.id_module = m.id
                    AND         wur.id_user IN ({$strUserList})
                ");
            }
            while ($row = $db->fetchrow()) $arrRoles['users'][$row['id_workspace']][$row['id_user']][$row['id']] = $row;

            foreach ($arrUser as $row)
            {
                $objUser = new user();
                $objUser->fields['id'] = $row['id'];

                // récupération et tri des espaces de travail de l'utilisateur
                $arrUser[$row['id']]['workspaces'] = $objUser->getworkspaces(true);

                if (!empty($arrFilter['system_workspace']) && !in_array($arrFilter['system_workspace'], array_keys($arrUser[$row['id']]['workspaces'])))
                {
                    // Suppression des utilisateurs n'appartenant pas à l'espace de travail
                    unset($arrUser[$row['id']]);
                }
                else
                {
                    // tri des groupes par nom
                    asort($arrUser[$row['id']]['groups']);

                    // tri des espaces par nom
                    asort($arrUser[$row['id']]['workspaces']);

                    if (file_exists($objUser->getphotopath()))
                    {
                        $arrUser[$row['id']]['photopath'] = ploopi_urlencode("admin-light.php?ploopi_op=ploopi_get_userphoto&ploopi_user_id={$row['id']}");
                    }
                    else $arrUser[$row['id']]['photopath'] = './img/blank.gif';

                    $arrUser[$row['id']]['roles'] = array();

                    // tableau qui va contenir les rôles dont dispose l'utilisateur dans l'espace courant
                    $arrUserWspRoles = array();

                    foreach($arrUser[$row['id']]['workspaces'] as $intIdWsp => $lbl)
                    {
                        if (isset($arrRoles['groups'][$intIdWsp]))
                        {
                            foreach($arrRoles['groups'][$intIdWsp] as $intIdGrp => $arrDetail)
                            {
                                // L'utilisateur appartient au groupe (donc il a les rôles)
                                if (in_array($intIdGrp, array_keys($row['groups'])))
                                {
                                    foreach($arrDetail as $intIdRole => $arrR)
                                        $arrUser[$row['id']]['roles'][$intIdRole] =
                                            sprintf("%s de %s",
                                                ploopi_htmlentities($arrR['role_label']),
                                                ploopi_htmlentities($arrR['module_label'])
                                            );
                                }
                            }
                        }

                        if (isset($arrRoles['users'][$intIdWsp][$row['id']]))
                        {
                            foreach($arrRoles['users'][$intIdWsp][$row['id']] as $intIdRole => $arrR)
                                $arrUser[$row['id']]['roles'][$intIdRole] =
                                    sprintf("%s de %s",
                                            ploopi_htmlentities($arrR['role_label']),
                                            ploopi_htmlentities($arrR['module_label'])
                                        );
                        }
                    }
                }
            }

            // Aucune réponse
            if (empty($arrUser))
            {
                $objTplDirectory->assign_block_vars('system_trombi_switch_result.switch_message',
                    array(
                        'CONTENT' => "Il n'y a aucune réponse pour votre recherche"
                    )
                );
            }
            else
            {
                // true si l'index est actif (si trop de réponses)
                $booIndex = sizeof($arrUser) > $intMaxLines;

                // Affichage d'un index
                if ($booIndex)
                {
                    $arrIndex = array();
                    foreach ($arrUser as $row)
                    {
                        $strIndex = strtoupper(substr($row['lastname'], 0, 1));
                        if (!isset($arrIndex[$strIndex])) $arrIndex[$strIndex] = 0;
                        $arrIndex[$strIndex]++;
                    }

                    // Lecture de l'index sélectionné ou choix par défaut du 1er élément
                    $strIndexSel = isset($_GET['idx']) && isset($arrIndex[$_GET['idx']]) ? $_GET['idx'] : key($arrIndex);

                    $arrUrlParams = array();

                    $arrUrlParams[] = "op=search";
                    if (!empty($_REQUEST['headingid'])) $arrUrlParams[] = "headingid={$_REQUEST['headingid']}";
                    if (!empty($_REQUEST['articleid'])) $arrUrlParams[] = "articleid={$_REQUEST['articleid']}";
                    if (!empty($_REQUEST['webedit_mode'])) $arrUrlParams[] = "webedit_mode={$_REQUEST['webedit_mode']}";

                    $objTplDirectory->assign_block_vars('system_trombi_switch_result.switch_index', array());

                    foreach($arrIndex as $strIndex => $intCount)
                    {
                        $objTplDirectory->assign_block_vars('system_trombi_switch_result.switch_index.index', array(
                            'LETTER' => $strIndex,
                            'COUNT' => $intCount,
                            'SELECTED' => $strIndexSel == $strIndex ? 'selected' : '',
                            'URL' => ploopi_urlencode('index.php?'.implode('&',$arrUrlParams+array('' => "idx={$strIndex}"))),
                        ));
                    }
                }

                foreach ($arrUser as $row)
                {
                    if (!$booIndex || strtoupper(substr($row['lastname'], 0, 1)) == $strIndexSel)
                    {
                        // Indices de tri pour le tableau
                        $strSortLabelGroups = implode(',', $row['groups']);
                        $strSortLabelWorkspaces = '';

                        $arrAddress = array();

                        if (!empty($row['address'])) $arrAddress[] = ploopi_nl2br(ploopi_htmlentities($row['address']));
                        if (!empty($row['postalcode']) || !empty($row['city'])) $arrAddress[] = ploopi_nl2br(ploopi_htmlentities(trim($row['postalcode'].' '.$row['city'])));
                        if (!empty($row['country'])) $arrAddress[] = ploopi_nl2br(ploopi_htmlentities($row['country']));

                        $objTplDirectory->assign_block_vars('system_trombi_switch_result.user',
                            array(
                                'ID' => $row['id'],
                                'LASTNAME' => ploopi_htmlentities($row['lastname']),
                                'FIRSTNAME' => ploopi_htmlentities($row['firstname']),
                                'LOGIN' => ploopi_htmlentities($row['login']),
                                'EMAIL' => ploopi_htmlentities($row['email']),
                                'PHONE' => ploopi_htmlentities($row['phone']),
                                'FAX' => ploopi_htmlentities($row['fax']),
                                'MOBILE' => ploopi_htmlentities($row['mobile']),
                                'SERVICE' => ploopi_htmlentities($row['service']),
                                'SERVICE2' => ploopi_htmlentities($row['service2']),
                                'WORKSPACES' => implode('<br />', $row['workspaces']),
                                'GROUPS' => implode('<br />', $row['groups']),
                                'ROLES' => implode('<br />', $row['roles']),
                                'FUNCTION' => ploopi_htmlentities($row['function']),
                                'RANK' => ploopi_htmlentities($row['rank']),
                                'NUMBER' => ploopi_htmlentities($row['number']),
                                'POSTALCODE' => ploopi_htmlentities($row['postalcode']),
                                'ADDRESS' => ploopi_htmlentities($row['address']),
                                'CITY' => ploopi_htmlentities($row['city']),
                                'COUNTRY' => ploopi_htmlentities($row['country']),
                                'ADDRESS_FULL' => implode('<br />', $arrAddress),
                                'BUILDING' => ploopi_htmlentities($row['building']),
                                'FLOOR' => ploopi_htmlentities($row['floor']),
                                'OFFICE' => ploopi_htmlentities($row['office']),
                                'COMMENTS' => ploopi_nl2br(ploopi_htmlentities($row['comments'])),
                                'PHOTOPATH' => $row['photopath']
                            )
                        );

                        include_once './include/classes/documents.php';

                        // Lecture du dossier racine de la mini ged associée à l'utilisateur
                        $objRootFolder = documentsfolder::getroot(
                            _SYSTEM_OBJECT_USER,
                            $row['id'],
                            $obj['module_id']
                        );

                        if (!empty($objRootFolder))
                        {
                            $objTplDirectory->assign_block_vars('system_trombi_switch_result.user.switch_files', array());

                            $arrFiles = $objRootFolder->getlist();

                            foreach($arrFiles as $intIdFile => $rowFile)
                            {
                                // Découpage du chemin pour modifier le fichier
                                $arrPath = explode('/', $rowFile['path']);
                                $strFileName = $arrPath[sizeof($arrPath)-1];
                                array_pop($arrPath);

                                $objTplDirectory->assign_block_vars('system_trombi_switch_result.user.switch_files.file', array(
                                    'FILENAME' => $strFileName,
                                    'PATH' => implode(' &raquo; ', $arrPath),
                                    'URL' => $rowFile['file']->geturl()
                                ));
                            }
                        }
                    }
                }
            }

        break;
    }

    $objTplDirectory->pparse('system_trombi');
}
else echo "Fichier ./templates/frontoffice/{$template_name}/system_trombi.tpl manquant";
?>
