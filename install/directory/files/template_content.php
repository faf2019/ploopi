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

    You should have received a copy of the GNU GeneralF Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Affichage template de l'annuaire
 *
 * @package directory
 * @subpackage template
 * @copyright Ovensia
 * @author Stéphane Escaich
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Init du module
 */

ploopi_init_module('directory', false, false, false);

/**
 * Inclusions spécifiques
 */
include_once './modules/directory/class_directory_contact.php';

/**
 * Récupération de la variable $op
 */

$op = empty($_REQUEST['op']) ? '' : $_REQUEST['op'];

// Récupération des paramètres
$arrFilter = array();

// On ne veut pas les caractères % et | dans la recherche avec LIKE
$pattern = '%|_';

// Lecture SESSION
if (isset($_SESSION['directory']['tpl_search'])) $arrFilter = $_SESSION['directory']['tpl_search'];

$arrParams = array(
    'directory_lastname',
    'directory_firstname',
    'directory_phone',
    'directory_email',
    'directory_number',
    'directory_service',
    'directory_function',
    'directory_rank',
    'directory_country',
    'directory_city',
    'directory_postalcode',
    'directory_heading'
);

foreach($arrParams as $strParam)
{
    // Lecture Param
    if (isset($_POST[$strParam]) && !ereg($pattern, $_POST[$strParam])) $arrFilter[$strParam] = $_POST[$strParam];
    // Affectation de valeur par défaut si non défini
    if (!isset($arrFilter[$strParam])) $arrFilter[$strParam] = '';
}

// Enregistrement SESSION
$_SESSION['directory']['tpl_search'] = $arrFilter;

$template_body->assign_vars(
    array(
        'DIRECTORY_SEARCH_LASTNAME' => htmlentities($arrFilter['directory_lastname']),
        'DIRECTORY_SEARCH_FIRSTNAME' => htmlentities($arrFilter['directory_firstname']),
        'DIRECTORY_SEARCH_PHONE' => htmlentities($arrFilter['directory_phone']),
        'DIRECTORY_SEARCH_EMAIL' => htmlentities($arrFilter['directory_email']),
        'DIRECTORY_SEARCH_NUMBER' => htmlentities($arrFilter['directory_number']),
        'DIRECTORY_SEARCH_SERVICE' => htmlentities($arrFilter['directory_service']),
        'DIRECTORY_SEARCH_FUNCTION' => htmlentities($arrFilter['directory_function']),
        'DIRECTORY_SEARCH_RANK' => htmlentities($arrFilter['directory_rank']),
        'DIRECTORY_SEARCH_COUNTRY' => htmlentities($arrFilter['directory_country']),
        'DIRECTORY_SEARCH_CITY' => htmlentities($arrFilter['directory_city']),
        'DIRECTORY_SEARCH_POSTALCODE' => htmlentities($arrFilter['directory_postalcode']),
        'DIRECTORY_SEARCH_HEADING' => $arrFilter['directory_heading']
    )
);

