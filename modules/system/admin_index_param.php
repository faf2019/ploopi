<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * Interface de gestion des paramètres de modules.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Récupération de l'identifiant de module
 */
if (!empty($_REQUEST['idmodule'])) $idmodule = $_REQUEST['idmodule'];

echo $skin->open_simplebloc(_SYSTEM_MODULESELECTED);
?>
<div style="padding:4px;">
<?php
// get modules
$modules = $workspace->getmodules();
usort($modules, create_function('$a,$b', 'return strcasecmp($a[\'instancename\'], $b[\'instancename\']);'));

if (empty($modules))
{
    ?>
    Aucun module pour cet espace
    <?php
}
else
{
    ?>
    <form id="form_modparam" action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post">
        <select class="select" name="idmodule" onchange="javascript:$('form_modparam').submit();">
        <?php
        foreach($modules as $mod)
        {
            if (empty($idmodule)) $idmodule = $mod['instanceid'];
            ?>
                <option <?php if ($idmodule == $mod['instanceid']) echo 'selected'; ?> value="<?php echo $mod['instanceid']; ?>"><?php echo ploopi\str::htmlentities("{$mod['instancename']} ({$mod['label']})"); ?></option>
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
echo $skin->close_simplebloc();

if (isset($idmodule))
{
    echo $skin->open_simplebloc(_SYSTEM_MODULEPARAM);

    $param_module->open($idmodule, $workspaceid);

    $arrParam = $param_module->getvalues();

    if (!empty($arrParam))
    {
        ?>
        <div style="padding:4px;">
            <form action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="save">
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
                            <textarea class="text" name="<?php echo ploopi\str::htmlentities($name); ?>"><?php echo ploopi\str::htmlentities($param['value']); ?></textarea>
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

    echo $skin->close_simplebloc();
}
?>
