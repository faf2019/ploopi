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

echo $skin->open_simplebloc(_SYSTEM_UPDATE." - $strSysVersion vers "._PLOOPI_VERSION);

switch($op)
{
    case 'system_update_execute':
        
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
                        if (!empty($matches[1]) && strcmp($matches[1],$strSysVersion)>0)
                        {
                            $arrUpdates[$matches[1]] = $matches[0];
                        }
                    }
                }
            }
        }
        
        ksort($arrUpdates);
        
        foreach($arrUpdates as $strSqlFile)
        {
            if (file_exists("{$strSysInstallPath}{$strSqlFile}") && is_readable("{$strSysInstallPath}{$strSqlFile}"))
            {
                ?>
                <div style="padding:4px;">Import du fichier <b><? echo $strSqlFile ?></b></div>
                <?
                $db->multiplequeries(file_get_contents("{$strSysInstallPath}{$strSqlFile}"));
            }
            else
            {
                ?>
                <div style="padding:4px;color:#a60000;">Impossible de lire le fichier <b><? echo "{$strSysInstallPath}{$strSqlFile}" ?></b>, v�rifiez les droits en lecture</div>
                <?
            }     
        }
        ?>
        <div style="padding:4px;">
        <b>Mise � jour termin�e</b>
        </div>
        <div style="padding:4px;">
        <button onclick="javascript:document.location.href='<? echo ploopi_urlencode($scriptenv); ?>';">Continuer</button>
        </div>
        <?
    break;
    
    default:
        ?>
        <div style="padding:4px;">
        Vous venez de mettre � jour Ploopi. 
        <br />Vous aviez la version <b><? echo $strSysVersion; ?></b> et le syst�me a �t� mis � jour en version <b><? echo _PLOOPI_VERSION; ?></b> 
        <br />Pour terminer la mise � jour vous devez mettre � jour la base de donn�es.
        </div>
        
        <div style="padding:4px;">
        <button onclick="javascript:document.location.href='<? echo ploopi_urlencode("{$scriptenv}?op=system_update_execute"); ?>';">Mettre � jour la Base de Donn�es</button>
        </div>
        <?
   break;
}

echo $skin->close_simplebloc();
?>