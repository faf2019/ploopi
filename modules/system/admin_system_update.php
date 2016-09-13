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
 * Mise à jour du système
 *
 * @package system
 * @subpackage system
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */

echo $skin->open_simplebloc(_SYSTEM_UPDATE.ovensia\ploopi\str::htmlentities(" - {$strSysVersion} vers ")._PLOOPI_VERSION);

switch($op)
{
    case 'system_update_execute':
        if ($strSysVersion == _PLOOPI_VERSION) ovensia\ploopi\output::redirect('admin.php?reloadsession');

        $strSysVersion = str_replace(' ', '', $strSysVersion);

        $strSysInstallPath = './install/system/';

        $arrUpdates = array();

        if (is_dir($strSysInstallPath))
        {
            $dir = @opendir($strSysInstallPath);
            while($file = readdir($dir))
            {

                if (is_file("{$strSysInstallPath}{$file}"))
                {
                    $matches = array();
                    if (preg_match("@^update_ploopi_(.*).sql@i", $file, $matches))
                    {
                        if (!empty($matches[1]) && version_compare($matches[1], $strSysVersion)>0)
                        {
                            $arrUpdates[$matches[1]] = $matches[0];
                        }
                    }
                }
            }
        }

        // Tri par version
        uksort($arrUpdates, 'version_compare');

        foreach($arrUpdates as $strSqlFile)
        {
            if (file_exists("{$strSysInstallPath}{$strSqlFile}") && is_readable("{$strSysInstallPath}{$strSqlFile}"))
            {
                ?>
                <div style="padding:4px;">Import du fichier <b><?php echo ovensia\ploopi\str::htmlentities($strSqlFile); ?></b></div>
                <?php
                $db->multiplequeries(file_get_contents("{$strSysInstallPath}{$strSqlFile}"));
            }
            else
            {
                ?>
                <div style="padding:4px;color:#a60000;">Impossible de lire le fichier <b><?php echo ovensia\ploopi\str::htmlentities("{$strSysInstallPath}{$strSqlFile}") ?></b>, vérifiez les droits en lecture</div>
                <?php
            }
        }
        ?>
        <div style="padding:4px;">
        <b>Mise à jour terminée</b>
        </div>
        <div style="padding:4px;">
        <button onclick="javascript:document.location.href='<?php echo ovensia\ploopi\crypt::urlencode('admin.php?reloadsession'); ?>';">Continuer</button>
        </div>
        <?php
    break;

    default:
        ?>
        <div style="padding:4px;">
        Vous venez de mettre à jour Ploopi.
        <br />Vous aviez la version <b><?php echo ovensia\ploopi\str::htmlentities($strSysVersion); ?></b> et le système a été mis à jour en version <b><?php echo _PLOOPI_VERSION; ?></b>
        <br />Pour terminer la mise à jour vous devez mettre à jour la base de données.
        </div>

        <div style="padding:4px;">
        <button onclick="javascript:document.location.href='<?php echo ovensia\ploopi\crypt::urlencode("admin.php?op=system_update_execute"); ?>';">Mettre à jour la Base de Données</button>
        </div>
        <?php
   break;
}

echo $skin->close_simplebloc();
?>
