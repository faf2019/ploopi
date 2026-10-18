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
 * Suite 20 — module forms : les graphiques sont-ils rendus ?
 *
 * Les graphiques étaient dessinés par JpGraph : embarqué en 3.0.7 (2010), il appelait des
 * fonctions supprimées en PHP 8 ; installé par Composer (mitoteam/jpgraph), sa licence
 * QPL est incompatible avec la GPL. formsChart (GD seul) le remplace.
 *
 * La suite demande à l'administrateur chaque type de graphique sur un formulaire réel et
 * vérifie qu'une image PNG valide, aux dimensions demandées, est renvoyée.
 *
 * @package forms
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';
exiger_instance();

amorcer_socle();

$strModule = PLOOPI_ROOT.'/modules/forms';
$booDeploye = false;

/**
 * Retire le module et ses données. Appelé quoi qu'il arrive.
 */
function graph_nettoyer()
{
    global $strModule, $booDeploye;

    $objRs = db()->query("SHOW TABLES LIKE 'form\\_%'");
    while ($objRs && $arrLigne = $objRs->fetch_row()) db()->query("DROP TABLE IF EXISTS `{$arrLigne[0]}`");

    $arrRequetes = array(
        "DELETE FROM `ploopi_mod_forms_form`",
        "DELETE FROM `ploopi_mod_forms_field`",
        "DELETE FROM `ploopi_mod_forms_graphic`",
        "DELETE FROM ploopi_module_workspace WHERE id_module IN (SELECT id FROM ploopi_module WHERE label = 'forms')",
        "DELETE FROM ploopi_module WHERE label = 'forms'",
        "DELETE FROM ploopi_module_type WHERE label = 'forms'"
    );

    foreach ($arrRequetes as $strRequete)
    {
        try { db()->query($strRequete); } catch (Throwable $objErreur) { }
    }

    if ($booDeploye && is_dir($strModule)) exec('rm -rf '.escapeshellarg($strModule));
}

register_shutdown_function('graph_nettoyer');
graph_nettoyer();

T::section('Prérequis');
T::vrai('le rendu est assuré par formsChart', is_file(PLOOPI_ROOT.'/install/forms/files/classes/formsChart.php'));
T::vrai('plus de copie embarquée de JpGraph dans le module', !is_dir(PLOOPI_ROOT.'/install/forms/files/jpgraph'));
T::contientpas('ni de dépendance Composer à JpGraph (QPL, incompatible GPL)', 'jpgraph', strtolower(file_get_contents(PLOOPI_ROOT.'/composer.json')));
T::contientpas('formsGraphic n\'appelle plus JpGraph', 'MtJpGraph', file_get_contents(PLOOPI_ROOT.'/install/forms/files/classes/formsGraphic.php'));

