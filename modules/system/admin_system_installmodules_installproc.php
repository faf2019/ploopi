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
 * Procédure d'installation d'un module
 *
 * @package system
 * @subpackage system
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Inclusion des parsers XML
 */
include_once './modules/system/xmlparser_mod.php';
include_once './modules/system/xmlparser_mb.php';
// La procédure elle-même : ploopi\system\moduleinstaller::install()

if (empty($_GET['installmoduletype']) || !preg_match('@^([a-z0-9_\-])+$@i', $_GET['installmoduletype'])) ploopi\output::redirect('admin.php');

ini_set('max_execution_time', 0);

global $idmoduletype;
$idmoduletype = -1;

// Gabarits de génération 4 : compte rendu dans une carte
$booSystemP4 = ploopi\skin::get()->version() >= 4;

if ($booSystemP4)
{
    echo ploopi\system\ui::pagehead(str_replace('<MODULE>', $_GET['installmoduletype'], _SYSTEM_SYSMOD_INSTALL_TITLE), _SYSTEM_SYSMOD_REPORT_DESC);
    echo ploopi\system\ui::card_open(_SYSTEM_LABEL_INSTALLREPORT, 'list-check');
}
else echo ploopi\skin::get()->open_simplebloc(_SYSTEM_LABEL_INSTALLREPORT);
?>

<?php
$select = "SELECT * FROM ploopi_module_type WHERE label = '".ploopi\db::get()->addslashes($_GET['installmoduletype'])."'";
ploopi\db::get()->query($select);
if (ploopi\db::get()->numrows())
{
    ?>
    <?php
    if ($booSystemP4) echo ploopi\system\ui::callout('warning', ploopi\str::htmlentities('Module déjà installé !'));
    else
    {
        ?>
    <div style="padding:4px;text-align:center;font-weight:bold;color:#a60000;">Module déjà installé !</div>
        <?php
    }
    ?>
    <?php
}
else
{
    ploopi\system\moduleinstaller::install($_GET['installmoduletype'], $rapport);

    $columns = array();
    $values = array();

    $columns['left']['operation']   = array('label' => 'Opération', 'width' => '250', 'options' => array('sort' => true));
    $columns['auto']['detail']      = array('label' => 'Détail', 'options' => array('sort' => true));
    $columns['right']['result']     = array('label' => 'Etat', 'width' => '60', 'options' => array('sort' => true));

    $c = 0;

    foreach($rapport as $op_detail)
    {
        $bullet = ($op_detail['res']) ? 'green' : 'red';
        $values[$c]['values']['operation'] = array('label' => ploopi\str::htmlentities($op_detail['operation']));
        $values[$c]['values']['detail'] = array('label' => ploopi\str::htmlentities($op_detail['detail']));
        $values[$c]['values']['result'] = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/p_{$bullet}.png\" />");

        $c++;
    }

    if ($booSystemP4) echo ploopi\system\ui::report($rapport);
    else ploopi\skin::get()->display_array($columns, $values, 'array_installproc');
}

if ($booSystemP4)
{
    echo ploopi\system\ui::card_close(ploopi\system\ui::button(array('label' => _PLOOPI_CONTINUE, 'icon' => 'arrow-right', 'class' => 'primary', 'href' => ploopi\crypt::urlencode('admin.php?sysToolbarItem=install'))));
    return;
}
?>
<div style="padding:4px;text-align:right;">
    <form action="<?php echo ploopi\crypt::urlencode("admin.php?sysToolbarItem=install"); ?>" method="post">
    <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_CONTINUE; ?>">
    </form>
</div>

<?php echo ploopi\skin::get()->close_simplebloc(); ?>
