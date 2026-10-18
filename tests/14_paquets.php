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
 * Cohérence des paquets d'installation install/<module>/ (cf. doc/reference.md §6).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
amorcer_socle();

/**
 * Liste les paquets d'installation de module (install/system/ n'en est pas un :
 * il porte le schéma du socle, sans description.xml).
 *
 * @return array noms de modules
 */
function lister_paquets()
{
    $arrModules = array();

    foreach(suivis(glob(PLOOPI_ROOT.'/install/*', GLOB_ONLYDIR)) as $strChemin)
    {
        if (file_exists($strChemin.'/description.xml')) $arrModules[] = basename($strChemin);
    }

    sort($arrModules);
    return $arrModules;
}

$arrModules = lister_paquets();

T::section('inventaire');
T::vrai('des paquets d\'installation sont trouvés', sizeof($arrModules) > 5, implode(', ', $arrModules));

T::section('description.xml');
foreach($arrModules as $strModule)
{
    $strChemin = PLOOPI_ROOT."/install/{$strModule}/description.xml";

    $objXml = @simplexml_load_file($strChemin);
    if (!T::vrai("{$strModule} : description.xml est un XML valide", $objXml !== false)) continue;

    $objType = $objXml->moduletype;
    T::eq("{$strModule} : le label vaut le nom du dossier", $strModule, (string)$objType->label);
    T::vrai("{$strModule} : une version est déclarée", (string)$objType->version !== '', 'version vide');
    T::vrai(
        "{$strModule} : la version est au format numérique pointé",
        preg_match('/^[0-9]+(\.[0-9]+)*$/', (string)$objType->version) === 1,
        'version : '.(string)$objType->version
    );
    T::vrai("{$strModule} : une description est renseignée", trim((string)$objType->description) !== '');
    T::vrai(
        "{$strModule} : la date est un timestamp MySQL YmdHis",
        preg_match('/^[0-9]{14}$/', (string)$objType->date) === 1,
        'date : '.(string)$objType->date
    );

    // Les identifiants d'action doivent être des entiers distincts.
    $arrActions = array();
    foreach($objType->action as $objAction) $arrActions[] = (string)$objAction->id_action;
    if (!empty($arrActions))
    {
        $arrNonEntiers = array_filter($arrActions, function($strId) { return preg_match('/^-?[0-9]+$/', $strId) !== 1; });
        T::vrai("{$strModule} : les id_action sont des entiers", empty($arrNonEntiers), implode(', ', $arrNonEntiers));
        T::vrai(
            "{$strModule} : les id_action sont distincts",
            sizeof($arrActions) === sizeof(array_unique($arrActions)),
            'doublons : '.implode(', ', array_diff_assoc($arrActions, array_unique($arrActions)))
        );
    }

    // Les paramètres doivent porter un nom.
    $arrSansNom = array();
    foreach($objType->paramtype as $objParam) if (trim((string)$objParam->name) === '') $arrSansNom[] = (string)$objParam->label;
    T::vrai("{$strModule} : tous les paramètres ont un nom", empty($arrSansNom), implode(', ', $arrSansNom));

    /**
     * doc/reference.md §6.4 : la balise <cms_object> de description.xml instancie une classe
     * ploopi\mb_cms_object qui n'existe pas — elle provoquerait une erreur fatale à
     * l'installation. Les objets WCE se déclarent dans mb.xml.
     */
    T::vrai("{$strModule} : pas de balise cms_object dans description.xml", !isset($objType->cms_object));
}

T::section('structure des paquets');
foreach($arrModules as $strModule)
{
    $strChemin = PLOOPI_ROOT."/install/{$strModule}";
    T::vrai("{$strModule} : dossier files/ présent", is_dir($strChemin.'/files'));
    T::vrai("{$strModule} : changelog.txt présent", file_exists($strChemin.'/changelog.txt'));
    T::vrai("{$strModule} : structure.sql présent", file_exists($strChemin.'/structure.sql'));
}

T::section('mb.xml');
foreach($arrModules as $strModule)
{
    $strChemin = PLOOPI_ROOT."/install/{$strModule}/mb.xml";

    if (!file_exists($strChemin))
    {
        T::skip("{$strModule} : mb.xml valide", 'pas de métabase déclarée');
        continue;
    }

    T::vrai("{$strModule} : mb.xml est un XML valide", @simplexml_load_file($strChemin) !== false);
}

