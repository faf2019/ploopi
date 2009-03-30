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
 * Gestion des objets ins�rables dans une page de contenu (WebEdit)
 *
 * @package system
 * @subpackage wce
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane Escaich
 */

global $articleid;
global $headingid;
global $template_name;

$op = empty($_REQUEST['op']) ? '' : $_REQUEST['op'];

/**
 * Initialisation du moteur de template.
 * Chargement du template.
 */

$objTplDirectory = new Template("./templates/frontoffice/{$template_name}");

if (file_exists("./templates/frontoffice/{$template_name}/system_trombi.tpl"))
{
    $objTplDirectory->set_filenames(array('system_trombi' => 'system_trombi.tpl'));

    // R�cup�ration des param�tres
    global $arrFilter;
    $arrFilter = array();

    // On ne veut pas les caract�res % et | dans la recherche avec LIKE
    $pattern = '%|_';

    // Lecture SESSION
    if (isset($_SESSION['system']['wce_search'])) $arrFilter = $_SESSION['system']['wce_search'];

    // Lecture Params
    if (isset($_POST['system_lastname']) && !ereg($pattern, $_POST['system_lastname'])) $arrFilter['system_lastname'] = $_POST['system_lastname'];
    if (isset($_POST['system_firstname']) && !ereg($pattern, $_POST['system_firstname'])) $arrFilter['system_firstname'] = $_POST['system_firstname'];
    if (isset($_POST['system_service']) && !ereg($pattern, $_POST['system_service'])) $arrFilter['system_service'] = $_POST['system_service'];
    if (isset($_POST['system_phone']) && !ereg($pattern, $_POST['system_phone'])) $arrFilter['system_phone'] = $_POST['system_phone'];
    if (isset($_POST['system_login']) && !ereg($pattern, $_POST['system_login'])) $arrFilter['system_login'] = $_POST['system_login'];
    if (isset($_POST['system_email']) && !ereg($pattern, $_POST['system_email'])) $arrFilter['system_email'] = $_POST['system_email'];
    if (isset($_POST['system_workspace']) && !ereg($pattern, $_POST['system_workspace'])) $arrFilter['system_workspace'] = $_POST['system_workspace'];

    // Affectation de valeurs par d�faut si non d�fini
    if (!isset($arrFilter['system_lastname'])) $arrFilter['system_lastname'] = '';
    if (!isset($arrFilter['system_firstname'])) $arrFilter['system_firstname'] = '';
    if (!isset($arrFilter['system_service'])) $arrFilter['system_service'] = '';
    if (!isset($arrFilter['system_phone'])) $arrFilter['system_phone'] = '';
    if (!isset($arrFilter['system_login'])) $arrFilter['system_login'] = '';
    if (!isset($arrFilter['system_email'])) $arrFilter['system_email'] = '';
    if (!isset($arrFilter['system_workspace'])) $arrFilter['system_workspace'] = '';

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
            'SYSTEM_TROMBI_FORMACTION' => $strFormActionParams
        )
    );

    // Construction de la liste des espace de travail
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

    function system_wce_buildworkspacetree($arrWorkspace, $objTplDirectory, $idsel = 0, $depth = 1)
    {
        global $arrFilter;

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

                system_wce_buildworkspacetree(&$arrWorkspace, &$objTplDirectory, $id, $depth+1);
            }
        }
    }

    // Affectation de la liste des espaces de travail
    system_wce_buildworkspacetree(&$arrWorkspace, &$objTplDirectory);

    switch($op)
    {
        case 'search':
            $objTplDirectory->assign_block_vars('system_trombi_switch_result', array());

            $objTplDirectory->assign_vars(
                array(
                    'SYSTEM_TROMBI_LASTNAME' => htmlentities($arrFilter['system_lastname']),
                    'SYSTEM_TROMBI_FIRSTNAME' => htmlentities($arrFilter['system_firstname']),
                    'SYSTEM_TROMBI_SERVICE' => htmlentities($arrFilter['system_service']),
                    'SYSTEM_TROMBI_PHONE' => htmlentities($arrFilter['system_phone']),
                    'SYSTEM_TROMBI_LOGIN' => htmlentities($arrFilter['system_login']),
                    'SYSTEM_TROMBI_EMAIL' => htmlentities($arrFilter['system_email']),
                    'SYSTEM_TROMBI_WORKSPACE' => $arrFilter['system_workspace']
                )
            );

            // Construction de la requ�te de recherche
            $arrWhere = array();
            $arrWhere[] = '1';

            if (!empty($arrFilter['system_lastname'])) $arrWhere[] = "u.lastname LIKE '".$db->addslashes($arrFilter['system_lastname'])."%'";
            if (!empty($arrFilter['system_firstname'])) $arrWhere[] = "u.firstname LIKE '".$db->addslashes($arrFilter['system_firstname'])."%'";
            if (!empty($arrFilter['system_service'])) $arrWhere[] = "u.service LIKE '".$db->addslashes($arrFilter['system_service'])."%'";
            if (!empty($arrFilter['system_phone'])) $arrWhere[] = "u.phone LIKE '".$db->addslashes($arrFilter['system_phone'])."%'";
            if (!empty($arrFilter['system_login'])) $arrWhere[] = "u.login LIKE '".$db->addslashes($arrFilter['system_login'])."%'";
            if (!empty($arrFilter['system_email'])) $arrWhere[] = "u.email LIKE '".$db->addslashes($arrFilter['system_email'])."%'";

            // Ex�cution de la requ�te principale permettant de lister les utilisateurs selon le filtre
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
                            g.id as groupid,
                            g.label

                FROM        ploopi_user u

                LEFT JOIN   ploopi_group_user gu
                ON          gu.id_user = u.id

                LEFT JOIN   ploopi_group g
                ON          g.id = gu.id_group

                WHERE       ".implode(' AND ', $arrWhere)."
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

                // groupe li�
                if (!empty($row['groupid']))
                {
                    $arrUser[$row['id']]['groups'][$row['groupid']] = $row['label'];
                    $arrGroup[$row['groupid']] = $row['label'];
                }
            }

            // liste des groupes trouv�s
            $strGroupList = implode(',', array_keys($arrGroup));

            // liste des utilisateurs trouv�s
            $strUserList = implode(',', array_keys($arrUser));

            // tableau contenant les r�les pour les utilisateurs/groupes trouv�s
            $arrRoles = array('groups' => array(), 'users' => array());

            if (!empty($strUserList))
            {
                if (!empty($strGroupList))
                {
                    // recherche des r�les "groupe"
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

                // recherche des r�les "utilisateur"
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

                // r�cup�ration et tri des espaces de travail de l'utilisateur
                $arrUser[$row['id']]['workspaces'] = $objUser->getworkspaces(true);

                if (!empty($arrFilter['system_workspace']) && !in_array($arrFilter['system_workspace'], array_keys($arrUser[$row['id']]['workspaces'])))
                {
                    // Suppression des utilisateurs n'appartenant pas � l'espace de travail
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

                    // tableau qui va contenir les r�les dont dispose l'utilisateur dans l'espace courant
                    $arrUserWspRoles = array();

                    foreach($arrUser[$row['id']]['workspaces'] as $intIdWsp => $lbl)
                    {
                        if (isset($arrRoles['groups'][$intIdWsp]))
                        {
                            foreach($arrRoles['groups'][$intIdWsp] as $intIdGrp => $arrDetail)
                            {
                                // L'utilisateur appartient au groupe (donc il a les r�les)
                                if (in_array($intIdGrp, array_keys($row['groups'])))
                                {
                                    foreach($arrDetail as $intIdRole => $arrR)
                                        $arrUser[$row['id']]['roles'][$intIdRole] =
                                            sprintf("%s de %s",
                                                htmlentities($arrR['role_label']),
                                                htmlentities($arrR['module_label'])
                                            );
                                }
                            }
                        }

                        if (isset($arrRoles['users'][$intIdWsp][$row['id']]))
                        {
                            foreach($arrRoles['users'][$intIdWsp][$row['id']] as $intIdRole => $arrR)
                                $arrUser[$row['id']]['roles'][$intIdRole] =
                                    sprintf("%s de %s",
                                            htmlentities($arrR['role_label']),
                                            htmlentities($arrR['module_label'])
                                        );
                        }
                    }
                }
            }

            // Aucune r�ponse
            if (empty($arrUser))
            {
                $objTplDirectory->assign_block_vars('system_trombi_switch_result.switch_message',
                    array(
                        'CONTENT' => "Il n'y a aucune r�ponse pour votre recherche"
                    )
                );
            }
            else
            {
                foreach ($arrUser as $row)
                {
                    // Indices de tri pour le tableau
                    $strSortLabelGroups = implode(',', $row['groups']);
                    $strSortLabelWorkspaces = '';

                    $arrAddress = array();

                    if (!empty($row['address'])) $arrAddress[] = ploopi_nl2br(htmlentities($row['address']));
                    if (!empty($row['postalcode']) || !empty($row['city'])) $arrAddress[] = ploopi_nl2br(htmlentities(trim($row['postalcode'].' '.$row['city'])));
                    if (!empty($row['country'])) $arrAddress[] = ploopi_nl2br(htmlentities($row['country']));

                    $objTplDirectory->assign_block_vars('system_trombi_switch_result.user',
                        array(
                            'ID' => $row['id'],
                            'LASTNAME' => htmlentities($row['lastname']),
                            'FIRSTNAME' => htmlentities($row['firstname']),
                            'LOGIN' => htmlentities($row['login']),
                            'EMAIL' => htmlentities($row['email']),
                            'PHONE' => htmlentities($row['phone']),
                            'FAX' => htmlentities($row['fax']),
                            'MOBILE' => htmlentities($row['mobile']),
                            'SERVICE' => htmlentities($row['service']),
                            'WORKSPACES' => implode('<br />', $row['workspaces']),
                            'GROUPS' => implode('<br />', $row['groups']),
                            'ROLES' => implode('<br />', $row['roles']),
                            'FUNCTION' => htmlentities($row['function']),
                            'RANK' => htmlentities($row['rank']),
                            'NUMBER' => htmlentities($row['number']),
                            'POSTALCODE' => htmlentities($row['postalcode']),
                            'ADDRESS' => htmlentities($row['address']),
                            'CITY' => htmlentities($row['city']),
                            'COUNTRY' => htmlentities($row['country']),
                            'ADDRESS_FULL' => implode('<br />', $arrAddress),
                            'BUILDING' => htmlentities($row['building']),
                            'FLOOR' => htmlentities($row['floor']),
                            'OFFICE' => htmlentities($row['office']),
                            'PHOTOPATH' => $row['photopath']
                        )
                    );

                }
            }

        break;
    }

    $objTplDirectory->pparse('system_trombi');
}
else echo "Fichier ./templates/frontoffice/{$template_name}/system_trombi.tpl manquant";
?>