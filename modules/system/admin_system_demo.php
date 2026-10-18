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
 * Système › Modules : génération du site de démonstration (ploopi\system\demo).
 *
 * Proposée sur une installation vierge seulement ; ploopi\system\demo::generate()
 * le vérifie à nouveau et refuse sinon.
 *
 * @package system
 * @subpackage system
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Génération (op=demo), puis redirection vers le compte rendu (op=demo_done) avec
 * reloadsession : la session est rechargée, le menu montre aussitôt les modules
 * installés, et recharger la page ne relance pas la génération.
 */
if ($_REQUEST['op'] == 'demo')
{
    ini_set('max_execution_time', 0);

    $intDemoWorkspace = ploopi\system\demo::generate($arrDemoRapport);
    $_SESSION['ploopi']['system_demo'] = array('workspace' => $intDemoWorkspace, 'rapport' => $arrDemoRapport);

    ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?op=demo_done&reloadsession'), false);
}

if (empty($_SESSION['ploopi']['system_demo'])) ploopi\output::redirect(ploopi\crypt::urlencode('admin.php?sysToolbarItem=install'), false);
$intDemoWorkspace = (int) $_SESSION['ploopi']['system_demo']['workspace'];
$arrDemoRapport = $_SESSION['ploopi']['system_demo']['rapport'];

// Le site ouvre dans l'espace qui le porte ; la session doit connaître le nouveau module
$strDemoContinue = ploopi\crypt::urlencode('admin.php?sysToolbarItem=install');
$strDemoSite = $intDemoWorkspace ? 'index.php?reloadsession' : '';

if (ploopi\skin::get()->version() >= 4)
{
    echo ploopi\system\ui::pagehead(_SYSTEM_SYSMOD_DEMO_DONE, _SYSTEM_SYSMOD_REPORT_DESC);
    echo ploopi\system\ui::card_open(_SYSTEM_LABEL_INSTALLREPORT, 'list-check');
    echo ploopi\system\ui::report($arrDemoRapport);
    $strActions = ploopi\system\ui::button(array('label' => _PLOOPI_CONTINUE, 'icon' => 'arrow-right', 'href' => $strDemoContinue));
    if ($strDemoSite) $strActions .= ' '.ploopi\system\ui::button(array('label' => _SYSTEM_SYSMOD_DEMO_SEE, 'icon' => 'external-link', 'class' => 'primary', 'href' => $strDemoSite, 'attrs' => array('target' => '_blank')));
    echo ploopi\system\ui::card_close($strActions);
    return;
}

echo ploopi\skin::get()->open_simplebloc(_SYSTEM_SYSMOD_DEMO_DONE);
?>
<ul>
    <?php
    foreach ($arrDemoRapport as $arrLigne)
    {
        ?>
        <li><?php echo ($arrLigne['res'] ? '✔' : '✘').' '.ploopi\str::htmlentities($arrLigne['operation'].' : '.$arrLigne['detail']); ?></li>
        <?php
    }
    ?>
</ul>
<div style="padding:4px;text-align:right;">
    <?php if ($strDemoSite) { ?><a href="<?php echo $strDemoSite; ?>" target="_blank"><?php echo _SYSTEM_SYSMOD_DEMO_SEE; ?></a> &nbsp; <?php } ?>
    <a href="<?php echo $strDemoContinue; ?>"><?php echo _PLOOPI_CONTINUE; ?></a>
</div>
<?php echo ploopi\skin::get()->close_simplebloc(); ?>