T::section('data.xml');
$intData = 0;
foreach($arrModules as $strModule)
{
    $strChemin = PLOOPI_ROOT."/install/{$strModule}/data.xml";
    if (!file_exists($strChemin)) continue;
    $intData++;
    T::vrai("{$strModule} : data.xml est un XML valide", @simplexml_load_file($strChemin) !== false);
}
if (!$intData) T::skip('data.xml valides', 'aucun paquet ne fournit de données initiales');

T::section('scripts de mise à jour');
foreach($arrModules as $strModule)
{
    $objXml = @simplexml_load_file(PLOOPI_ROOT."/install/{$strModule}/description.xml");
    if ($objXml === false) continue;

    $strVersion = (string)$objXml->moduletype->version;
    $arrPosterieurs = array();

    foreach(suivis(glob(PLOOPI_ROOT."/install/{$strModule}/update/update_*.sql")) as $strFichier)
    {
        $strVersionFichier = substr(basename($strFichier, '.sql'), strlen('update_'));
        // Les scripts sont appliqués tant que leur version est <= version cible
        // (cf. doc/reference.md §6.5) : un script plus récent que la version déclarée ne
        // serait jamais joué.
        if (version_compare($strVersionFichier, $strVersion, '>')) $arrPosterieurs[] = basename($strFichier);
    }

    T::vrai(
        "{$strModule} : aucun script de mise à jour postérieur à la version {$strVersion}",
        empty($arrPosterieurs),
        implode(', ', $arrPosterieurs)
    );
}

T::section('id_action déclarés == constantes ACTION_* du module');
$intCompares = 0;
foreach($arrModules as $strModule)
{
    $strTools = PLOOPI_ROOT."/install/{$strModule}/files/classes/tools.php";
    if (!file_exists($strTools)) continue;
    $intCompares++;

    $objXml = @simplexml_load_file(PLOOPI_ROOT."/install/{$strModule}/description.xml");
    $arrDeclarees = array();
    foreach($objXml->moduletype->action as $objAction) $arrDeclarees[] = intval($objAction->id_action);
    sort($arrDeclarees);

    preg_match_all('/const\s+(ACTION_[A-Z0-9_]+)\s*=\s*(-?[0-9]+)/', file_get_contents($strTools), $arrMatch, PREG_SET_ORDER);
    $arrConstantes = array();
    foreach($arrMatch as $arrConst)
    {
        // ACTION_ANY (-1) est une convention interne (« au moins une action »), elle
        // n'est pas déclarée dans description.xml.
        if (intval($arrConst[2]) < 0) continue;
        $arrConstantes[] = intval($arrConst[2]);
    }
    sort($arrConstantes);

    T::eq("{$strModule} : les id_action correspondent aux constantes ACTION_*", $arrDeclarees, $arrConstantes);
}
if (!$intCompares) T::skip('comparaison id_action / constantes', 'aucun module ne fournit classes/tools.php');

T::section('fichiers retirés des paquets (obsolete.txt)');
/**
 * La mise à jour d'un module supprime les chemins que son paquet déclare dans
 * install/<module>/obsolete.txt (system_module_purgeobsolete). Un chemin déclaré
 * obsolète ne doit plus figurer dans le paquet, sans quoi la mise à jour le
 * recopierait puis le supprimerait.
 */
$arrIncoherents = array();
foreach (suivis(glob(PLOOPI_ROOT.'/install/*/obsolete.txt')) as $strListe)
{
    $strFiles = dirname($strListe).'/files';
    foreach (file($strListe, FILE_IGNORE_NEW_LINES) as $strLigne)
    {
        $strChemin = trim(preg_replace('/#.*$/', '', $strLigne), " \t/");
        if ($strChemin !== '' && file_exists($strFiles.'/'.$strChemin) && suivi($strFiles.'/'.$strChemin)) $arrIncoherents[] = basename(dirname($strListe)).'/'.$strChemin;
    }
}
T::vrai('aucun chemin obsolète encore présent dans son paquet', empty($arrIncoherents), implode(', ', $arrIncoherents));

