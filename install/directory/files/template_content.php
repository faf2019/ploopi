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
 * @author Ovensia
 * @version  $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */

/**
 * Init du module
 */

ploopi\module::init('directory', false, false, false);

/**
 * Inclusions spécifiques
 */
include_once './modules/directory/class_directory_contact.php';

/**
 * Récupération de la variable $op
 */

$op = empty($_REQUEST['op']) ? '' : $_REQUEST['op'];

switch($op)
{
    case 'search':

        // Récupération des paramètres
        $arrFilter = array();

        // On ne veut pas les caractères % et | dans la recherche avec LIKE
        $pattern = '/%|_/';

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
            'directory_heading',
            'directory_comments'
        );

        foreach($arrParams as $strParam)
        {
            // Lecture Param
            if (isset($_POST[$strParam]) && !preg_match($pattern, $_POST[$strParam])) $arrFilter[$strParam] = $_POST[$strParam];
            // Affectation de valeur par défaut si non défini
            if (!isset($arrFilter[$strParam])) $arrFilter[$strParam] = '';
        }

        // Enregistrement SESSION
        $_SESSION['directory']['tpl_search'] = $arrFilter;

        $template_body->assign_vars(
            array(
                'DIRECTORY_SEARCH_LASTNAME' => ploopi\str::htmlentities($arrFilter['directory_lastname']),
                'DIRECTORY_SEARCH_FIRSTNAME' => ploopi\str::htmlentities($arrFilter['directory_firstname']),
                'DIRECTORY_SEARCH_PHONE' => ploopi\str::htmlentities($arrFilter['directory_phone']),
                'DIRECTORY_SEARCH_EMAIL' => ploopi\str::htmlentities($arrFilter['directory_email']),
                'DIRECTORY_SEARCH_NUMBER' => ploopi\str::htmlentities($arrFilter['directory_number']),
                'DIRECTORY_SEARCH_SERVICE' => ploopi\str::htmlentities($arrFilter['directory_service']),
                'DIRECTORY_SEARCH_FUNCTION' => ploopi\str::htmlentities($arrFilter['directory_function']),
                'DIRECTORY_SEARCH_RANK' => ploopi\str::htmlentities($arrFilter['directory_rank']),
                'DIRECTORY_SEARCH_COUNTRY' => ploopi\str::htmlentities($arrFilter['directory_country']),
                'DIRECTORY_SEARCH_CITY' => ploopi\str::htmlentities($arrFilter['directory_city']),
                'DIRECTORY_SEARCH_POSTALCODE' => ploopi\str::htmlentities($arrFilter['directory_postalcode']),
                'DIRECTORY_SEARCH_HEADING' => $arrFilter['directory_heading'],
                'DIRECTORY_SEARCH_COMMENTS' => $arrFilter['directory_comments']
            )
        );


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
            $ptrRs = ploopi\db::get()->query("
                SELECT      h.*

                FROM        ploopi_mod_directory_heading h

                WHERE       label LIKE '%".ploopi\db::get()->addslashes($arrFilter['directory_heading'])."%'
            ");

            // rubriques répondant au libellé
            while ($row = ploopi\db::get()->fetchrow($ptrRs)) $arrHeadingId[] = $row['id'];

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


        if (!empty($arrFilter['directory_lastname'])) $arrWhere[] = "c.lastname LIKE '".ploopi\db::get()->addslashes($arrFilter['directory_lastname'])."%'";
        if (!empty($arrFilter['directory_firstname'])) $arrWhere[] = "c.firstname LIKE '".ploopi\db::get()->addslashes($arrFilter['directory_firstname'])."%'";
        if (!empty($arrFilter['directory_phone'])) $arrWhere[] = "c.phone LIKE '".ploopi\db::get()->addslashes($arrFilter['directory_phone'])."%'";
        if (!empty($arrFilter['directory_email'])) $arrWhere[] = "c.email LIKE '%".ploopi\db::get()->addslashes($arrFilter['directory_email'])."%'";
        if (!empty($arrFilter['directory_number'])) $arrWhere[] = "c.number LIKE '".ploopi\db::get()->addslashes($arrFilter['directory_number'])."%'";
        if (!empty($arrFilter['directory_service'])) $arrWhere[] = "c.service LIKE '%".ploopi\db::get()->addslashes($arrFilter['directory_service'])."%'";
        if (!empty($arrFilter['directory_function'])) $arrWhere[] = "c.function LIKE '%".ploopi\db::get()->addslashes($arrFilter['directory_function'])."%'";
        if (!empty($arrFilter['directory_rank'])) $arrWhere[] = "c.rank LIKE '%".ploopi\db::get()->addslashes($arrFilter['directory_rank'])."%'";
        if (!empty($arrFilter['directory_country'])) $arrWhere[] = "c.country LIKE '%".ploopi\db::get()->addslashes($arrFilter['directory_country'])."%'";
        if (!empty($arrFilter['directory_city'])) $arrWhere[] = "c.city LIKE '%".ploopi\db::get()->addslashes($arrFilter['directory_city'])."%'";
        if (!empty($arrFilter['directory_postalcode'])) $arrWhere[] = "c.postalcode LIKE '".ploopi\db::get()->addslashes($arrFilter['directory_postalcode'])."%'";
        if (!empty($arrFilter['directory_comments'])) $arrWhere[] = "c.comments LIKE '%".ploopi\db::get()->addslashes($arrFilter['directory_comments'])."%'";

        // Exécution de la requête principale permettant de lister les utilisateurs selon le filtre
        $ptrRs = ploopi\db::get()->query("
            SELECT      c.*, h.label

            FROM        ploopi_mod_directory_contact c,
                        ploopi_mod_directory_heading h

            WHERE       ".implode(' AND ', $arrWhere)."

            ORDER BY    c.lastname, c.firstname
        ");

        if (ploopi\db::get()->numrows())
        {
            $c = 0;
            while ($row = ploopi\db::get()->fetchrow($ptrRs))
            {
                $c++;

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

                foreach(preg_split('/;/', $arrDirectoryHeadings['list'][$row['id_heading']]['parents']) as $intIdHeading)
                {
                    if (isset($arrDirectoryHeadings['list'][$intIdHeading])) $arrContactHeadings[] = $arrDirectoryHeadings['list'][$intIdHeading]['label'];
                }

                $arrContactHeadings[] = $row['label'];
                $strContactHeadings = implode(' > ', $arrContactHeadings);


                $template_body->assign_block_vars('directory_switch_result.contact',
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
                        'HEADING' => ploopi\str::htmlentities($row['label']),
                        'HEADINGS' => ploopi\str::htmlentities($strContactHeadings),
                        'ALTERNATE_STYLE' => $c%2,
                        'LINK' => ploopi\crypt::urlencode("index.php?headingid={$headingid}&template_moduleid={$template_moduleid}&op=contact&directory_contact_id={$row['id']}")
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
            if (file_exists($objContact->getphotopath())) $strPhotopath = ploopi\crypt::urlencode("index-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$_GET['directory_contact_id']}");
            else $strPhotopath = './modules/directory/img/nopic.gif';

            $arrAddress = array();
            if (!empty($objContact->fields['address'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities($objContact->fields['address']));
            if (!empty($objContact->fields['postalcode']) || !empty($objContact->fields['city'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities(trim($objContact->fields['postalcode'].' '.$objContact->fields['city'])));
            if (!empty($objContact->fields['country'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities($objContact->fields['country']));

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
                if (isset($arrDirectoryHeadings['list'][$intHeadingId])) $arrDirectoryHeadingsLabel[$intHeadingId] = '<a title="Ouvrir l\'annuaire détaillé de '.ploopi\str::htmlentities($arrDirectoryHeadings['list'][$intHeadingId]['label']).'" href="'.ploopi\crypt::urlencode('index.php?'.implode('&',$arrRequest)).'">'.ploopi\str::htmlentities($arrDirectoryHeadings['list'][$intHeadingId]['label']).'</a>';
            }

            $arrRequest['directory_heading_id'] = "directory_heading_id={$objContact->fields['id_heading']}";
            $arrDirectoryHeadingsLabel[] = '<a title="Ouvrir l\'annuaire détaillé de '.ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['label']).'" href="'.ploopi\crypt::urlencode('index.php?'.implode('&',$arrRequest)).'">'.ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['label']).'</a>';

            $arrHeadingAddress = array();
            if (!empty($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['address'])) $arrHeadingAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['address']));
            if (!empty($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['postalcode']) || !empty($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['city'])) $arrHeadingAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities(trim($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['postalcode'].' '.$arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['city'])));
            if (!empty($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['country'])) $arrHeadingAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['country']));

            $template_body->assign_block_vars('directory_switch_contact',
                array(
                    'POSITION' => $objContact->fields['position'],
                    'CIVILITY' => ploopi\str::htmlentities($objContact->fields['civility']),
                    'LASTNAME' => ploopi\str::htmlentities($objContact->fields['lastname']),
                    'FIRSTNAME' => ploopi\str::htmlentities($objContact->fields['firstname']),
                    'EMAIL' => ploopi\str::htmlentities($objContact->fields['email']),
                    'PHONE' => ploopi\str::htmlentities($objContact->fields['phone']),
                    'FAX' => ploopi\str::htmlentities($objContact->fields['fax']),
                    'MOBILE' => ploopi\str::htmlentities($objContact->fields['mobile']),
                    'SERVICE' => ploopi\str::htmlentities($objContact->fields['service']),
                    'FUNCTION' => ploopi\str::htmlentities($objContact->fields['function']),
                    'RANK' => ploopi\str::htmlentities($objContact->fields['rank']),
                    'NUMBER' => ploopi\str::htmlentities($objContact->fields['number']),
                    'POSTALCODE' => ploopi\str::htmlentities($objContact->fields['postalcode']),
                    'ADDRESS' => ploopi\str::nl2br(ploopi\str::htmlentities($objContact->fields['address'])),
                    'CITY' => ploopi\str::htmlentities($objContact->fields['city']),
                    'COUNTRY' => ploopi\str::htmlentities($objContact->fields['country']),
                    'ADDRESS_FULL' => implode('<br />', $arrAddress),
                    'BUILDING' => ploopi\str::htmlentities($objContact->fields['building']),
                    'FLOOR' => ploopi\str::htmlentities($objContact->fields['floor']),
                    'OFFICE' => ploopi\str::htmlentities($objContact->fields['office']),
                    'HEADING' => ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['label']),
                    'HEADINGS' => implode('<br />', $arrDirectoryHeadingsLabel),
                    'HEADING_PHONE' => ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['phone']),
                    'HEADING_FAX' => ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['fax']),
                    'HEADING_POSTALCODE' => ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['postalcode']),
                    'HEADING_ADDRESS' => ploopi\str::nl2br(ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['address'])),
                    'HEADING_CITY' => ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['city']),
                    'HEADING_COUNTRY' => ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['country']),
                    'HEADING_ADDRESS' => ploopi\str::nl2br(ploopi\str::htmlentities($arrDirectoryHeadings['list'][$objContact->fields['id_heading']]['address'])),
                    'HEADING_ADDRESS_FULL' => implode('<br />', $arrHeadingAddress),
                    'PHOTOPATH' => $strPhotopath,
                    'COMMENTS' => ploopi\str::nl2br(ploopi\str::htmlentities($objContact->fields['comments']))
                )
            );

            // Lecture du dossier racine de la mini ged associée à l'utilisateur
            $objRootFolder = ploopi\documentsfolder::getroot(
                _DIRECTORY_OBJECT_CONTACT,
                $objContact->fields['id'],
                $template_moduleid
            );

            if (!empty($objRootFolder))
            {
                $template_body->assign_block_vars('directory_switch_contact.switch_files', array());

                $arrFiles = $objRootFolder->getlist();

                foreach($arrFiles as $intIdFile => $rowFile)
                {
                    // Découpage du chemin pour modifier le fichier
                    $arrPath = explode('/', $rowFile['path']);
                    $strFileName = $arrPath[sizeof($arrPath)-1];
                    array_pop($arrPath);

                    $template_body->assign_block_vars('directory_switch_contact.switch_files.file', array(
                        'FILENAME' => $strFileName,
                        'PATH' => implode(' &raquo; ', $arrPath),
                        'URL' => $rowFile['file']->geturl()
                    ));
                }
            }


            // Recherche des personnes du même service
            $ptrRs = ploopi\db::get()->query("
                SELECT      c.*

                FROM        ploopi_mod_directory_contact c

                WHERE       id_heading = {$objContact->fields['id_heading']}
                AND         id <> {$objContact->fields['id']}

                ORDER BY    c.position, c.lastname, c.firstname
            ");

            $c = 0;
            while ($row = ploopi\db::get()->fetchrow($ptrRs))
            {
                $c++;

                $arrAddress = array();
                if (!empty($row['address'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities($row['address']));
                if (!empty($row['postalcode']) || !empty($row['city'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities(trim($row['postalcode'].' '.$row['city'])));
                if (!empty($row['country'])) $arrAddress[] = ploopi\str::nl2br(ploopi\str::htmlentities($row['country']));

                $objContact = new directory_contact();
                $objContact->fields['id'] = $row['id'];

                if (file_exists($objContact->getphotopath())) $row['photopath'] = ploopi\crypt::urlencode("index-light.php?ploopi_op=directory_contact_getphoto&directory_contact_id={$row['id']}");
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
                        'ALTERNATE_STYLE' => $c%2,
                        'LINK' => ploopi\crypt::urlencode('index.php?'.implode('&',$arrRequest))
                    )
                );

            }

        }
    break;

    /**
     * Affichage de l'annuaire complet
     */
    case 'full':
        // Reset de la recherche
        unset($_SESSION['directory']['tpl_search']);

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
                        'LABEL' => isset($arrDirectoryHeadings['list'][$intId]) ? ploopi\str::htmlentities($arrDirectoryHeadings['list'][$intId]['label']) : '',
                        'LINK' => ploopi\crypt::urlencode('index.php?'.implode('&',$arrRequest))
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
        // Reset de la recherche
        unset($_SESSION['directory']['tpl_search']);

        // Récupération des rubriques
        $arrDirectoryHeadings = directory_getheadings();

        $template_body->assign_block_vars('directory_switch_organigram', array());

        directory_template_display_organigram($template_body, $arrDirectoryHeadings);
    break;

    /**
     * Affichage des numéros abrégés
     */
    case 'speeddialing':
        // Reset de la recherche
        unset($_SESSION['directory']['tpl_search']);

        $template_body->assign_block_vars('directory_switch_speeddialing', array());

        ploopi\db::get()->query("
            SELECT      *
            FROM        ploopi_mod_directory_speeddialing
            ORDER BY    heading, label
        ");

        $strHeading = null;

        while ($row = ploopi\db::get()->fetchrow())
        {
            if ($row['heading'] != $strHeading) // Nouvelle rubrique
            {
                $strHeading = $row['heading'];

                $template_body->assign_block_vars('directory_switch_speeddialing.heading',
                    array(
                        'ID' => urlencode(ploopi\str::convertaccents(strtolower(strtr(trim($strHeading), _PLOOPI_INDEXATION_WORDSEPARATORS, str_pad('', strlen(_PLOOPI_INDEXATION_WORDSEPARATORS), '-'))))),
                        'LABEL' => ploopi\str::htmlentities($strHeading)
                    )
                );

            }

            $template_body->assign_block_vars('directory_switch_speeddialing.heading.number',
                array(
                    'LABEL' => ploopi\str::htmlentities($row['label']),
                    'NUMBER' => ploopi\str::htmlentities($row['number']),
                    'SHORTNUMBER' => ploopi\str::htmlentities($row['shortnumber'])
                )
            );
        }

    break;
}

?>
