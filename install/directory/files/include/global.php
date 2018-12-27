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
 * Fonctions, constantes, variables globales
 *
 * @package directory
 * @subpackage global
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Définition des constantes
 */

define ('_DIRECTORY_MANAGE_GROUP',  1);
define ('_DIRECTORY_MANAGE_COMMON', 2);

define ('_DIRECTORY_ACTION_CONTACTS',         1);
define ('_DIRECTORY_ACTION_MANAGERS',         2);
define ('_DIRECTORY_ACTION_SPEEDDIALING',     3);

define ('_DIRECTORY_ACTION_HEADING_ADD',      10);
define ('_DIRECTORY_ACTION_HEADING_DELETE',   11);
define ('_DIRECTORY_ACTION_HEADING_MODIFY',   12);

define ('_DIRECTORY_ACTION_CONTACT_ADD',      15);
define ('_DIRECTORY_ACTION_CONTACT_DELETE',   16);
define ('_DIRECTORY_ACTION_CONTACT_MODIFY',   17);

define ('_DIRECTORY_ACTION_SPEEDDIALING_ADD',      20);
define ('_DIRECTORY_ACTION_SPEEDDIALING_DELETE',   21);
define ('_DIRECTORY_ACTION_SPEEDDIALING_MODIFY',   22);


define ('_DIRECTORY_OBJECT_HEADING',        1);
define ('_DIRECTORY_OBJECT_CONTACT',        2);

global $arrDirectoryImportFields;
$arrDirectoryImportFields = array(
    'lastname'      =>  "Nom",
    'firstname'     =>  "Prénom",
    'civility'     =>   "Civilité",

    'service'       =>  "Service",
    'function'      =>  "Fonction",
    'rank'          =>  "Grade/Niveau",
    'number'        =>  "Numéro de Poste",
    'phone'         =>  "Numéro de Téléphone",
    'mobile'        =>  "Numéro de Portable",
    'fax'           =>  "Numéro de Fax",
    'email'         =>  "Adresse mèl",

    'building'      =>  "Bâtiment",
    'floor'         =>  "Etage",
    'office'        =>  "Bureau",
    'address'       =>  "Adresse",
    'postalcode'    =>  "Code Postal",
    'city'          =>  "Ville",
    'country'       =>  "Pays",

    'comments'      =>  "Commentaires"
);

/**
 * Retourne l'ensemble des rubriques dans un tableau
 *
 * @return array tableau contenant les rubriques
 */

