<?php
/*
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
 * Interface de recherche du moteur de recherche intégrale
 *
 * @package system
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Récupération des paramètres de recherche
 */

if (isset($_REQUEST['system_search_keywords']))     $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] = $_REQUEST['system_search_keywords'];
if (isset($_REQUEST['system_search_workspace']))    $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'] = $_REQUEST['system_search_workspace'];
if (isset($_REQUEST['system_search_module']))       $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module'] = $_REQUEST['system_search_module'];
if (isset($_REQUEST['system_search_date1']))    $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'] = $_REQUEST['system_search_date1'];
if (isset($_REQUEST['system_search_date2']))    $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'] = $_REQUEST['system_search_date2'];

/**
 * Initialisation de la session
 */

if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_workspace'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date1'] = '';
if (!isset($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'])) $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_date2'] = '';

echo $skin->create_pagetitle(_SYSTEM_LABEL_SEARCH);
echo $skin->open_simplebloc('Formulaire de recherche');
?>
<form action="<?php echo ploopi_urlencode('admin.php?op=search_next'); ?>" onsubmit="javascript:system_search_next();return false;" method="post">
<div class="dims_va" style="padding:4px;">
    <span>Mots Clés:</span>
    <input type="text" class="text" name="system_search_keywords" id="system_search_keywords" value="<?php echo htmlentities($_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_keywords']); ?>" />

    <span>Module:</span>
    <select class="select" name="system_search_module" id="system_search_module" />
    <option value="">(tous)</option>
    <?php
    // on parcourt la liste des modules de l'espace courant
    $arrAvailableModules = array();
    foreach ($_SESSION['ploopi']['workspaces'][$_SESSION['ploopi']['workspaceid']]['modules'] as $modid)
    {
        $arrMod = &$_SESSION['ploopi']['modules'][$modid];
        if ($arrMod['active'])
        {
            $arrAvailableModules[] = $modid;
            ?>
            <option value="<?php echo $modid; ?>" <?php if ($modid == $_SESSION['ploopi'][_PLOOPI_MODULE_SYSTEM]['search_module']) echo 'selected'; ?>><?php echo htmlentities($arrMod['label']); ?></option>
            <?php
        }
    }
    ?>
    </select>

    <input type="hidden" class="text" name="system_search_date1" id="system_search_date1" />
    <input type="hidden" class="text" name="system_search_date2" id="system_search_date2" />
    <input type="reset" class="button" value="<?php echo _PLOOPI_RESET; ?>">
    <input type="submit" class="button" value="<?php echo _PLOOPI_SEARCH; ?>">

</div>
</form>

<div id="system_search_result">
<?php include_once './modules/system/public_search_result.php'; ?>
</div>

<?php
echo $skin->close_simplebloc();
?>

<script type="text/javascript">
//<!--
if ($('system_search_keywords')) $('system_search_keywords').focus();
//-->
</script>
