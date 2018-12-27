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
 * Interface publique de gestion de paramètres de modules pour un utilisateur
 *
 * @package system
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Récupération de l'identifiant du module
 */
if (!empty($_REQUEST['idmodule']) && is_numeric($_REQUEST['idmodule'])) $idmodule = $_REQUEST['idmodule'];

echo ploopi\skin::get()->create_pagetitle(_PLOOPI_LABEL_MYWORKSPACE);
echo ploopi\skin::get()->open_simplebloc(_PLOOPI_LABEL_MYPARAMS);
?>
<div style="padding:4px;">
<?php
echo ploopi\skin::get()->open_simplebloc(_SYSTEM_MODULESELECTED);

?>
<div style="padding:4px;">
<?php
$arrModList = array();

foreach($_SESSION['ploopi']['workspaces'] as $idw => $wksp)
{
    if (!empty($wksp['adminlevel']) && !empty($wksp['modules']))
    {
        $arrModList = array_unique(array_merge($arrModList, $wksp['modules']));
    }
}

if (empty($arrModList))
{
    ?>
    Aucun module
    <?php
}
else
{
    ?>
    <form id="form_modparam" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post">
        <input type="hidden" name="op" value="param">
        <select class="select" name="idmodule" onchange="javascript:jQuery('#form_modparam').submit();">
        <?php
        usort($arrModList, create_function('$a,$b', 'return strcasecmp($_SESSION[\'ploopi\'][\'modules\'][$a][\'label\'], $_SESSION[\'ploopi\'][\'modules\'][$b][\'label\']);'));

        foreach($arrModList as $idm)
        {
            $mod = &$_SESSION['ploopi']['modules'][$idm];

            if (empty($idmodule)) $idmodule = $idm;
            ?>
                <option <?php if ($idmodule == $idm) echo 'selected'; ?> value="<?php echo $idm; ?>"><?php echo ploopi\str::htmlentities("{$mod['label']} ({$mod['moduletype']})"); ?></option>
            <?php
        }
        ?>
        </select>
    </form>
    <?php
}
?>
</div>
<?php
echo ploopi\skin::get()->close_simplebloc();

if (isset($idmodule))
{
    echo ploopi\skin::get()->open_simplebloc(_SYSTEM_MODULEPARAM);

    $param_module = new ploopi\param();
    $param_module->open($idmodule, 0, $_SESSION['ploopi']['userid'], 1);

    $arrParam = $param_module->getvalues();

    if (!empty($arrParam))
    {
        ?>
        <div style="padding:4px;">
            <form action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="paramsave">
            <input type="hidden" name="idmodule" value="<?php echo $idmodule; ?>">
            <div class="ploopi_form">
            <?php
            foreach($arrParam as $name => $param)
            {
                ?>
                <p>
                    <label><?php echo ploopi\str::htmlentities($param['label']); ?>:</label>

                    <?php
                    if (!empty($param['choices']))
                    {
                        ?>
                        <select class="select" name="<?php echo ploopi\str::htmlentities($name); ?>">
                        <?php
                        foreach($param['choices'] as $value => $displayed_value)
                        {
                            ?>
                            <option <?php if ($param['value'] == $value) echo 'selected'; ?> value="<?php echo ploopi\str::htmlentities($value); ?>"><?php echo ploopi\str::htmlentities($displayed_value); ?></option>
                            <?php
                        }
                        ?>
                        </select>
                        <?php
                    }
                    else
                    {
                        if (strlen($param['value'])>200 || strpos($param['value'], "\n") !== false)
                        {
                            ?>
                            <textarea class="text" name="<?php echo ploopi\str::htmlentities($name); ?>"><?php echo htmlspecialchars($param['value']); ?></textarea>
                            <?php
                        }
                        else
                        {
                            ?>
                            <input class="text" type="text" name="<?php echo ploopi\str::htmlentities($name); ?>" value="<?php echo ploopi\str::htmlentities($param['value']); ?>" />
                            <?php
                        }
                    }
                    ?>
                </p>
                <?php
            }
            ?>
            </div>
            <div style="text-align:right;">
                    <input class="button" type="submit" value="<?php echo _PLOOPI_SAVE; ?>">
            </div>
            </form>
        </div>
    <?php
    }
    else echo '&nbsp;'._SYSTEM_LABEL_NOMODULEPARAM;

    echo ploopi\skin::get()->close_simplebloc();
}
?>
</div>
<?php echo ploopi\skin::get()->close_simplebloc(); ?>