switch($op)
{
    case 'search':
        $arrDirectoryHeadings = directory_getheadings();
        
        $template_body->assign_block_vars('directory_switch_result', array());

        // Construction de la requête de recherche
        $arrWhere = array();
        $arrWhere[] = 'c.id_heading > 0';
        $arrWhere[] = 'h.id = c.id_heading';
        
        if ($arrFilter['directory_heading'] != '') // Recherche sur rubrique (un peu spécial)
        {
            $arrHeadingId = array();
            
            // recherche sur libellé de rubrique
            $ptrRs = $db->query("
                SELECT      h.*
    
                FROM        ploopi_mod_directory_heading h
    
                WHERE       label LIKE '%".$db->addslashes($arrFilter['directory_heading'])."%'
            ");
            
            // rubriques répondant au libellé 
            while ($row = $db->fetchrow($ptrRs)) $arrHeadingId[] = $row['id'];
            
            // recherche des rubriques filles et complétion du tableau de rubriques de recherche
            $intHid = current($arrHeadingId);
            while ($intHid !== false)
            {
                if (!empty($arrDirectoryHeadings['tree'][$intHid])) foreach($arrDirectoryHeadings['tree'][$intHid] as $intNewHid) $arrHeadingId[] = $intNewHid; 
                $intHid = next($arrHeadingId);
            }
            
            if (!empty($arrHeadingId)) $arrWhere[] = 'c.id_heading IN ('.implode(',', $arrHeadingId).')';
            else $arrWhere[] = 'c.id_heading = -1';
        }
        

        if (!empty($arrFilter['directory_lastname'])) $arrWhere[] = "c.lastname LIKE '".$db->addslashes($arrFilter['directory_lastname'])."%'";
        if (!empty($arrFilter['directory_firstname'])) $arrWhere[] = "c.firstname LIKE '".$db->addslashes($arrFilter['directory_firstname'])."%'";
        if (!empty($arrFilter['directory_phone'])) $arrWhere[] = "c.phone LIKE '".$db->addslashes($arrFilter['directory_phone'])."%'";
        if (!empty($arrFilter['directory_email'])) $arrWhere[] = "c.email LIKE '".$db->addslashes($arrFilter['directory_email'])."%'";
        if (!empty($arrFilter['directory_number'])) $arrWhere[] = "c.number LIKE '".$db->addslashes($arrFilter['directory_number'])."%'";
        if (!empty($arrFilter['directory_service'])) $arrWhere[] = "c.service LIKE '".$db->addslashes($arrFilter['directory_service'])."%'";
        if (!empty($arrFilter['directory_function'])) $arrWhere[] = "c.function LIKE '".$db->addslashes($arrFilter['directory_function'])."%'";
        if (!empty($arrFilter['directory_rank'])) $arrWhere[] = "c.rank LIKE '".$db->addslashes($arrFilter['directory_rank'])."%'";
        if (!empty($arrFilter['directory_country'])) $arrWhere[] = "c.country LIKE '".$db->addslashes($arrFilter['directory_country'])."%'";
        if (!empty($arrFilter['directory_city'])) $arrWhere[] = "c.city LIKE '".$db->addslashes($arrFilter['directory_city'])."%'";
        if (!empty($arrFilter['directory_postalcode'])) $arrWhere[] = "c.postalcode LIKE '".$db->addslashes($arrFilter['directory_postalcode'])."%'";

        // Exécution de la requête principale permettant de lister les utilisateurs selon le filtre
        $ptrRs = $db->query("
            SELECT      c.*, h.label

            FROM        ploopi_mod_directory_contact c,
                        ploopi_mod_directory_heading h

            WHERE       ".implode(' AND ', $arrWhere)."

            ORDER BY    c.lastname, c.firstname
        ");

        if ($db->numrows())
        {
            $c = 0;
            while ($row = $db->fetchrow($ptrRs))
            {
                $c++;

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
                
                foreach(split(';', $arrDirectoryHeadings['list'][$row['id_heading']]['parents']) as $intIdHeading)
                {
                    if (isset($arrDirectoryHeadings['list'][$intIdHeading])) $arrContactHeadings[] = $arrDirectoryHeadings['list'][$intIdHeading]['label'];
                }
                
                $arrContactHeadings[] = $row['label'];
                $strContactHeadings = implode(' > ', $arrContactHeadings);


                $template_body->assign_block_vars('directory_switch_result.contact',
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
                        'COMMENTS' => ploopi_nl2br(htmlentities($row['comments'])),
                        'HEADING' => htmlentities($row['label']),
                        'HEADINGS' => htmlentities($strContactHeadings),
                        'ALTERNATE_STYLE' => $c%2,
                        'LINK' => ploopi_urlencode("index.php?headingid={$headingid}&template_moduleid={$template_moduleid}&op=contact&directory_contact_id={$row['id']}")
                    )
                );

            }
        }
        else
        {
            $template_body->assign_block_vars('directory_switch_result.switch_message',
                array(
                    'CONTENT' => "Il n'y a pas de réponse pour votre recherche"
                )
            );

        }

    break;

    case 'contact':
        // Récupération des rubriques
        $arrDirectoryHeadings = directory_getheadings();

        $objContact = new directory_contact();
        if (!empty($_GET['directory_contact_id']) && is_numeric($_GET['directory_contact_id']) && $objContact->open($_GET['directory_contact_id']))
        {
            if (file_exists($objContact->getphotopath())) $strPhotopath = ploopi_urlencode("index-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$_GET['directory_contact_id']}");
            else $strPhotopath = './modules/directory/img/nopic.gif';

            $arrAddress = array();
            if (!empty($objContact->fields['address'])) $arrAddress[] = ploopi_nl2br(htmlentities($objContact->fields['address']));
            if (!empty($objContact->fields['postalcode']) || !empty($objContact->fields['city'])) $arrAddress[] = ploopi_nl2br(htmlentities(trim($objContact->fields['postalcode'].' '.$objContact->fields['city'])));
            if (!empty($objContact->fields['country'])) $arrAddress[] = ploopi_nl2br(htmlentities($objContact->fields['country']));

            // Construction du lien sur l'annuaire détaillé de la rubrique
            $arrRequest = array();
            $arrRequest['headingid'] = "headingid={$headingid}";
            $arrRequest['op'] = "op=full";
            $arrRequest['template_moduleid'] = "template_moduleid={$template_moduleid}";
            if (!empty($_REQUEST['webedit_mode'])) $arrRequest['webedit_mode'] = "webedit_mode={$_REQUEST['webedit_mode']}";

            // Tableau des rubriques associées au contact
            $arrDirectoryHeadingsLabel = array();

            foreach(explode(';', $arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['parents']) as $intHeadingId)
            {
                $arrRequest['directory_heading_id'] = "directory_heading_id={$intHeadingId}";
                if (isset($arrDirectoryHeadings['list'][$intHeadingId])) $arrDirectoryHeadingsLabel[$intHeadingId] = '<a title="Ouvrir l\'annuaire détaillé de '.htmlentities($arrDirectoryHeadings['list'][$intHeadingId]['label']).'" href="'.ploopi_urlencode('index.php?'.implode('&',$arrRequest)).'">'.htmlentities($arrDirectoryHeadings['list'][$intHeadingId]['label']).'</a>';
            }

            $arrRequest['directory_heading_id'] = "directory_heading_id={$objContact->fields['id_heading']}";
            $arrDirectoryHeadingsLabel[] = '<a title="Ouvrir l\'annuaire détaillé de '.htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['label']).'" href="'.ploopi_urlencode('index.php?'.implode('&',$arrRequest)).'">'.htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['label']).'</a>';

            $arrHeadingAddress = array();
            if (!empty($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['address'])) $arrHeadingAddress[] = ploopi_nl2br(htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['address']));
            if (!empty($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['postalcode']) || !empty($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['city'])) $arrHeadingAddress[] = ploopi_nl2br(htmlentities(trim($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['postalcode'].' '.$arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['city'])));
            if (!empty($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['country'])) $arrHeadingAddress[] = ploopi_nl2br(htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['country']));

            $template_body->assign_block_vars('directory_switch_contact',
                array(
                    'CIVILITY' => htmlentities($objContact->fields['civility']),
                    'LASTNAME' => htmlentities($objContact->fields['lastname']),
                    'FIRSTNAME' => htmlentities($objContact->fields['firstname']),
                    'EMAIL' => htmlentities($objContact->fields['email']),
                    'PHONE' => htmlentities($objContact->fields['phone']),
                    'FAX' => htmlentities($objContact->fields['fax']),
                    'MOBILE' => htmlentities($objContact->fields['mobile']),
                    'SERVICE' => htmlentities($objContact->fields['service']),
                    'FUNCTION' => htmlentities($objContact->fields['function']),
                    'RANK' => htmlentities($objContact->fields['rank']),
                    'NUMBER' => htmlentities($objContact->fields['number']),
                    'POSTALCODE' => htmlentities($objContact->fields['postalcode']),
                    'ADDRESS' => ploopi_nl2br(htmlentities($objContact->fields['address'])),
                    'CITY' => htmlentities($objContact->fields['city']),
                    'COUNTRY' => htmlentities($objContact->fields['country']),
                    'ADDRESS_FULL' => implode('<br />', $arrAddress),
                    'BUILDING' => htmlentities($objContact->fields['building']),
                    'FLOOR' => htmlentities($objContact->fields['floor']),
                    'OFFICE' => htmlentities($objContact->fields['office']),
                    'HEADING' => htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['label']),
                    'HEADINGS' => implode('<br />', $arrDirectoryHeadingsLabel),
                    'HEADING_PHONE' => htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['phone']),
                    'HEADING_FAX' => htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['fax']),
                    'HEADING_POSTALCODE' => htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['postalcode']),
                    'HEADING_ADDRESS' => ploopi_nl2br(htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['address'])),
                    'HEADING_CITY' => htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['city']),
                    'HEADING_COUNTRY' => htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['country']),
                    'HEADING_ADDRESS' => ploopi_nl2br(htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['address'])),
                    'HEADING_ADDRESS_FULL' => implode('<br />', $arrHeadingAddress),
                    'PHOTOPATH' => $strPhotopath,
                    'COMMENTS' => ploopi_nl2br(htmlentities($objContact->fields['comments']))
                )
            );

            // Recherche des personnes du même service
            $ptrRs = $db->query("
                SELECT      c.*

                FROM        ploopi_mod_directory_contact c

                WHERE       id_heading = {$objContact->fields['id_heading']}
                AND         id <> {$objContact->fields['id']}

                ORDER BY    c.lastname, c.firstname
            ");

            $c = 0;
            while ($row = $db->fetchrow($ptrRs))
            {
                $c++;

                $arrAddress = array();
                if (!empty($row['address'])) $arrAddress[] = ploopi_nl2br(htmlentities($row['address']));
                if (!empty($row['postalcode']) || !empty($row['city'])) $arrAddress[] = ploopi_nl2br(htmlentities(trim($row['postalcode'].' '.$row['city'])));
                if (!empty($row['country'])) $arrAddress[] = ploopi_nl2br(htmlentities($row['country']));

                $objContact = new directory_contact();
                $objContact->fields['id'] = $row['id'];

                if (file_exists($objContact->getphotopath())) $row['photopath'] = ploopi_urlencode("index-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$row['id']}");
                else $row['photopath'] = './modules/directory/img/nopic.gif';

                // Construction du lien sur la fiche contact
                $arrRequest = array();

                $arrRequest[] = "headingid={$headingid}";
                $arrRequest[] = "op=contact";
                $arrRequest[] = "template_moduleid={$template_moduleid}";
                $arrRequest[] = "directory_contact_id={$row['id']}";
                if (!empty($_REQUEST['webedit_mode'])) $arrRequest[] = "webedit_mode={$_REQUEST['webedit_mode']}";

                $template_body->assign_block_vars('directory_switch_contact.contact',
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
                        'COMMENTS' => ploopi_nl2br(htmlentities($row['comments'])),
                        'ALTERNATE_STYLE' => $c%2,
                        'LINK' => ploopi_urlencode('index.php?'.implode('&',$arrRequest))
                    )
                );

            }

        }
    break;

    /**
     * Affichage de l'annuaire complet
     */
    case 'full':
        $intHeadingId = isset($_GET['directory_heading_id']) && is_numeric($_GET['directory_heading_id']) ? $_GET['directory_heading_id'] : 0;

        // Récupération des rubriques
        $arrDirectoryHeadings = directory_getheadings();

        // Récupération des contacts par rubriques
        $arrContacts = directory_getcontacts();

        $template_body->assign_block_vars('directory_switch_full', array());

        if ($intHeadingId) // Rubrique sélectionnée
        {
            $template_body->assign_block_vars('directory_switch_full.switch_selected_heading', array());

            // Tableau des rubriques à afficher
            $arrSelectedHeadings = explode(';', $arrDirectoryHeadings['list'][$intHeadingId]['parents']);
            unset($arrSelectedHeadings[0]);
            $arrSelectedHeadings[] = $intHeadingId;

            // Construction du lien sur la rubrique
            $arrRequest = array();

            $arrRequest[] = "headingid={$headingid}";
            $arrRequest[] = "op=full";
            $arrRequest[] = "template_moduleid={$template_moduleid}";
            if (!empty($_REQUEST['webedit_mode'])) $arrRequest[] = "webedit_mode={$_REQUEST['webedit_mode']}";

            foreach($arrSelectedHeadings as $intId)
            {
                $arrRequest[] = "directory_heading_id={$intId}";

                $template_body->assign_block_vars('directory_switch_full.switch_selected_heading.heading',
                    array(
                        'LABEL' => isset($arrDirectoryHeadings['list'][$intId]) ? $arrDirectoryHeadings['list'][$intId]['label'] : '',
                        'LINK' => ploopi_urlencode('index.php?'.implode('&',$arrRequest))
                    )
                );
            }
        }

        directory_template_display($template_body, $arrDirectoryHeadings, $arrContacts, $intHeadingId);
    break;
    
    /**
     * Affichage de l'organigramme
     */
    case 'organigram':
        // Récupération des rubriques
        $arrDirectoryHeadings = directory_getheadings();

        $template_body->assign_block_vars('directory_switch_organigram', array());

        directory_template_display_organigram($template_body, $arrDirectoryHeadings);
    break;
    
    /**
     * Affichage des numéros abrégés
     */
    case 'speeddialing':
        $template_body->assign_block_vars('directory_switch_speeddialing', array());
        
        $db->query("
            SELECT      * 
            FROM        ploopi_mod_directory_speeddialing
            ORDER BY    heading, label
        ");
        
        $strHeading = null;
        
        while ($row = $db->fetchrow()) 
        {
            if ($row['heading'] != $strHeading) // Nouvelle rubrique
            {
                $strHeading = $row['heading'];
                
                $template_body->assign_block_vars('directory_switch_speeddialing.heading',
                    array(
                        'ID' => urlencode(ploopi_convertaccents(strtolower(strtr(trim($strHeading), _PLOOPI_INDEXATION_WORDSEPARATORS, str_pad('', strlen(_PLOOPI_INDEXATION_WORDSEPARATORS), '-'))))),
                        'LABEL' => htmlentities($strHeading)
                    )
                );
                
            }

            $template_body->assign_block_vars('directory_switch_speeddialing.heading.number',
                array(
                    'LABEL' => htmlentities($row['label']),
                    'NUMBER' => htmlentities($row['number']),
                    'SHORTNUMBER' => htmlentities($row['shortnumber'])
                )
            );
        }
        
    break;
}

?>