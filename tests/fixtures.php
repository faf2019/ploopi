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
 * Jeux d'essai de la suite : documents de la GED couvrant les cas de contrôle d'accès et de
 * rendu. Les lignes sont écrites directement en base afin de pouvoir fabriquer des cas
 * qu'aucun parcours applicatif ne produit, comme un document d'un espace inaccessible.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Crée un document en base et sur disque
 *
 * @return array identifiant et empreinte md5 du document
 */

function fixture_document($strNom, $strExtension, $strContenu, $intIdModule, $intIdWorkspace, $strTimestamp)
{
    $objDb = db();

    $objDb->query(sprintf(
        "INSERT INTO ploopi_documents_file
            (md5id, name, label, description, ref, extension, size,
             timestp_file, timestp_create, timestp_modify,
             id_folder, id_user, id_user_modify, id_workspace, id_module, id_record, id_object)
         VALUES ('', '%s', '%s', '', '', '%s', %d, '%s', '%s', '%s', 0, 2, 2, %d, %d, '1', 1)",
        $objDb->real_escape_string($strNom),
        $objDb->real_escape_string($strNom),
        $objDb->real_escape_string($strExtension),
        strlen($strContenu),
        $strTimestamp, $strTimestamp, $strTimestamp,
        (int)$intIdWorkspace, (int)$intIdModule
    ));

    $intId = $objDb->insert_id;
    // empreinte de test : la génération réelle est aléatoire (suite 30), les
    // fixtures insèrent directement en base et n'ont qu'à être uniques
    $strMd5 = bin2hex(random_bytes(16));
    $objDb->query("UPDATE ploopi_documents_file SET md5id = '{$strMd5}' WHERE id = {$intId}");

    $strDossier = PLOOPI_ROOT.'/data/documents/'.substr($strTimestamp, 0, 8);
    if (!is_dir($strDossier)) mkdir($strDossier, 0777, true);
    file_put_contents("{$strDossier}/{$intId}.{$strExtension}", $strContenu);

    return array('id' => $intId, 'md5' => $strMd5);
}

/**
 * Reconstruit le jeu de documents et renvoie leurs empreintes
 *
 * @return array
 */

function fixtures_documents()
{
    db()->query('DELETE FROM ploopi_documents_file');

    $strTs = date('YmdHis');

    return array(
        // Module system, espace de l'administrateur : doit être accessible
        'system'    => fixture_document('note-system.txt', 'txt', 'contenu system', 1, 2, $strTs),
        // Module et espace inconnus de l'utilisateur : ne doit jamais être servi
        'etranger'  => fixture_document('secret-autre-espace.txt', 'txt', 'contenu etranger', 42, 99, $strTs),
        // Page HTML : l'affichage en ligne doit être refusé
        'html'      => fixture_document('xss.html', 'html', '<script>alert(document.domain)</script>', 1, 2, $strTs),
        // Image : l'affichage en ligne doit rester possible
        'image'     => fixture_document('apercu.png', 'png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ), 1, 2, $strTs),
    );
}

/**
 * Supprime les documents du jeu d'essai
 */

function fixtures_nettoyer()
{
    db()->query('DELETE FROM ploopi_documents_file');
    db()->query("DELETE FROM ploopi_user WHERE login = 'cible'");
}
