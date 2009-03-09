<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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
 * Interface de gestion des paramètres de modules au niveau système
 *
 * @package system
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Récupération de l'identifiant du module
 */
if (!empty($_REQUEST['idmodule'])) $idmodule = $_REQUEST['idmodule'];

echo $skin->open_simplebloc(_SYSTEM_MODULESELECTED);
?>
<div style="padding:4px;">
<form id="form_modparam" action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
    <select class="select" name="idmodule" onchange="javascript:$('form_modparam').submit();">
    <?php
    foreach($_SESSION['ploopi']['modules'] as $idm => $mod)
    {
        if (empty($idmodule)) $idmodule = $idm;
        ?>
            <option <?php if ($idmodule == $idm) echo 'selected'; ?> value="<?php echo $idm; ?>"><?php echo "{$mod['label']} ({$mod['moduletype']})"; ?></option>
        <?php
    }
    ?>
    </select>
</form>
</div>
<?php
echo $skin->close_simplebloc();

if (isset($idmodule))
{
    echo $skin->open_simplebloc(_SYSTEM_MODULEPARAM);

    $param_module->open($idmodule);

    $arrParam = $param_module->getvalues();

    if (!empty($arrParam))
    {
        ?>
        <div style="padding:4px;">
            <form action="<?php echo ploopi_urlencode('admin.php'); ?>" method="post">
            <input type="hidden" name="op" value="save">
            <input type="hidden" name="idmodule" value="<?php echo $idmodule; ?>">
            <div class="ploopi_form">
            <?php
            foreach($arrParam as $name => $param)
            {
                ?>
                <p>
                    <label><?php echo $param['label']; ?>:</label>

                    <?php
                    if (!empty($param['choices']))
                    {
                        ?>
                        <select class="select" name="<?php echo $name; ?>">
                        <?php
                        foreach($param['choices'] as $value => $displayed_value)
                        {
                            ?>
                            <option <?php if ($param['value'] == $value) echo 'selected'; ?> value="<?php echo htmlspecialchars($value); ?>"><?php echo $displayed_value; ?></option>
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
                            <textarea class="text" name="<?php echo $name; ?>"><?php echo htmlspecialchars($param['value']); ?></textarea>
                            <?php
                        }
                        else
                        {
                            ?>
                            <input class="text" type="text" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($param['value']); ?>" />
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
