<?php
/*
    Copyright (c) 2002-2007 Netlor
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
 * Fonctions, constantes, variables globales
 *
 * @package directory
 * @subpackage global
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

define ('_DIRECTORY_MANAGE_GROUP',  1);
define ('_DIRECTORY_MANAGE_COMMON', 2);

define ('_DIRECTORY_ACTION_CONTACTS',         1);
define ('_DIRECTORY_ACTION_MANAGERS',         2);
define ('_DIRECTORY_ACTION_SPEEDDIALING',     3);

define ('_DIRECTORY_OBJECT_HEADING',        1);

/**
 * Retourne l'ensemble des rubriques dans un tableau
 *
 * @return array tableau contenant les rubriques
 */

function directory_getheadings($intIdHeading = 0)
{
    global $db;

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
            $fields['parents'] = (isset($arrHeadings['list'][$fields['id_heading']])) ? "{$arrHeadings['list'][$fields['id_heading']]['parents']};{$fields['id_heading']}" : $fields['id_heading'];

            $arrHeadings['list'][$fields['id']] = $fields;
            $arrHeadings['tree'][$fields['id_heading']][] = $fields['id'];
        }
    }

    return($arrHeadings);
}

/**
 * Retourne l'ensemble des contacts partagés triés par rubrique dans un tableau
 *
 * @return array tableau contenant les contacts
 */

