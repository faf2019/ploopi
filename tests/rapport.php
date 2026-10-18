<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Rapports d'un passage de la suite : junit.xml et rapport.html.
 *
 *   php tests/rapport.php tests/resultats/<version de PHP>
 *
 * Lit dans ce dossier ce que les étapes y ont laissé, chacune facultative :
 *   controles.jsonl        un contrôle par ligne (T::chk / T::skip, tests/bootstrap.php)
 *   suites.jsonl           une suite par ligne : code retour, durée, interruption (tests/run.sh)
 *   sorties/<suite>.txt    sortie de chaque suite, sans couleurs (tests/run.sh)
 *   navigateur/            parcours navigateur : resultats.json et captures (tests/navigateur/)
 *   journaux/              journaux de l'instance, conservés au démontage (tests/instance.sh)
 *
 * rapport.html est autonome (styles en ligne, aucune ressource extérieure) ; les
 * captures du parcours y sont liées relativement : garder le dossier entier.
 *
 * @package ploopi
 * @subpackage tests
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

if ($argc < 2 || !is_dir($argv[1])) { fwrite(STDERR, "Usage : php tests/rapport.php <dossier de résultats>\n"); exit(2); }
$strDossier = rtrim($argv[1], '/');

/**
 * Lit un fichier JSON Lines.
 *
 * @param string $strFichier chemin
 * @return array lignes décodées
 */
