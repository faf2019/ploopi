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
 * Interface de recherche
 *
 * @package doc
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Récupération des paramètres de recherche et remplissage de la variable session du module
 */

if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_keywords'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_keywords'] = '';
if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype'] = '';
if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user'] = '';
if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace'] = '';
if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1'] = '';
if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2'] = '';

/**
 * On affiche les options si une option a été modifiée
 */
$show_options = (
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2'])
);

/**
 * Affichage du formulaire de recherche
 */
?>

<form action="<?php echo ploopi\crypt::urlencode('admin.php'); ?>" onsubmit="javascript:doc_search_next();return false;" method="post">
<input type="hidden" name="op" value="search_next">
<div class="doc_folderinfo">
    <div style="float:left;height:40px;">
        <p style="margin:0;padding:4px 0px 4px 4px;">
            <img src="./modules/doc/img/search.png" />
        </p>
    </div>

    <div style="float:left;height:40px;">
        <p style="margin:0;padding:4px;float:left;">
            <strong>Recherche</strong>
            <br />d'un Fichier
        </p>
    </div>

    <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
        <p style="margin:0;padding:4px;float:left;">
            <input type="text" class="text" style="width:140px;" id="doc_search_keywords" value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_keywords']); ?>" placeholder="Fichier / Mot Clé" />
            <input type="submit" class="flatbutton" value="Rechercher" />
        </p>
    </div>
    <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
        <p style="margin:0;padding:4px;float:left;">
            <a href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('doc_search_options');"><strong>Afficher/Cacher<br />les options supplémentaires</strong></a>
        </p>
    </div>
</div>
<div class="doc_folderinfo" style="display:<?php echo ($show_options) ? 'block' : 'none'; ?>;border-top:1px solid #c0c0c0;" id="doc_search_options">
    <p style="float:left;margin:0;padding:4px;">
        <strong>Type</strong>:
        <br />
        <?php
        $arrFileType = array();

        $select = "SELECT distinct(filetype) FROM ploopi_mimetype";
        ploopi\db::get()->query($select);
        while ($row = ploopi\db::get()->fetchrow())
        {
            $arrFileType[$row['filetype']] = (isset($ploopi_type_file[$row['filetype']]) ? $ploopi_type_file[$row['filetype']] : $row['filetype']);
        }
        natcasesort($arrFileType);
        ?>
        <select class="select" style="width:100px;" id="doc_search_filetype">
            <option value="">(tout)</option>
            <?php
            foreach ($arrFileType as $strFileType => $strFileTypeLang)
            {
                ?>
                <option value="<?php echo ploopi\str::htmlentities($strFileType); ?>" <?php if ($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype'] == $strFileType) echo 'selected'; ?>><?php echo ploopi\str::htmlentities($strFileTypeLang); ?></option>
                <?php
            }
            ?>
        </select>
    </p>
    <p style="margin:0;padding:4px;float:left;">
        <strong>Propriétaire</strong>:
        <br /><input type="text" class="text"  style="width:90px;" value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user']); ?>" id="doc_search_user">
    </p>
    <p style="margin:0;padding:4px;float:left;">
        <strong>Espace</strong>:
        <br /><input type="text" class="text" style="width:90px;" value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace']); ?>" id="doc_search_workspace">
    </p>
    <p style="margin:0;padding:4px;float:left;">
        <strong>Date (du)</strong>:
        <br />
        <input type="text" class="text" style="width:70px;" value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1']); ?>" name="doc_search_date1" id="doc_search_date1">
        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('doc_search_date1', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
    </p>
    <p style="float:left;margin:0;padding:4px;">
        <strong>Date (au)</strong>:
        <br />
        <input type="text" class="text" style="width:70px;" value="<?php echo ploopi\str::htmlentities(ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2'])); ?>" name="doc_search_date2" id="doc_search_date2">
        <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('doc_search_date2', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
    </p>
</div>

</form>
<script type="text/javascript">
Event.observe(window, 'load', function() { $('doc_search_keywords').focus(); } )
</script>

<div class="doc_explorer_main" id="doc_search_result">
<?php include_once './modules/doc/public_search_result.php'; ?>
</div>