function directory_getcontacts()
{
    global $db;

    $arrContacts = array();

    $result = $db->query("
        SELECT      *
        FROM        ploopi_mod_directory_contact
        WHERE       id_heading > 0
        ORDER BY    lastname, firstname
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

function directory_gettreeview($headings = array())
{
    global $db;

    $treeview =
        array(
            'list' => array(),
            'tree' => array()
        );

    foreach($headings['list'] as $id => $fields)
    {
        $treeview['list'][$fields['id']] =
            array(
                'id' => $fields['id'],
                'label' => $fields['label'],
                'description' => $fields['description'],
                'parents' => split(';', $fields['parents']),
                'node_link' => '',
                'node_onclick' => "ploopi_skin_treeview_shownode('{$fields['id']}', '".ploopi_queryencode("ploopi_op=directory_heading_detail&directory_heading_id={$fields['id']}")."', 'admin-light.php');",
                'link' => ploopi_urlencode("admin.php?directory_heading_id={$fields['id']}"),
                'onclick' => '',
                'icon' => './modules/directory/img/ico_heading.png'
            );

        $treeview['tree'][$fields['id_heading']][] = $fields['id'];
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

    // Gestion des contacts de la rubrique
    if (isset($arrContacts[$intHeadingId]))
    {
        $c = 0;
        foreach($arrContacts[$intHeadingId] as $row)
        {
            $c++;

            $template_body->assign_block_vars('directory_switch_full.line', array());

            $arrAddress = array();
            if (!empty($row['address'])) $arrAddress[] = ploopi_nl2br(htmlentities($row['address']));
            if (!empty($row['postalcode']) || !empty($row['city'])) $arrAddress[] = ploopi_nl2br(htmlentities(trim($row['postalcode'].' '.$row['city'])));
            if (!empty($row['country'])) $arrAddress[] = ploopi_nl2br(htmlentities($row['country']));

            $objContact = new directory_contact();
            $objContact->fields['id'] = $row['id'];

            if (file_exists($objContact->getphotopath())) $row['photopath'] = ploopi_urlencode("index-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$row['id']}");
            else $row['photopath'] = './modules/directory/img/nopic.gif';

            // Récupération des rubriques du contact
            $arrContactHeadings = array();
            
            foreach(split(';', $arrHeadings['list'][$intHeadingId]['parents']) as $intHid)
            {
                if (isset($arrHeadings['list'][$intHid])) $arrContactHeadings[] = $arrHeadings['list'][$intHid]['label'];
            }
            
            $arrContactHeadings[] = $arrHeadings['list'][$intHeadingId]['label'];
            $strContactHeadings = implode(' > ', $arrContactHeadings);            
            
            // Construction du lien sur la fiche contact
            $arrRequest = array();

            $arrRequest[] = "op=contact";
            $arrRequest[] = "template_moduleid={$template_moduleid}";
            $arrRequest[] = "directory_contact_id={$row['id']}";
            if (!empty($_REQUEST['webedit_mode'])) $arrRequest[] = "webedit_mode={$_REQUEST['webedit_mode']}";

            $template_body->assign_block_vars('directory_switch_full.line.contact',
                array(
                    'ID' => $row['id'],
                    'CIVILITY' => htmlentities($row['civility']),
                    'LASTNAME' => htmlentities($row['lastname']),
                    'FIRSTNAME' => htmlentities($row['firstname']),
                    'EMAIL' => htmlentities($row['email']),
                    'PHONE' => htmlentities($row['phone']),
                    'FAX' => htmlentities($row['fax']),
                    'MOBILE' => htmlentities($row['mobile']),
                    'SERVICE' => htmlentities($row['service']),
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
                    'PHOTOPATH' => $row['photopath'],
                    'HEADING' => htmlentities($arrHeadings['list'][$intHeadingId]['label']),
                    'HEADINGS' => htmlentities($strContactHeadings),
                    'ALTERNATE_STYLE' => $c%2,
                    'LINK' => ploopi_urlencode('index.php?'.implode('&',$arrRequest))
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

            $arrRequest[] = "op=full";
            $arrRequest[] = "template_moduleid={$template_moduleid}";
            $arrRequest[] = "directory_heading_id={$intId}";
            if (!empty($_REQUEST['webedit_mode'])) $arrRequest[] = "webedit_mode={$_REQUEST['webedit_mode']}";

            $template_body->assign_block_vars('directory_switch_full.line.heading',
                array(
                    'ID' => $intId,
                    'LABEL' => htmlentities($arrHeadings['list'][$intId]['label']),
                    'PHONE' => htmlentities($arrHeadings['list'][$intId]['phone']),
                    'FAX' => htmlentities($arrHeadings['list'][$intId]['fax']),
                    'POSTALCODE' => htmlentities($arrHeadings['list'][$intId]['postalcode']),
                    'ADDRESS' => ploopi_nl2br(htmlentities($arrHeadings['list'][$intId]['address'])),
                    'CITY' => htmlentities($arrHeadings['list'][$intId]['city']),
                    'COUNTRY' => htmlentities($arrHeadings['list'][$intId]['country']),
                    'DEPTH' => substr_count($arrHeadings['list'][$intId]['parents'], ';')+1,
                    'LINK' => ploopi_urlencode('index.php?'.implode('&',$arrRequest))
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

    if (isset($arrHeadings['tree'][$intHeadingId]))
    {
        foreach($arrHeadings['tree'][$intHeadingId] as $intId)
        {
            // Construction du lien sur la rubrique
            $arrRequest = array();

            $arrRequest[] = "op=full";
            $arrRequest[] = "template_moduleid={$template_moduleid}";
            $arrRequest[] = "directory_heading_id={$intId}";
            if (!empty($_REQUEST['webedit_mode'])) $arrRequest[] = "webedit_mode={$_REQUEST['webedit_mode']}";

            $template_body->assign_block_vars('directory_switch_organigram.heading',
                array(
                    'ID' => $intId,
                    'LABEL' => htmlentities($arrHeadings['list'][$intId]['label']),
                    'PHONE' => htmlentities($arrHeadings['list'][$intId]['phone']),
                    'FAX' => htmlentities($arrHeadings['list'][$intId]['fax']),
                    'POSTALCODE' => htmlentities($arrHeadings['list'][$intId]['postalcode']),
                    'ADDRESS' => ploopi_nl2br(htmlentities($arrHeadings['list'][$intId]['address'])),
                    'CITY' => htmlentities($arrHeadings['list'][$intId]['city']),
                    'COUNTRY' => htmlentities($arrHeadings['list'][$intId]['country']),
                    'DEPTH' => substr_count($arrHeadings['list'][$intId]['parents'], ';')+1,
                    'LINK' => ploopi_urlencode('index.php?'.implode('&',$arrRequest))
                )
            );

            directory_template_display_organigram($template_body, $arrHeadings, $intId);
        }
    }
}
?>
