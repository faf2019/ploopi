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
*/

/**
 * Suite 44 — ploopi\cache (stockage en fichiers, qui remplace Cache_Lite).
 *
 * Le cache est désactivé dans l'instance de test (_PLOOPI_USE_CACHE) : la suite
 * l'active pour elle seule et travaille dans un dossier temporaire.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

require __DIR__.'/bootstrap.php';

amorcer_socle();

use ploopi\cache;

cache::init();
(new ReflectionProperty(cache::class, '_booActivated'))->setValue(null, true);

$strDossier = sys_get_temp_dir().'/ploopi_cache_'.uniqid();
mkdir($strDossier);

T::section('sortie mise en cache (start / end)');

$objCache = new cache('page', 60, $strDossier);
ob_start();
$booLu = $objCache->start();
if (!$booLu) { echo 'contenu généré'; $objCache->end(); }
T::eq('premier passage : contenu généré et affiché', 'contenu généré', ob_get_clean());
T::vrai('premier passage : rien en cache', ($booLu) === false);

ob_start();
$booLu = (new cache('page', 60, $strDossier))->start();
$strSortie = ob_get_clean();
T::vrai('second passage : lu en cache', $booLu);
T::eq('second passage : contenu affiché depuis le cache', 'contenu généré', $strSortie);
T::vrai('date de dernière modification', abs((new cache('page', 60, $strDossier))->get_lastmodified() - time()) < 5);

ob_start();
$booLu = (new cache('page', 60, $strDossier))->start(true);
ob_end_clean(); ob_end_clean();
T::vrai('régénération forcée : cache ignoré', ($booLu) === false);

T::section('expiration');
touch($strDossier.'/'.basename(glob($strDossier.'/ploopi_*')[0]), time() - 120);
clearstatcache();
ob_start();
$booLu = (new cache('page', 60, $strDossier))->start();
ob_end_clean(); ob_end_clean();
T::vrai('contenu plus vieux que sa durée de vie : ignoré', ($booLu) === false);

T::section('variables (get_var / save_var)');
$objVar = new cache('variable', 60, $strDossier);
T::vrai('variable absente', ($objVar->get_var()) === false);
$objVar->save_var(array('a' => 1, 'b' => 'é'));
T::eq('variable relue', array('a' => 1, 'b' => 'é'), (new cache('variable', 60, $strDossier))->get_var());
(new cache('objet', 60, $strDossier))->save_var(new ArrayObject(array(1)));
T::vrai('aucun objet ne sort du cache', (new cache('objet', 60, $strDossier))->get_var() instanceof __PHP_Incomplete_Class);

T::section('groupes');
$objA = new cache('x', 60, $strDossier); $objA->set_groupe('g1'); $objA->save('dans g1');
$objB = new cache('x', 60, $strDossier); $objB->set_groupe('g2'); $objB->save('dans g2');
$objA->clean();
$objA2 = new cache('x', 60, $strDossier); $objA2->set_groupe('g1');
$objB2 = new cache('x', 60, $strDossier); $objB2->set_groupe('g2');
ob_start(); $booA = $objA2->start(); ob_end_clean(); if (!$booA) ob_end_clean();
ob_start(); $booB = $objB2->start(); $strB = ob_get_clean();
T::vrai('clean() vide son groupe', ($booA) === false);
T::vrai('clean() épargne les autres groupes', $booB && $strB === 'dans g2');
T::eq('fichiers temporaires d\'écriture retirés', array(), glob($strDossier.'/*.*'));

T::section('cache désactivé');
(new ReflectionProperty(cache::class, '_booActivated'))->setValue(null, false);
$intNiveau = ob_get_level();
$booLu = (new cache('page', 60, $strDossier))->start();
T::vrai('start() sans effet', ($booLu) === false);
T::eq('aucune capture de sortie', $intNiveau, ob_get_level());
T::vrai('get_var() sans effet', (new cache('variable', 60, $strDossier))->get_var() === false);

array_map('unlink', glob($strDossier.'/*'));
rmdir($strDossier);

T::vrai('Cache_Lite n\'est plus une dépendance', strpos(file_get_contents(PLOOPI_ROOT.'/composer.json'), 'cache_lite') === false);

exit(T::bilan('44_cache'));