function directory_getheadings($intIdHeading = 0)
{
    $db = ploopi\db::get();

    $booIsAdmin = ploopi\acl::isadmin();
    if (!$booIsAdmin)
    {
        // Lecture du profil utilisateur (groupes notamment)
        $objUser = new ploopi\user();
        $arrGroups = $objUser->open($_SESSION['ploopi']['userid']) ? $objUser->getgroups(true) : array();

        // Tous les validateurs pour toutes les rubriques !
        $arrVal = ploopi\validation::get(_DIRECTORY_OBJECT_HEADING);

        // On trie par rubrique
        // $arrValidation contient un enregistrement par rubrique, si false alors il y a des validateurs mais l'utilisateur courant n'en fait pas partie, si true alors l'utilisateur courant est validateur
        foreach($arrVal as $row)
        {
            if (!isset($arrValidation[$row['id_record']])) $arrValidation[$row['id_record']] = false;
            if (($row['type_validation'] == 'user' && $row['id_validation'] == $_SESSION['ploopi']['userid']) || ($row['type_validation'] == 'group' && isset($arrGroups[$row['id_validation']]))) $arrValidation[$row['id_record']] = true;
        }
    }

    $arrHeadings =
        array(
            'list' => array(),
            'tree' => array()
        );

    $result = $db->query("
        SELECT      *

        FROM        ploopi_mod_directory_heading

        ORDER BY    id_heading,
                    position
    ");

    while ($fields = $db->fetchrow($result))
    {

        if ($intIdHeading == 0 || $fields['id'] == $intIdHeading || $fields['id_heading'] == $intIdHeading || (isset($arrHeadings['list'][$fields['id_heading']]) && in_array($intIdHeading, explode(';', $arrHeadings['list'][$fields['id_heading']]['parents']))))
        {
            // Validateur ? oui si "admin sys" ou "validateur" ou "validateur de la rubrique parent"
            $fields['isvalidator'] = $booIsAdmin || !empty($arrValidation[$fields['id']]) || (!isset($arrValidation[$fields['id']]) && !empty($arrHeadings['list'][$fields['id_heading']]['isvalidator']));
            $fields['parents'] = (isset($arrHeadings['list'][$fields['id_heading']])) ? "{$arrHeadings['list'][$fields['id_heading']]['parents']};{$fields['id_heading']}" : $fields['id_heading'];

            $arrHeadings['list'][$fields['id']] = $fields;
            $arrHeadings['tree'][$fields['id_heading']][] = $fields['id'];
        }
    }

    return $arrHeadings;
}

/**
 * Retourne l'ensemble des contacts partagés triés par rubrique dans un tableau
 *
 * @return array tableau contenant les contacts
 */

function directory_getcontacts()
{
    $db = ploopi\db::get();

    $arrContacts = array();

    $result = $db->query("
        SELECT      *
        FROM        ploopi_mod_directory_contact
        WHERE       id_heading > 0
        ORDER BY    position, lastname, firstname
    ");

    while ($fields = $db->fetchrow($result)) $arrContacts[$fields['id_heading']][] = $fields;

    return($arrContacts);
}

/**
 * Retourne l'arbre des rubriques pour la méthode skin::display_treeview()
 *
 * @param array $rubriques les rubriques
 * @return array treeview
 *
 * @see risques_getrubriques
 * @see skin::display_treeview
 */

function directory_gettreeview($headings = array(), $booPopup = false)
{
    $db = ploopi\db::get();

    $treeview =
        array(
            'list' => array(),
            'tree' => array()
        );

    foreach($headings['list'] as $id => $fields)
    {
        $arrParents = preg_split('/;/', $fields['parents']);
        $icon = 'ico_heading.png';

        if ($booPopup)
        {
            $strNodePrefix = 'pop_';
            $strNodeId = $strNodePrefix.$fields['id'];

            foreach($arrParents as &$strNodeParentId) $strNodeParentId = $strNodePrefix.$strNodeParentId;

            $strNodeOnClick = "ploopi_skin_treeview_shownode('{$strNodeId}', '".ploopi\crypt::queryencode("ploopi_op=directory_heading_detail&directory_heading_id={$fields['id']}&directory_option=popup")."', 'admin-light.php');";

            $strLink = 'javascript:void(0);';
            $strOnClick = $fields['isvalidator'] ? 'javascript:directory_heading_choose(\''.$fields['id'].'\', \''.addslashes($fields['label']).'\');' : "javascript:alert('Vous ne disposez pas des autorisations nécessaires');";

            if (!$fields['isvalidator']) $icon = 'ico_heading_false.png';
        }
        else
        {
            $strNodePrefix = '';
            $strNodeId = $fields['id'];

            $strNodeOnClick = "ploopi_skin_treeview_shownode('{$strNodeId}', '".ploopi\crypt::queryencode("ploopi_op=directory_heading_detail&directory_heading_id={$fields['id']}")."', 'admin-light.php');";

            $strLink = ploopi\crypt::urlencode("admin.php?directory_heading_id={$fields['id']}");
            $strOnClick = '';
        }

        $treeview['list'][$strNodeId] =
            array(
                'id' => $strNodeId,
                'label' => $fields['label'],
                'description' => $fields['description'],
                'parents' => $arrParents,
                'node_link' => '',
                'node_onclick' => $strNodeOnClick,
                'link' => $strLink,
                'onclick' => $strOnClick,
                'icon' => "./modules/directory/img/{$icon}"
            );

        $treeview['tree'][$strNodePrefix.$fields['id_heading']][] = $strNodeId;
    }

    return($treeview);
}

/**
 * Affichage frontoffice de l'annuaire complet (intégration template)
 *
 * @param object $template_body template
 * @param array $arrHeadings tableau des rubriques
 * @param array $arrContacts tableau des contacts
 * @param int $intHeadingId rubrique à afficher
 */

function directory_template_display(&$template_body, &$arrHeadings, &$arrContacts, $intHeadingId = 0)
{
    global $template_moduleid;
    global $headingid;

    // Gestion des contacts de la rubrique
    if (isset($arrContacts[$intHeadingId]))
    {
        $c = 0;
        foreach($arrContacts[$intHeadingId] as $row)
        {
            $c++;

            $template_body->assign_block_vars('directory_switch_full.line', array());

            $arrAddress = array();
            if (!empty($row['address'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities($row['address']));
            if (!empty($row['postalcode']) || !empty($row['city'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities(trim($row['postalcode'].' '.$row['city'])));
            if (!empty($row['country'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities($row['country']));

            $objContact = new directory_contact();
            $objContact->fields['id'] = $row['id'];

            if (file_exists($objContact->getphotopath())) $row['photopath'] = ploopi\crypt::urlencode("index-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$row['id']}");
            else $row['photopath'] = './modules/directory/img/nopic.gif';

            // Récupération des rubriques du contact
            $arrContactHeadings = array();

            foreach(preg_split('/;/', $arrHeadings['list'][$intHeadingId]['parents']) as $intHid)
            {
                if (isset($arrHeadings['list'][$intHid])) $arrContactHeadings[] = $arrHeadings['list'][$intHid]['label'];
            }

            $arrContactHeadings[] = $arrHeadings['list'][$intHeadingId]['label'];
            $strContactHeadings = implode(' > ', $arrContactHeadings);

            // Construction du lien sur la fiche contact
            $arrRequest = array();

            $arrRequest[] = "headingid={$headingid}";
            $arrRequest[] = "op=contact";
            $arrRequest[] = "template_moduleid={$template_moduleid}";
            $arrRequest[] = "directory_contact_id={$row['id']}";
            if (!empty($_REQUEST['webedit_mode'])) $arrRequest[] = "webedit_mode={$_REQUEST['webedit_mode']}";

            $template_body->assign_block_vars('directory_switch_full.line.contact',
                array(
                    'ID' => $row['id'],
                    'POSITION' => $row['position'],
                    'CIVILITY' => ploopi\str::htmlentities($row['civility']),
                    'LASTNAME' => ploopi\str::htmlentities($row['lastname']),
                    'FIRSTNAME' => ploopi\str::htmlentities($row['firstname']),
                    'EMAIL' => ploopi\str::htmlentities($row['email']),
                    'PHONE' => ploopi\str::htmlentities($row['phone']),
                    'FAX' => ploopi\str::htmlentities($row['fax']),
                    'MOBILE' => ploopi\str::htmlentities($row['mobile']),
                    'SERVICE' => ploopi\str::htmlentities($row['service']),
                    'FUNCTION' => ploopi\str::htmlentities($row['function']),
                    'RANK' => ploopi\str::htmlentities($row['rank']),
                    'NUMBER' => ploopi\str::htmlentities($row['number']),
                    'POSTALCODE' => ploopi\str::htmlentities($row['postalcode']),
                    'ADDRESS' => ploopi\str::htmlentities($row['address']),
                    'CITY' => ploopi\str::htmlentities($row['city']),
                    'COUNTRY' => ploopi\str::htmlentities($row['country']),
                    'ADDRESS_FULL' => implode('<br />', $arrAddress),
                    'BUILDING' => ploopi\str::htmlentities($row['building']),
                    'FLOOR' => ploopi\str::htmlentities($row['floor']),
                    'OFFICE' => ploopi\str::htmlentities($row['office']),
                    'PHOTOPATH' => $row['photopath'],
                    'COMMENTS' => ploopi\str::nl2br(ploopi\str::htmlentities($row['comments'])),
                    'HEADING' => ploopi\str::htmlentities($arrHeadings['list'][$intHeadingId]['label']),
                    'HEADINGS' => ploopi\str::htmlentities($strContactHeadings),
                    'ALTERNATE_STYLE' => $c%2,
                    'LINK' => ploopi\crypt::urlencode('index.php?'.implode('&',$arrRequest))
                )
            );

        }
    }

    if (isset($arrHeadings['tree'][$intHeadingId]))
    {
        foreach($arrHeadings['tree'][$intHeadingId] as $intId)
        {
            $template_body->assign_block_vars('directory_switch_full.line', array());

            // Construction du lien sur la rubrique
            $arrRequest = array();

            $arrRequest[] = "headingid={$headingid}";
            $arrRequest[] = "op=full";
            $arrRequest[] = "template_moduleid={$template_moduleid}";
            $arrRequest[] = "directory_heading_id={$intId}";
            if (!empty($_REQUEST['webedit_mode'])) $arrRequest[] = "webedit_mode={$_REQUEST['webedit_mode']}";

            $template_body->assign_block_vars('directory_switch_full.line.heading',
                array(
                    'ID' => $intId,
                    'LABEL' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['label']),
                    'PHONE' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['phone']),
                    'FAX' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['fax']),
                    'POSTALCODE' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['postalcode']),
                    'ADDRESS' => ploopi\str::nl2br(ploopi\str::htmlentities($arrHeadings['list'][$intId]['address'])),
                    'CITY' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['city']),
                    'COUNTRY' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['country']),
                    'DEPTH' => substr_count($arrHeadings['list'][$intId]['parents'], ';')+1,
                    'LINK' => ploopi\crypt::urlencode('index.php?'.implode('&',$arrRequest))
                )
            );

            directory_template_display($template_body, $arrHeadings, $arrContacts, $intId);
        }
    }
}

/**
 * Affichage frontoffice de l'organigramme (intégration template)
 *
 * @param object $template_body template
 * @param array $arrHeadings tableau des rubriques
 * @param int $intHeadingId rubrique à afficher
 */

function directory_template_display_organigram(&$template_body, &$arrHeadings, $intHeadingId = 0)
{
    global $template_moduleid;
    global $headingid;

    if (isset($arrHeadings['tree'][$intHeadingId]))
    {
        foreach($arrHeadings['tree'][$intHeadingId] as $intId)
        {
            // Construction du lien sur la rubrique
            $arrRequest = array();

            $arrRequest[] = "headingid={$headingid}";
            $arrRequest[] = "op=full";
            $arrRequest[] = "template_moduleid={$template_moduleid}";
            $arrRequest[] = "directory_heading_id={$intId}";
            if (!empty($_REQUEST['webedit_mode'])) $arrRequest[] = "webedit_mode={$_REQUEST['webedit_mode']}";

            $template_body->assign_block_vars('directory_switch_organigram.heading',
                array(
                    'ID' => $intId,
                    'LABEL' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['label']),
                    'PHONE' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['phone']),
                    'FAX' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['fax']),
                    'POSTALCODE' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['postalcode']),
                    'ADDRESS' => ploopi\str::nl2br(ploopi\str::htmlentities($arrHeadings['list'][$intId]['address'])),
                    'CITY' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['city']),
                    'COUNTRY' => ploopi\str::htmlentities($arrHeadings['list'][$intId]['country']),
                    'DEPTH' => substr_count($arrHeadings['list'][$intId]['parents'], ';')+1,
                    'LINK' => ploopi\crypt::urlencode('index.php?'.implode('&',$arrRequest))
                )
            );

            directory_template_display_organigram($template_body, $arrHeadings, $intId);
        }
    }
}
?>
