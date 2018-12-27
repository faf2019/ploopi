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
 * Interface de recherche
 *
 * @package doc
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
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
if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_stem'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_stem'] = 1;
if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_phonetic'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_phonetic'] = 1;
if (!isset($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_and'])) $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_and'] = '0';

/**
 * On affiche les options si une option a été modifiée
 */
$show_options = (
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2']) ||
    empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_phonetic']) ||
    empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_phonetic']) ||
    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_and'])
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
            <input type="text" class="text" style="width:140px;" id="doc_search_keywords" autofocus value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_keywords']); ?>" placeholder="Fichier / Mot Clé" />
            <input type="submit" class="flatbutton" value="Rechercher" />
        </p>
    </div>
    <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
        <p style="margin:0;padding:4px;float:left;">
            <a href="javascript:void(0);" onclick="javascript:ploopi.switchdisplay('doc_search_options');"><strong>Afficher/Cacher<br />les options supplémentaires</strong></a>
        </p>
    </div>
</div>
<div class="doc_folderinfo ploopi_va" style="padding:2px;display:<?php echo ($show_options) ? 'block' : 'none'; ?>;border-top:1px solid #c0c0c0;" id="doc_search_options">
    <strong>Type</strong>:
    <?php
    $arrFileType = array();

    ploopi\db::get()->query("SELECT distinct(filetype) FROM ploopi_mimetype");
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
    <input type="text" style="width:90px;" placeholder="Propriétaire" value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user']); ?>" id="doc_search_user">
    <input type="text" style="width:90px;" placeholder="Espace" value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace']); ?>" id="doc_search_workspace">
    <input type="text" style="width:70px;" placeholder="Date (du)" value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1']); ?>" name="doc_search_date1" id="doc_search_date1">
    <a href="javascript:void(0);" onclick="javascript:ploopi.calendar.open('doc_search_date1', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
    <input type="text" style="width:70px;" placeholder="Date (au)" value="<?php echo ploopi\str::htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2']); ?>" name="doc_search_date2" id="doc_search_date2">
    <a href="javascript:void(0);" onclick="javascript:ploopi.calendar.open('doc_search_date2', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
    <input type="checkbox" id="doc_search_stem" value="1" <? if ($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_stem']) echo 'checked="chedked"'; ?> /><label class="ploopi_checkbox" for="doc_search_stem">Recherche par racinisation</label>
    <input type="checkbox" id="doc_search_phonetic" value="1" <? if ($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_phonetic']) echo 'checked="chedked"'; ?> /><label class="ploopi_checkbox" for="doc_search_phonetic">Recherche phonétique</label>
    <input type="radio" id="doc_search_and" name="doc_search_and" value="1" <? if ($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_and'] === '1') echo 'checked="chedked"'; ?> /><label class="ploopi_checkbox" for="doc_search_and">ET</label>
    <input type="radio" id="doc_search_or" name="doc_search_and" value="0" <? if ($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_and'] === '0') echo 'checked="chedked"'; ?> /><label class="ploopi_checkbox" for="doc_search_or">OU</label>
</div>

</form>

<div class="doc_explorer_main" id="doc_search_result">
<?php include_once './modules/doc/public_search_result.php'; ?>
</div>
