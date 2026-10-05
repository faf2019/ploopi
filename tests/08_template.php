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
 * Tests du moteur de template maison (lib/template/template.php).
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require_once __DIR__.'/bootstrap.php';
amorcer_socle();

include_once _PLOOPI_TESTS_ROOT.'/lib/template/template.php';

$strFixtures = __DIR__.'/fixtures/template';

/**
 * Rend un template et retourne sa sortie (pparse() écrit sur la sortie standard).
 *
 * @param string $strFichier nom du fichier de template
 * @param array $arrVars variables simples
 * @param array $arrBlocs blocs, sous la forme array(array('nom' => ..., 'vars' => array()))
 * @return string sortie produite
 */
function rendre($strFichier, $arrVars = array(), $arrBlocs = array())
{
    global $strFixtures;

    $objTpl = new Template($strFixtures);
    $objTpl->set_filenames(array('body' => $strFichier));
    if (!empty($arrVars)) $objTpl->assign_vars($arrVars);
    foreach($arrBlocs as $arrBloc) $objTpl->assign_block_vars($arrBloc['nom'], $arrBloc['vars']);

    ob_start();
    $objTpl->pparse('body');
    return ob_get_clean();
}

T::section('substitution de variables');
$strSortie = rendre('base.tpl', array('TITRE' => 'Essai'));
T::contient('la variable est substituée', 'Titre: Essai', $strSortie);
T::contient('une variable non affectée rend une chaîne vide', 'Fin: []', $strSortie);

T::section('blocs itérés');
$strSortie = rendre(
    'base.tpl',
    array('TITRE' => 'Liste'),
    array(
        array('nom' => 'article',     'vars' => array('NOM' => 'Premier', 'ID' => 1)),
        array('nom' => 'article.tag', 'vars' => array('LABEL' => 'étiquette')),
        array('nom' => 'article',     'vars' => array('NOM' => 'Second',  'ID' => 2))
    )
);
T::contient('première itération', '- Premier (1)', $strSortie);
T::contient('seconde itération', '- Second (2)', $strSortie);
T::contient('bloc imbriqué rattaché à son parent', '* étiquette', $strSortie);
T::eq('le bloc imbriqué n\'apparaît qu\'une fois', 1, substr_count($strSortie, '* étiquette'));
T::contientpas('le balisage de bloc ne subsiste pas dans la sortie', 'BEGIN', $strSortie);

$strVide = rendre('base.tpl', array('TITRE' => 'Vide'));
T::contientpas('un bloc sans itération ne produit rien', '- ', $strVide);

T::section('échappement');
/**
 * Le moteur insère les valeurs TELLES QUELLES : il n'échappe rien. C'est à
 * l'appelant d'appliquer str::htmlentities() avant assign_var (CLAUDE.md §8.2).
 * Ce test fixe le contrat pour éviter qu'on le suppose protecteur.
 */
T::contient(
    'les valeurs sont insérées sans échappement',
    '<script>alert(1)</script>',
    rendre('base.tpl', array('TITRE' => '<script>alert(1)</script>'))
);

T::section('<!-- INCLUDE --> n\'est pas géré');
/**
 * CLAUDE.md §10 mentionne une directive <!-- INCLUDE fichier.tpl -->. Le moteur ne
 * l'implémente pas (aucune occurrence dans lib/template/template.php, aucun .tpl du
 * dépôt ne l'utilise) : le commentaire ressort tel quel dans la page.
 * La composition se fait par assign_var_from_handle().
 */
$strSortie = rendre('inclusion.tpl');
T::contient('la directive est recopiée telle quelle', '<!-- INCLUDE fragment.tpl -->', $strSortie);
T::contientpas('le fragment n\'est pas inclus', '[fragment]', $strSortie);

T::section('assign_var_from_handle');
$objTpl = new Template($strFixtures);
$objTpl->set_filenames(array('body' => 'base.tpl', 'frag' => 'fragment.tpl'));
$objTpl->assign_var_from_handle('TITRE', 'frag');
ob_start();
$objTpl->pparse('body');
$strSortie = ob_get_clean();
T::contient('un template rendu peut alimenter une variable d\'un autre', '[fragment]', $strSortie);

T::section('les templates du dépôt se compilent');
$arrTemplates = glob(_PLOOPI_TESTS_ROOT.'/templates/*/*/*.tpl');
T::chk('des templates sont trouvés', sizeof($arrTemplates) > 0, sizeof($arrTemplates).' fichier(s)');

$arrEnErreur = array();
foreach($arrTemplates as $strTemplate)
{
    $objTpl = new Template(dirname($strTemplate));
    $objTpl->set_filenames(array('body' => basename($strTemplate)));

    ob_start();
    $booOk = @$objTpl->pparse('body');
    ob_end_clean();

    if (!$booOk) $arrEnErreur[] = substr($strTemplate, strlen(_PLOOPI_TESTS_ROOT) + 1);
}
T::chk('tous les templates du dépôt se compilent et se rendent', empty($arrEnErreur), implode(', ', $arrEnErreur));

T::bilan('08_template');