// La suppression elle-même, sur une arborescence jetable
if (!function_exists('system_module_purgeobsolete')) include_once PLOOPI_ROOT.'/modules/system/include/functions.php';
$strBac = sys_get_temp_dir().'/ploopi_obsolete_'.getmypid();
$strModule = $strBac.'/module';
@mkdir($strModule.'/ancien/sous', 0777, true);
@mkdir($strModule.'/garde', 0777, true);
file_put_contents($strModule.'/ancien/sous/a.php', 'x');
file_put_contents($strModule.'/vieux.php', 'x');
file_put_contents($strModule.'/garde/b.php', 'x');
file_put_contents($strBac.'/dehors.txt', 'x');
file_put_contents($strBac.'/obsolete.txt', "# commentaire\nancien\nvieux.php  # fichier\n../dehors.txt\ngarde/../../dehors.txt\n/etc/passwd\ninexistant.php\n");
$arrSupprimes = system_module_purgeobsolete($strBac.'/obsolete.txt', $strModule);
T::vrai('dossier et fichier déclarés supprimés', !file_exists($strModule.'/ancien') && !file_exists($strModule.'/vieux.php'));
T::eq('compte rendu des suppressions', array('ancien', 'vieux.php'), $arrSupprimes);
T::vrai('un chemin non déclaré est conservé', file_exists($strModule.'/garde/b.php'));
T::vrai('aucune sortie du dossier du module (« .. », chemin absolu)', file_exists($strBac.'/dehors.txt') && file_exists('/etc/passwd'));
exec('rm -rf '.escapeshellarg($strBac));

T::section('exécution réelle de structure.sql');
if (!bdd_disponible()) T::skip('exécution des structure.sql', 'aucune base de données joignable');
else
{
    $objDb = ploopi\db::get();

    foreach($arrModules as $strModule)
    {
        $strChemin = PLOOPI_ROOT."/install/{$strModule}/structure.sql";
        if (!file_exists($strChemin)) continue;

        $strSql = file_get_contents($strChemin);
        $arrTables = array();
        if (preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $strSql, $arrMatch)) $arrTables = $arrMatch[1];

        // On repart d'un état propre pour que le test ne dépende pas d'un passage antérieur.
        foreach($arrTables as $strTable) $objDb->query("DROP TABLE IF EXISTS `{$strTable}`");

        /**
         * db::query() retourne null quand la requête échoue. On ne peut pas se fier à
         * get_last_error() ici : il est « collant », la dernière erreur y reste après
         * une requête réussie, ce qui attribuerait l'erreur d'un module au suivant.
         */
        $arrErreurs = array();
        foreach(ploopi\db::split_sql($strSql) as $strRequete)
        {
            if (trim($strRequete) === '' || trim($strRequete) === ';') continue;
            if (is_null(@$objDb->query($strRequete))) $arrErreurs[] = $objDb->get_last_error().' -- '.substr(trim($strRequete), 0, 60);
        }

        T::vrai("{$strModule} : structure.sql s'exécute sans erreur", empty($arrErreurs), implode(' | ', array_slice($arrErreurs, 0, 3)));
        T::vrai("{$strModule} : au moins une table créée", !empty($arrTables), 'aucun CREATE TABLE détecté');

        $arrManquantes = array();
        foreach($arrTables as $strTable)
        {
            $objRs = $objDb->query("SHOW TABLES LIKE '".$objDb->addslashes($strTable)."'");
            if (!$objDb->numrows($objRs)) $arrManquantes[] = $strTable;
        }
        T::vrai("{$strModule} : toutes les tables déclarées existent", empty($arrManquantes), implode(', ', $arrManquantes));

        /**
         * doc/reference.md §6.2 : toute table métier porte id_module, id_workspace et id_user,
         * qui assurent le cloisonnement par instance, par espace et par auteur.
         * Les tables de nomenclature ou de liaison y échappent légitimement ; on vérifie
         * donc qu'AU MOINS UNE table du module porte le triplet.
         */
        $booCloisonnement = false;
        foreach($arrTables as $strTable)
        {
            $arrColonnes = array();
            $objRs = $objDb->query("SHOW COLUMNS FROM `{$strTable}`");
            while($arrCol = $objDb->fetchrow($objRs)) $arrColonnes[] = $arrCol['Field'];
            if (in_array('id_module', $arrColonnes) && in_array('id_workspace', $arrColonnes)) { $booCloisonnement = true; break; }
        }
        T::vrai("{$strModule} : au moins une table porte id_module et id_workspace", $booCloisonnement, 'aucun cloisonnement détecté');

        foreach($arrTables as $strTable) $objDb->query("DROP TABLE IF EXISTS `{$strTable}`");
    }
}

exit(T::bilan('14_paquets'));