function lire_jsonl($strFichier)
{
    $arrLignes = array();
    if (!is_file($strFichier)) return $arrLignes;
    foreach (file($strFichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $strLigne)
    {
        $arrLigne = json_decode($strLigne, true);
        if (is_array($arrLigne)) $arrLignes[] = $arrLigne;
    }
    return $arrLignes;
}

function h($str) { return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function x($str) { return htmlspecialchars(preg_replace('/[^\x09\x0A\x0D\x20-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', (string)$str), ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8'); }

$arrControles = lire_jsonl("{$strDossier}/controles.jsonl");
$arrSuitesInfo = array();
foreach (lire_jsonl("{$strDossier}/suites.jsonl") as $arrSuite) $arrSuitesInfo[$arrSuite['suite']] = $arrSuite;
$arrNavigateur = is_file("{$strDossier}/navigateur/resultats.json") ? json_decode(file_get_contents("{$strDossier}/navigateur/resultats.json"), true) : null;
$arrJournaux = is_dir("{$strDossier}/journaux") ? glob("{$strDossier}/journaux/*") : array();
$strMeta = is_file("{$strDossier}/passage.json") ? file_get_contents("{$strDossier}/passage.json") : '';
$arrMeta = $strMeta ? json_decode($strMeta, true) : array();

// Contrôles regroupés par suite, dans l'ordre d'exécution
$arrSuites = array();
foreach (array_keys($arrSuitesInfo) as $strSuite) $arrSuites[$strSuite] = array();
foreach ($arrControles as $arrC) $arrSuites[$arrC['suite']][] = $arrC;

$arrTotal = array('ok' => 0, 'ko' => 0, 'skip' => 0, 'interrompues' => 0);
$arrParSuite = array();
foreach ($arrSuites as $strSuite => $arrListe)
{
    $arrN = array('ok' => 0, 'ko' => 0, 'skip' => 0);
    foreach ($arrListe as $arrC) $arrN[$arrC['statut']]++;
    $arrN['interrompue'] = !empty($arrSuitesInfo[$strSuite]['interrompue']);
    $arrN['duree'] = $arrSuitesInfo[$strSuite]['duree'] ?? 0;
    $arrParSuite[$strSuite] = $arrN;
    foreach (array('ok', 'ko', 'skip') as $k) $arrTotal[$k] += $arrN[$k];
    if ($arrN['interrompue']) $arrTotal['interrompues']++;
}

$arrEcransNav = array();
if ($arrNavigateur) foreach ($arrNavigateur['passes'] as $arrPasse) foreach ($arrPasse['ecrans'] as $arrE) $arrEcransNav[] = $arrE + array('passe' => $arrPasse['nom']);
$intNavKo = count(array_filter($arrEcransNav, function($e) { return !empty($e['problemes']); }));

$strVersion = $arrMeta['php'] ?? basename($strDossier);
$booVert = $arrTotal['ko'] === 0 && $arrTotal['interrompues'] === 0 && $intNavKo === 0;

/* ------------------------------------------------------------------ */
/* JUnit                                                              */

$intEchecsJ = $arrTotal['ko'] + $intNavKo;
$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$xml .= sprintf('<testsuites name="Ploopi (PHP %s)" tests="%d" failures="%d" errors="%d" skipped="%d">'."\n",
    x($strVersion), count($arrControles) + count($arrEcransNav) + $arrTotal['interrompues'], $intEchecsJ, $arrTotal['interrompues'], $arrTotal['skip']);
foreach ($arrSuites as $strSuite => $arrListe)
{
    $arrN = $arrParSuite[$strSuite];
    $xml .= sprintf('  <testsuite name="%s" tests="%d" failures="%d" errors="%d" skipped="%d" time="%.3f">'."\n",
        x($strSuite), count($arrListe) + ($arrN['interrompue'] ? 1 : 0), $arrN['ko'], $arrN['interrompue'] ? 1 : 0, $arrN['skip'], $arrN['duree']);
    foreach ($arrListe as $arrC)
    {
        $xml .= sprintf('    <testcase classname="%s" name="%s"', x($strSuite.($arrC['section'] !== '' ? '.'.$arrC['section'] : '')), x($arrC['label']));
        if ($arrC['statut'] === 'ko') $xml .= sprintf(">\n      <failure message=\"%s\">%s</failure>\n    </testcase>\n", x($arrC['detail']), x($arrC['detail']));
        elseif ($arrC['statut'] === 'skip') $xml .= sprintf(">\n      <skipped message=\"%s\"/>\n    </testcase>\n", x($arrC['detail']));
        else $xml .= "/>\n";
    }
    if ($arrN['interrompue'])
    {
        $strSortie = @file_get_contents("{$strDossier}/sorties/{$strSuite}.txt");
        $xml .= sprintf('    <testcase classname="%s" name="suite interrompue avant son bilan">'."\n".'      <error message="code %d">%s</error>'."\n    </testcase>\n",
            x($strSuite), $arrSuitesInfo[$strSuite]['rc'] ?? 0, x(substr((string)$strSortie, -4000)));
    }
    $xml .= "  </testsuite>\n";
}
if ($arrNavigateur)
{
    $xml .= sprintf('  <testsuite name="navigateur" tests="%d" failures="%d" errors="0" skipped="0">'."\n", count($arrEcransNav), $intNavKo);
    foreach ($arrEcransNav as $arrE)
    {
        $xml .= sprintf('    <testcase classname="navigateur.%s" name="%s"', x($arrE['passe']), x($arrE['libelle']));
        if ($arrE['problemes']) $xml .= sprintf(">\n      <failure message=\"%s\">%s</failure>\n    </testcase>\n", x($arrE['problemes'][0]), x(implode("\n", $arrE['problemes'])));
        else $xml .= "/>\n";
    }
    $xml .= "  </testsuite>\n";
}
$xml .= "</testsuites>\n";
file_put_contents("{$strDossier}/junit.xml", $xml);

/* ------------------------------------------------------------------ */
/* HTML                                                               */

ob_start();
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tests Ploopi — PHP <?php echo h($strVersion); ?></title>
<style>
:root { --fond:#f6f7f9; --carte:#fff; --texte:#1d232b; --doux:#5b6573; --bord:#dde1e6; --ok:#1f7a3f; --ko:#b42318; --skip:#9a6700; }
@media (prefers-color-scheme: dark) { :root { --fond:#14181d; --carte:#1d232b; --texte:#e6e9ed; --doux:#9aa4b1; --bord:#323a45; --ok:#4cc27a; --ko:#ff7b72; --skip:#e3b341; } }
* { box-sizing: border-box; }
body { margin:0; padding:24px 16px; font:15px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif; background:var(--fond); color:var(--texte); }
main { max-width:1200px; margin:0 auto; }
h1 { font-size:1.6em; margin:0 0 4px; } h2 { margin:32px 0 12px; font-size:1.25em; }
.doux { color:var(--doux); }
.carte { background:var(--carte); border:1px solid var(--bord); border-radius:10px; padding:16px; margin:12px 0; overflow-x:auto; }
.bilan { display:flex; flex-wrap:wrap; gap:12px; }
.bilan div { background:var(--carte); border:1px solid var(--bord); border-radius:10px; padding:12px 18px; min-width:130px; }
.bilan strong { display:block; font-size:1.8em; }
.ok { color:var(--ok); } .ko { color:var(--ko); } .skip { color:var(--skip); }
.etat { display:inline-block; padding:2px 10px; border-radius:99px; font-weight:600; color:#fff; background:var(--ok); }
.etat.rouge { background:var(--ko); }
table { border-collapse:collapse; width:100%; } th, td { text-align:left; padding:6px 10px; border-bottom:1px solid var(--bord); vertical-align:top; }
th { color:var(--doux); font-weight:600; } th.n, td.n { text-align:right; font-variant-numeric:tabular-nums; }
pre { white-space:pre-wrap; word-break:break-word; font-size:13px; margin:6px 0 0; }
details summary { cursor:pointer; }
.galerie { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px; }
.galerie figure { margin:0; background:var(--carte); border:1px solid var(--bord); border-radius:8px; overflow:hidden; }
.galerie figure.probleme { border:2px solid var(--ko); }
.galerie img { display:block; width:100%; height:150px; object-fit:cover; object-position:top; background:var(--bord); }
.galerie figcaption { padding:6px 8px; font-size:12px; }
.galerie ul { margin:4px 0 0; padding-left:16px; color:var(--ko); }
label.filtre { display:inline-flex; gap:6px; align-items:center; margin:4px 0; }
body.seulement-problemes .galerie figure:not(.probleme) { display:none; }
</style>
</head>
<body>
<main>
<h1>Tests Ploopi — PHP <?php echo h($strVersion); ?> <span class="etat<?php echo $booVert ? '' : ' rouge'; ?>"><?php echo $booVert ? 'vert' : 'rouge'; ?></span></h1>
<p class="doux"><?php
    echo h(date('Y-m-d H:i', filemtime(is_file("{$strDossier}/controles.jsonl") ? "{$strDossier}/controles.jsonl" : $strDossier)));
    foreach (array('php_complet' => 'PHP', 'base' => 'Base', 'commit' => 'Commit') as $k => $l) if (!empty($arrMeta[$k])) echo ' · '.h($l).' '.h($arrMeta[$k]);
?></p>

<div class="bilan">
    <div><span class="doux">Contrôles réussis</span><strong class="ok"><?php echo $arrTotal['ok']; ?></strong></div>
    <div><span class="doux">Échecs</span><strong class="<?php echo $arrTotal['ko'] ? 'ko' : ''; ?>"><?php echo $arrTotal['ko']; ?></strong></div>
    <div><span class="doux">Suites interrompues</span><strong class="<?php echo $arrTotal['interrompues'] ? 'ko' : ''; ?>"><?php echo $arrTotal['interrompues']; ?></strong></div>
    <div><span class="doux">Non évalués</span><strong class="<?php echo $arrTotal['skip'] ? 'skip' : ''; ?>"><?php echo $arrTotal['skip']; ?></strong></div>
    <?php if ($arrNavigateur) { ?><div><span class="doux">Écrans (navigateur)</span><strong><?php echo count($arrEcransNav); ?></strong><span class="<?php echo $intNavKo ? 'ko' : 'ok'; ?>"><?php echo $intNavKo; ?> avec problème</span></div><?php } ?>
</div>

<h2>Échecs</h2>
<?php
$booAucun = true;
foreach ($arrSuites as $strSuite => $arrListe)
{
    $arrKo = array_filter($arrListe, function($c) { return $c['statut'] === 'ko'; });
    if (!$arrKo && !$arrParSuite[$strSuite]['interrompue']) continue;
    $booAucun = false;
    echo '<div class="carte"><strong>'.h($strSuite).'</strong>';
    if ($arrParSuite[$strSuite]['interrompue'])
    {
        echo '<p class="ko">Suite interrompue avant son bilan (code '.h($arrSuitesInfo[$strSuite]['rc'] ?? '?').') : ses contrôles restants n\'ont pas tourné.</p>';
        echo '<pre>'.h(substr((string)@file_get_contents("{$strDossier}/sorties/{$strSuite}.txt"), -3000)).'</pre>';
    }
    if ($arrKo)
    {
        echo '<table><tr><th>Section</th><th>Contrôle</th><th>Détail</th></tr>';
        foreach ($arrKo as $c) echo '<tr><td>'.h($c['section']).'</td><td>'.h($c['label']).'</td><td><pre>'.h($c['detail']).'</pre></td></tr>';
        echo '</table>';
    }
    echo '</div>';
}
foreach ($arrEcransNav as $e)
{
    if (!$e['problemes']) continue;
    $booAucun = false;
    echo '<div class="carte"><strong>navigateur › '.h($e['passe']).'</strong> — '.h($e['libelle']).' (<a href="navigateur/'.h($e['passe']).'/'.h($e['nom']).'.png">capture</a>)<ul class="ko">';
    foreach ($e['problemes'] as $p) echo '<li>'.h($p).'</li>';
    echo '</ul></div>';
}
if ($booAucun) echo '<p class="ok">Aucun échec.</p>';
?>

<h2>Suites</h2>
<div class="carte"><table>
<tr><th>Suite</th><th class="n">OK</th><th class="n">KO</th><th class="n">Non évalués</th><th class="n">Durée</th><th>Sortie</th></tr>
<?php foreach ($arrParSuite as $strSuite => $n) { ?>
<tr><td><?php echo h($strSuite); ?><?php if ($n['interrompue']) echo ' <span class="ko">(interrompue)</span>'; ?></td>
<td class="n ok"><?php echo $n['ok']; ?></td><td class="n <?php echo $n['ko'] ? 'ko' : ''; ?>"><?php echo $n['ko']; ?></td><td class="n <?php echo $n['skip'] ? 'skip' : ''; ?>"><?php echo $n['skip']; ?></td>
<td class="n"><?php echo sprintf('%.1f s', $n['duree']); ?></td><td><?php if (is_file("{$strDossier}/sorties/{$strSuite}.txt")) echo '<a href="sorties/'.h($strSuite).'.txt">texte</a>'; ?></td></tr>
<?php } ?>
</table></div>

<?php if ($arrTotal['skip']) { ?>
<h2>Non évalués</h2>
<details class="carte"><summary><?php echo $arrTotal['skip']; ?> contrôle(s) non évalué(s), faute d'un prérequis</summary><table>
<tr><th>Suite</th><th>Contrôle</th><th>Raison</th></tr>
<?php foreach ($arrControles as $c) if ($c['statut'] === 'skip') echo '<tr><td>'.h($c['suite']).'</td><td>'.h($c['label']).'</td><td>'.h($c['detail']).'</td></tr>'; ?>
</table></details>
<?php } ?>

<?php if ($arrNavigateur) { ?>
<h2>Parcours navigateur</h2>
<label class="filtre"><input type="checkbox" id="filtre"> Écrans avec problème seulement</label>
<?php foreach ($arrNavigateur['passes'] as $arrPasse) { ?>
<h3><?php echo h($arrPasse['nom']); ?> <span class="doux">(<?php echo h($arrPasse['largeur']); ?> px, thème <?php echo $arrPasse['theme'] === 'dark' ? 'sombre' : 'clair'; ?>)</span></h3>
<div class="galerie">
<?php foreach ($arrPasse['ecrans'] as $e) { $strImg = 'navigateur/'.$arrPasse['nom'].'/'.$e['nom'].'.png'; ?>
<figure class="<?php echo $e['problemes'] ? 'probleme' : ''; ?>"><a href="<?php echo h($strImg); ?>"><img loading="lazy" src="<?php echo h($strImg); ?>" alt="<?php echo h($e['libelle']); ?>"></a>
<figcaption><?php echo h($e['libelle']); ?><?php if ($e['problemes']) { echo '<ul>'; foreach ($e['problemes'] as $p) echo '<li>'.h($p).'</li>'; echo '</ul>'; } ?></figcaption></figure>
<?php } ?>
</div>
<?php } ?>
<script>
document.getElementById('filtre').addEventListener('change', function () { document.body.classList.toggle('seulement-problemes', this.checked); });
</script>
<?php } ?>

<?php if ($arrJournaux) { ?>
<h2>Journaux</h2>
<div class="carte"><ul>
<?php foreach ($arrJournaux as $strJ) echo '<li><a href="journaux/'.h(basename($strJ)).'">'.h(basename($strJ)).'</a> <span class="doux">('.number_format(filesize($strJ), 0, ',', ' ').' octets)</span></li>'; ?>
</ul>
<?php
$strErreurs = "{$strDossier}/journaux/ploopi_erreurs.log";
if (is_file($strErreurs) && filesize($strErreurs) > 0) echo '<details><summary>Journal d\'erreurs de Ploopi (fin)</summary><pre>'.h(substr(file_get_contents($strErreurs), -6000)).'</pre></details>';
?>
</div>
<?php } ?>
</main>
</body>
</html>
<?php
file_put_contents("{$strDossier}/rapport.html", ob_get_clean());
echo "Rapports : {$strDossier}/junit.xml, {$strDossier}/rapport.html\n";
exit(0);