if (!is_dir($strModule))
{
    exec('cp -r '.escapeshellarg(PLOOPI_ROOT.'/install/forms/files').' '.escapeshellarg($strModule), $arrSortie, $intRc);
    $booDeploye = ($intRc === 0);
}
foreach (preg_split('/;\s*\n/', file_get_contents(PLOOPI_ROOT.'/install/forms/structure.sql')) as $strRequete)
{
    if (trim($strRequete) === '') continue;
    try { db()->query($strRequete); } catch (Throwable $objErreur) { }
}
db()->query("INSERT INTO ploopi_module_type (label, system, description, version, author, date)
             VALUES ('forms', 0, 'Formulaires', '3.8.1', 'Ovensia', '".date('YmdHis')."')");
$intType = db()->insert_id;
db()->query("INSERT INTO ploopi_module (label, id_module_type, id_workspace, active, visible)
             VALUES ('forms', {$intType}, 2, 1, 1)");
$intModule = db()->insert_id;
db()->query("INSERT INTO ploopi_module_workspace (id_module, id_workspace, position, blockposition)
             VALUES ({$intModule}, 2, 1, '')");
T::vrai('module forms déployé', is_dir($strModule.'/classes') && $intModule > 0);

/**
 * Crée un formulaire à un champ texte, sa table de données et quelques réponses.
 *
 * @param string $strLabel libellé
 * @param int $intModule instance
 * @param array $arrValeurs [valeur => nombre de jours dans le passé]
 * @return array [identifiant du formulaire, identifiant du champ]
 */
// option_displaydate à 0 : les graphiques chronologiques (barres, courbes, radar) n'avaient
// des données que si l'option « afficher la date » était cochée — la requête ne
// sélectionnait la date de réponse qu'à cette condition.
function graph_formulaire($strLabel, $intModule, $arrValeurs)
{
    db()->query(sprintf(
        "INSERT INTO ploopi_mod_forms_form (label, description, email_from, email, typeform, nbline, option_displaydate,
                                            id_user, id_workspace, id_module)
         VALUES ('%s', '', '', '', 'app', 25, 0, 2, 2, %d)", db()->real_escape_string($strLabel), (int)$intModule
    ));
    $intForm = db()->insert_id;

    db()->query("INSERT INTO ploopi_mod_forms_field (id_form, name, fieldname, type, format, `values`, formula, description,
                                                     xhtmlcontent, xhtmlcontent_cleaned, position, style_field, style_form,
                                                     defaultvalue, option_formview, option_arrayview, option_exportview)
                 VALUES ({$intForm}, 'Couleur', 'champ_1', 'text', 'string', '', '', '', '', '', 1, '', '', '', 1, 1, 1)");
    $intField = db()->insert_id;

    db()->query("CREATE TABLE `form_{$intModule}_{$intForm}` (
                    `#id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `date_validation` BIGINT(14) DEFAULT 0, `ip` VARCHAR(16) DEFAULT '',
                    `user_id` INT(10) UNSIGNED DEFAULT 0, `user_login` VARCHAR(255) DEFAULT '',
                    `user_firstname` VARCHAR(255) DEFAULT '', `user_lastname` VARCHAR(255) DEFAULT '',
                    `workspace_id` INT(10) UNSIGNED DEFAULT 0, `workspace_label` VARCHAR(255) DEFAULT '',
                    `workspace_code` VARCHAR(255) DEFAULT '', `champ_1` VARCHAR(255) DEFAULT NULL
                 ) ENGINE=MyISAM");

    foreach ($arrValeurs as $arrValeur)
    {
        list($strValeur, $intJours) = $arrValeur;
        db()->query("INSERT INTO `form_{$intModule}_{$intForm}` (date_validation, user_id, workspace_id, champ_1)
                     VALUES (".date('YmdHis', strtotime("-{$intJours} days")).", 2, 2, '".db()->real_escape_string($strValeur)."')");
    }

    return array($intForm, $intField);
}

list($intForm, $intField) = graph_formulaire('Couleurs preferees', $intModule,
    array(array('Rouge', 1), array('Rouge', 2), array('Vert', 2), array('Bleu', 3), array('Bleu', 3), array('Bleu', 4)));
T::eq('formulaire et réponses créés', 6, (int)db()->query("SELECT COUNT(*) FROM `form_{$intModule}_{$intForm}`")->fetch_row()[0]);

/**
 * Crée un graphique sur le champ du formulaire. Couleurs : dégradé rouge → bleu pour
 * les secteurs, #336699 pour la série des autres types, remplie avec la transparence
 * par défaut du module (0.5) : sur fond blanc, rgb(153, 178, 204).
 *
 * @param int $intForm formulaire
 * @param int $intField champ représenté
 * @param string $strType type jpgraph (pie, pie3d, bar, line, radar…)
 * @return int identifiant
 */
function graph_creer($intForm, $intField, $strType)
{
    db()->query("INSERT INTO ploopi_mod_forms_graphic (id_form, label, type, line_aggregation, operation, timefield,
                                                       pie_field, pie_color1, pie_color2,
                                                       line1_field, line1_operation, line1_color, line1_legend,
                                                       filled, param_font, param_mark_type)
                 VALUES ({$intForm}, 'Graphique {$strType}', '{$strType}', 'day', 'count', 0,
                         {$intField}, '#ff0000', '#0000ff',
                         {$intField}, 'count', '#336699', 'Reponses', 1, 18, 1)");

    return db()->insert_id;
}

H::reset();
H::connexion();
$arrEnv = explode('/', H::env());
$arrEnv[1] = 2;
$arrEnv[2] = $intModule;
$arrEnv[3] = 'public';
$strEnv = implode('/', $arrEnv);
H::req('/admin.php?ploopi_env='.urlencode($strEnv));
T::vrai('administrateur connecté dans le module', substr_count($strEnv, '/') === 4, $strEnv);

/**
 * Compte les pixels d'une image proches d'une couleur, et les pixels saturés.
 *
 * L'image d'erreur du module — fond blanc, texte gris anti-aliasé — n'a aucun pixel
 * saturé : c'est ce qui la distingue d'un graphique, quel qu'il soit.
 *
 * @param resource|GdImage $resImage image
 * @param array $arrRgb couleur cherchée [r, g, b]
 * @return array [pixels proches de la couleur, pixels saturés]
 */
function graph_couleurs($resImage, $arrRgb)
{
    $intProches = $intSatures = 0;
    for ($x = 0; $x < imagesx($resImage); $x += 2)
    {
        for ($y = 0; $y < imagesy($resImage); $y += 2)
        {
            $arrPixel = imagecolorsforindex($resImage, imagecolorat($resImage, $x, $y));
            $arrPixel = array($arrPixel['red'], $arrPixel['green'], $arrPixel['blue']);
            if (max($arrPixel) - min($arrPixel) > 40) $intSatures++;
            if (abs($arrPixel[0] - $arrRgb[0]) + abs($arrPixel[1] - $arrRgb[1]) + abs($arrPixel[2] - $arrRgb[2]) < 40) $intProches++;
        }
    }

    return array($intProches, $intSatures);
}

/**
 * Demande un graphique et décode l'image renvoyée.
 *
 * @param int $intGraphique identifiant
 * @param int $intLargeur largeur demandée
 * @return array [réponse, ressource GD ou false]
 */
function graph_rendre($intGraphique, $intLargeur)
{
    global $strEnv;

    $arrRep = H::req("/index-quick.php?ploopi_op=forms_graphic_generate&forms_graphic_id={$intGraphique}&forms_graphic_width={$intLargeur}&ploopi_env=".urlencode($strEnv));
    $resImage = (substr($arrRep['body'], 0, 8) === "\x89PNG\r\n\x1a\n") ? @imagecreatefromstring($arrRep['body']) : false;

    return array($arrRep, $resImage);
}

foreach (array('pie', 'pie3d', 'bar', 'barc', 'line', 'linec', 'radar', 'radarc') as $strType)
{
    T::section("Graphique {$strType}");
    $intGraphique = graph_creer($intForm, $intField, $strType);
    list($arrRep, $resImage) = graph_rendre($intGraphique, 640);

    T::vrai('une image PNG valide est renvoyée', $resImage !== false,
        "HTTP {$arrRep['code']}, ".strlen($arrRep['body']).' octets : '.substr(preg_replace('/[^\x20-\x7e]/', '.', $arrRep['body']), 0, 120));
    T::eq('largeur demandée respectée', 640, $resImage ? imagesx($resImage) : null);
    /**
     * Une erreur de rendu produit aussi un PNG — l'image d'erreur du module — : seule
     * la présence de couleur prouve qu'un graphique a été tracé. Et la couleur
     * paramétrée doit être celle du tracé : JpGraph 4 imposait son thème par
     * défaut.
     */
    $arrRgb = in_array($strType, array('pie', 'pie3d')) ? array(255, 0, 0) : array(153, 178, 204);
    list($intProches, $intSatures) = $resImage ? graph_couleurs($resImage, $arrRgb) : array(0, 0);
    T::vrai('l\'image contient un graphique, pas un message d\'erreur', $intSatures > 200, "{$intSatures} pixels saturés");
    T::vrai('le tracé reprend la couleur paramétrée', $intProches > 20, "{$intProches} pixels proches de rgb(".implode(',', $arrRgb).')');
}

T::section('Formulaire sans réponse');
list($intFormVide, $intFieldVide) = graph_formulaire('Vide', $intModule, array());
list($arrRep, $resImage) = graph_rendre(graph_creer($intFormVide, $intFieldVide, 'pie'), 640);
T::vrai('une image d\'erreur est renvoyée, pas une page blanche', $resImage !== false, "HTTP {$arrRep['code']}");
list(, $intSatures) = $resImage ? graph_couleurs($resImage, array(0, 0, 0)) : array(0, -1);
T::eq('témoin du contrôle de couleur : l\'image d\'erreur n\'a aucun pixel saturé', 0, $intSatures);

T::section('Suppression d\'un formulaire qui porte des graphiques');
/**
 * formsForm::delete() parcourt les graphiques du formulaire : la classe formsGraphic
 * est alors utilisée hors de tout rendu, ce qui échouait sur une constante de JpGraph
 * indéfinie.
 */
H::req("/index-quick.php?ploopi_op=forms_delete&forms_id={$intForm}&ploopi_env=".urlencode($strEnv));
T::vrai('le formulaire est supprimé', db()->query("SELECT id FROM ploopi_mod_forms_form WHERE id = {$intForm}")->num_rows === 0);
T::vrai('ses graphiques aussi', db()->query("SELECT id FROM ploopi_mod_forms_graphic WHERE id_form = {$intForm}")->num_rows === 0);

exit(T::bilan('20_module_forms_graphiques'));
