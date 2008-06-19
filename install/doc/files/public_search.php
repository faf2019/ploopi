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
 * Interface de recherche
 *
 * @package doc
 * @subpackage public
 * @copyright Netlor, Ovensia
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
$show_options = (   !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype']) ||
                    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user']) ||
                    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace']) ||
                    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1']) ||
                    !empty($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2'])
                );

/**
 * Affichage du formulaire de recherche
 */                
?>

<form action="<? echo ploopi_urlencode('admin.php'); ?>" onsubmit="javascript:doc_search_next();return false;" method="post">
<input type="hidden" name="op" value="search_next">
<div class="doc_folderinfo">
    <div style="float:left;height:40px;">
        <p style="margin:0;padding:4px 0px 4px 8px;">
            <img src="./modules/doc/img/search.png" />
        </p>
    </div>
    <div style="float:left;height:40px;">
            <p style="margin:0;padding:4px;">
            <strong>Recherche</strong>
            <br />d'un Fichier
        </p>
    </div>
    <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px;">
            <strong>Nom / Mots Clés</strong>:
            <br /><input type="text" class="text" style="width:140px;" id="doc_search_keywords" value="<? echo htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_keywords']); ?>">
        </p>
    </div>
    <div style="display:<? echo ($show_options) ? 'block' : 'none'; ?>;" id="doc_search_options">
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px;">
                <strong>Type</strong>:
                <br />
                <?
                $select = "SELECT distinct(filetype) FROM ploopi_mod_doc_ext ORDER BY filetype";
                $db->query($select);
                ?>
                <select class="select" style="width:100px;" id="doc_search_filetype">
                    <option value="">(tout)</option>
                    <?
                    while ($row = $db->fetchrow())
                    {
                        ?>
                        <option value="<? echo $row['filetype']; ?>" <? if ($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_filetype'] == $row['filetype']) echo 'selected'; ?>><? echo $row['filetype']; ?></option>
                        <?
                    }
                    ?>
                </select>
            </p>
        </div>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px;">
                <strong>Propriétaire</strong>:
                <br /><input type="text" class="text"  style="width:90px;" value="<? echo htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_user']); ?>" id="doc_search_user">
            </p>
        </div>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px;">
                <strong>Espace</strong>:
                <br /><input type="text" class="text" style="width:90px;" value="<? echo htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_workspace']); ?>" id="doc_search_workspace">
            </p>
        </div>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px;">
                <strong>Date (du)</strong>:
                <br />
                <input type="text" class="text" style="width:70px;" value="<? echo $_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date1']; ?>" name="doc_search_date1" id="doc_search_date1">
                <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('doc_search_date1', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
            </p>
        </div>
        <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px;">
                <strong>Date (au)</strong>:
                <br />
                <input type="text" class="text" style="width:70px;" value="<? echo htmlentities($_SESSION['doc'][$_SESSION['ploopi']['moduleid']]['search_date2']); ?>" name="doc_search_date2" id="doc_search_date2">
                <a href="javascript:void(0);" onclick="javascript:ploopi_calendar_open('doc_search_date2', event);"><img src="./img/calendar/calendar.gif" width="31" height="18" align="top" border="0"></a>
            </p>
        </div>
    </div>
    <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px;">
            <strong>Lancer la recherche</strong>:
            <br /><input type="submit" class="flatbutton" value="Rechercher">
        </p>
    </div>
    <div style="float:left;height:40px;border-left:1px solid #e0e0e0;">
            <p style="margin:0;padding:4px;">
            <a href="javascript:void(0);" onclick="javascript:ploopi_switchdisplay('doc_search_options');"><strong>Afficher<br />plus d'options</strong></a>
        </p>
    </div>
</div>
</form>
<script type="text/javascript">
$('doc_search_keywords').focus();
</script>

<div class="doc_explorer_main" id="doc_search_result">
<? include_once './modules/doc/public_search_result.php'; ?>
</div>
