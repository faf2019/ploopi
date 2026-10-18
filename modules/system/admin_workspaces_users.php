<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Utilisateurs d'un espace ou d'un groupe (gabarits de génération 4).
 *
 * Remplace admin_index_users_list.php : mêmes utilisateurs (rattachés directement à
 * l'espace ou au groupe), mêmes actions et mêmes droits, mêmes exports. La liste
 * est transmise au script de l'écran (include/workspaces.js) qui la filtre, la
 * trie, la pagine et gère la sélection, sans rechargement.
 *
 * Variables fournies par admin_index.php / admin_index_users.php : $workspaceid,
 * $groupid, $workspace, $group, $tabs.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

global $ploopi_system_levels;

$objSkin = ploopi\skin::get();
$db = ploopi\db::get();

$booWorkspace = $_SESSION['system']['level'] == _SYSTEM_WORKSPACES;

// Au-delà, la page deviendrait trop lourde : l'annuaire et ses filtres prennent le relais
$intMaxUsers = 5000;

switch ($_SESSION['system']['level'])
{
    case _SYSTEM_WORKSPACES :
        $strSql = "
            SELECT      ploopi_user.*,
                        ploopi_workspace.id as idref,
                        ploopi_workspace.label as label,
                        ploopi_workspace_user.adminlevel

            FROM        ploopi_user

            INNER JOIN  ploopi_workspace_user
            ON          ploopi_workspace_user.id_user = ploopi_user.id
            AND         ploopi_workspace_user.id_workspace = ".((int) $workspaceid)."

            INNER JOIN  ploopi_workspace
            ON          ploopi_workspace.id = ploopi_workspace_user.id_workspace

            ORDER BY lastname, firstname
        ";
    break;

    case _SYSTEM_GROUPS :
    default:
        $strSql = "
            SELECT      ploopi_user.*,
                        ploopi_group.id as idref,
                        ploopi_group.label as label

            FROM        ploopi_user

            INNER JOIN  ploopi_group_user
            ON          ploopi_group_user.id_user = ploopi_user.id
            AND         ploopi_group_user.id_group = ".((int) $groupid)."

            INNER JOIN  ploopi_group
            ON          ploopi_group.id = ploopi_group_user.id_group

            ORDER BY lastname, firstname
        ";
    break;
}

// Dernière requête : base des exports (system_directory_export), entière ou restreinte
// à une sélection d'identifiants
ploopi\session::setvar('directory_sql', $strSql);
ploopi\session::setvar('system_users_sql', $strSql);

$rs = $db->query($strSql);
$intTotal = $db->numrows($rs);

$arrUsers = array();

if ($intTotal <= $intMaxUsers)
{
    $strToday = date('YmdHis');

    $arrRows = array();
    while ($row = $db->fetchrow($rs)) $arrRows[] = $row;

    // Dans un groupe, détacher se limite aux utilisateurs dont le niveau le plus élevé
    // dans les espaces ne dépasse pas celui de l'administrateur (system_user_isdetachable)
    $arrMaxLevels = (!$booWorkspace && $_SESSION['ploopi']['adminlevel'] < _PLOOPI_ID_LEVEL_SYSTEMADMIN) ? system_users_maxlevel(array_column($arrRows, 'id')) : array();

    foreach($arrRows as $row)
    {
        // Dans un espace, un administrateur n'agit que sur les niveaux inférieurs ou égaux au sien
        $booCan = !$booWorkspace || $_SESSION['ploopi']['adminlevel'] >= $row['adminlevel'];
        $booDetach = $booCan && (!isset($arrMaxLevels[$row['id']]) || $arrMaxLevels[$row['id']] <= $_SESSION['ploopi']['adminlevel']);

        $strLast = '';
        $strLastLabel = '';
        if (!empty($row['last_connection']))
        {
            $strLast = substr($row['last_connection'], 0, 8);
            $arrLocal = ploopi\date::timestamp2local($row['last_connection']);
            $strLastLabel = $arrLocal['date'];
        }

        $strBase = "admin.php?wspToolbarItem=tabUsers&usrTabItem=tabUserList&user_id={$row['id']}&op=";

        $arrUsers[$row['id']] = array(
            'id' => (int) $row['id'],
            'ln' => $row['lastname'],
            'fn' => $row['firstname'],
            'login' => $row['login'],
            'email' => $row['email'],
            'svc' => $row['service'],
            'ent' => $row['entity'],
            'org' => '',
            'lvl' => $booWorkspace ? (int) $row['adminlevel'] : null,
            'lvlLabel' => $booWorkspace && isset($ploopi_system_levels[$row['adminlevel']]) ? $ploopi_system_levels[$row['adminlevel']] : '',
            'last' => $strLast,
            'lastLabel' => $strLastLabel,
            'off' => !empty($row['disabled']) || (!empty($row['date_expire']) && $row['date_expire'] < $strToday),
            'can' => $booCan,
            'edit' => $booCan ? ploopi\crypt::urlencode($strBase.'modify_user') : '',
            'detach' => $booDetach ? ploopi\crypt::urlencode($strBase.'detach_user') : '',
            'del' => $booCan ? ploopi\crypt::urlencode($strBase.'delete_user') : ''
        );
    }

    // Origine : le premier groupe de chaque utilisateur (le moins profond), en une requête
    if (!empty($arrUsers))
    {
        $rs = $db->query("
            SELECT      gu.id_user, g.label
            FROM        ploopi_group_user gu
            INNER JOIN  ploopi_group g ON g.id = gu.id_group
            WHERE       gu.id_user IN (".implode(',', array_keys($arrUsers)).")
            ORDER BY    g.depth, g.label
        ");

        while ($row = $db->fetchrow($rs))
        {
            if ($arrUsers[$row['id_user']]['org'] === '') $arrUsers[$row['id_user']]['org'] = $row['label'];
        }
    }
}

// Exports de l'annuaire (mêmes formats que la liste historique)
$arrExports = array(
    'xlsx' => array('Excel (XLSX)', 'file-spreadsheet'),
    'xls' => array('Excel 97 (XLS)', 'file-spreadsheet'),
    'csv' => array('CSV', 'file-text'),
    'xml' => array('XML', 'file-text'),
    'vcf' => array('vCard (VCF)', 'id')
);
if (ploopi\param::get('system_jodwebservice') != '')
{
    $arrExports['ods'] = array('OpenDocument (ODS)', 'file-spreadsheet');
    $arrExports['pdf'] = array('PDF', 'file-text');
}

// Ajouts possibles depuis cette liste (sous-écrans existants)
$arrAdd = array();
if (isset($tabs['tabUserAdd'])) $arrAdd[] = array($tabs['tabUserAdd']['url'], 'user-plus', _SYSTEM_USERS_ADD_NEW, _SYSTEM_USERS_ADD_NEW_DESC);
if (isset($tabs['tabUserAttach'])) $arrAdd[] = array($tabs['tabUserAttach']['url'], 'link', _SYSTEM_USERS_ADD_ATTACH, _SYSTEM_USERS_ADD_ATTACH_DESC);
if (isset($tabs['tabUserImport'])) $arrAdd[] = array($tabs['tabUserImport']['url'], 'upload', _SYSTEM_USERS_ADD_IMPORT, _SYSTEM_USERS_ADD_IMPORT_DESC);
if (isset($tabs['tabGroupAttach'])) $arrAdd[] = array($tabs['tabGroupAttach']['url'], 'users-group', _SYSTEM_USERS_ADD_GROUP, _SYSTEM_USERS_ADD_GROUP_DESC);

$arrData = array(
    'users' => array_values($arrUsers),
    'workspace' => $booWorkspace,
    'card' => ploopi\crypt::queryencode('ploopi_op=system_user_card&system_context='.($booWorkspace ? 'workspace' : 'group').'&system_context_id='.($booWorkspace ? (int) $workspaceid : (int) $groupid)),
    'labels' => array(
        'never' => _SYSTEM_USERS_NEVER,
        'none' => _SYSTEM_USERS_NONE,
        'empty' => _SYSTEM_USERS_EMPTY,
        'selected' => _SYSTEM_USERS_SELECTED,
        'selectedPlural' => _SYSTEM_USERS_SELECTED_PLURAL,
        'user' => _SYSTEM_USERS_USER,
        'users' => _SYSTEM_TAB_USERS,
        'summary' => _SYSTEM_USERS_SUMMARY,
        'sortedBy' => _SYSTEM_USERS_SORTEDBY,
        'edit' => _SYSTEM_LABEL_MODIFY,
        'detach' => _SYSTEM_TITLE_USERDETACH,
        'delete' => _SYSTEM_LABEL_DELETE,
        // Messages écrits pour une chaîne JavaScript entre apostrophes : séquences rétablies
        'confirmDetach' => str_replace(array('\\n', "\\'"), array("\n", "'"), _SYSTEM_MSG_CONFIRMUSERDETACH),
        'confirmDelete' => str_replace(array('\\n', "\\'"), array("\n", "'"), _SYSTEM_MSG_CONFIRMUSERDELETE),
        'confirmDetachAll' => _SYSTEM_MSG_CONFIRMUSERSDETACH,
        'disabled' => _SYSTEM_USERS_CARD_DISABLED,
        'select' => _SYSTEM_USERS_SELECT,
        'prev' => _SYSTEM_USERS_PREV,
        'next' => _SYSTEM_USERS_NEXT,
        'all' => _SYSTEM_USERS_ALL
    )
);
?>
<div class="p4-users p4-ui" data-p4-users>
    <script type="application/json" data-p4-users-data><?php echo json_encode($arrData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?></script>

    <div class="p4-list-toolbar">
        <div class="p4-input-icon p4-list-search">
            <?php echo $objSkin->icon('search', 'p4-icon-sm'); ?>
            <input class="p4-input" type="search" data-p4-users-search placeholder="<?php echo ploopi\str::htmlentities(_SYSTEM_USERS_SEARCH); ?>" aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_USERS_SEARCH); ?>" />
        </div>
        <button type="button" class="p4-btn" data-p4-users-filters aria-expanded="false" aria-controls="p4-users-filters">
            <?php echo $objSkin->icon('filter', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_FILTERS); ?><span class="p4-count" data-p4-users-filters-count hidden>0</span>
        </button>
        <span class="p4-grow"></span>

        <div class="p4-menu-wrap">
            <button type="button" class="p4-btn" data-p4-menu="p4-users-export" aria-expanded="false" aria-haspopup="true">
                <?php echo $objSkin->icon('download', 'p4-icon-sm'); ?><span class="p4-hide-sm"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_EXPORT); ?></span><?php echo $objSkin->icon('chevron-down', 'p4-icon-sm'); ?>
            </button>
            <div class="p4-menu" id="p4-users-export">
                <?php
                foreach($arrExports as $strType => $arrExport)
                {
                    ?>
                    <a class="p4-menu-item" data-p4-export href="<?php echo ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc={$strType}"); ?>"><?php echo $objSkin->icon($arrExport[1]); ?><?php echo ploopi\str::htmlentities($arrExport[0]); ?></a>
                    <?php
                }
                ?>
            </div>
        </div>

        <?php
        if (!empty($arrAdd))
        {
            ?>
            <div class="p4-menu-wrap">
                <button type="button" class="p4-btn p4-btn-primary" data-p4-menu="p4-users-add" aria-expanded="false" aria-haspopup="true">
                    <?php echo $objSkin->icon('user-plus', 'p4-icon-sm'); ?><span class="p4-hide-sm"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_ADD); ?></span><?php echo $objSkin->icon('chevron-down', 'p4-icon-sm'); ?>
                </button>
                <div class="p4-menu p4-users-add-menu" id="p4-users-add">
                    <?php
                    foreach($arrAdd as $arrItem)
                    {
                        ?>
                        <a class="p4-menu-item" href="<?php echo ploopi\crypt::urlencode($arrItem[0]); ?>"><?php echo $objSkin->icon($arrItem[1]); ?><span><?php echo ploopi\str::htmlentities($arrItem[2]); ?><small><?php echo ploopi\str::htmlentities($arrItem[3]); ?></small></span></a>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="p4-filters" id="p4-users-filters" hidden>
        <div class="p4-fgrid">
            <div class="p4-field p4-c4"><label for="p4-f-lastname"><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_LASTNAME); ?></label><input class="p4-input" id="p4-f-lastname" data-p4-filter="ln" /></div>
            <div class="p4-field p4-c4"><label for="p4-f-firstname"><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_FIRSTNAME); ?></label><input class="p4-input" id="p4-f-firstname" data-p4-filter="fn" /></div>
            <div class="p4-field p4-c4"><label for="p4-f-login"><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_LOGIN); ?></label><input class="p4-input" id="p4-f-login" data-p4-filter="login" /></div>
            <div class="p4-field p4-c4"><label for="p4-f-email"><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_EMAIL); ?></label><input class="p4-input" id="p4-f-email" data-p4-filter="email" /></div>
            <div class="p4-field p4-c4"><label for="p4-f-from"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_CONNECTION_FROM); ?></label><input class="p4-input" type="date" id="p4-f-from" data-p4-filter="from" /></div>
            <div class="p4-field p4-c4"><label for="p4-f-to"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_CONNECTION_TO); ?></label><input class="p4-input" type="date" id="p4-f-to" data-p4-filter="to" /></div>
        </div>
        <div class="p4-row p4-filters-actions">
            <button type="button" class="p4-btn p4-btn-ghost p4-btn-sm" data-p4-filters-reset><?php echo $objSkin->icon('eraser', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_RESET); ?></button>
            <button type="button" class="p4-btn p4-btn-primary p4-btn-sm" data-p4-filters-apply><?php echo $objSkin->icon('filter', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_FILTER); ?></button>
        </div>
    </div>

    <div class="p4-alpha" role="group" aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_USERS_ALPHA); ?>" data-p4-alpha></div>

    <form class="p4-bulkbar" method="post" action="<?php echo ploopi\crypt::urlencode('admin.php?wspToolbarItem=tabUsers&usrTabItem=tabUserList&op=detach_users'); ?>" data-p4-bulk hidden>
        <strong data-p4-bulk-count></strong>
        <span class="p4-grow"></span>
        <button type="submit" class="p4-btn p4-btn-ghost p4-btn-sm"><?php echo $objSkin->icon('unlink', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_DETACH_SELECTION); ?></button>
        <div class="p4-menu-wrap">
            <button type="button" class="p4-btn p4-btn-ghost p4-btn-sm" data-p4-menu="p4-users-export-sel" aria-expanded="false" aria-haspopup="true"><?php echo $objSkin->icon('download', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_EXPORT_SELECTION); ?></button>
            <div class="p4-menu" id="p4-users-export-sel">
                <?php
                foreach($arrExports as $strType => $arrExport)
                {
                    ?>
                    <a class="p4-menu-item" data-p4-export-selection href="<?php echo ploopi\crypt::urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc={$strType}"); ?>"><?php echo $objSkin->icon($arrExport[1]); ?><?php echo ploopi\str::htmlentities($arrExport[0]); ?></a>
                    <?php
                }
                ?>
            </div>
        </div>
        <button type="button" class="p4-btn p4-btn-ghost p4-btn-sm" data-p4-bulk-clear><?php echo $objSkin->icon('x', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_UNSELECT); ?></button>
    </form>

    <?php
    if ($intTotal > $intMaxUsers)
    {
        ?>
        <div class="p4-callout p4-callout-warning"><?php echo $objSkin->icon('alert-triangle'); ?><div class="p4-callout-body"><?php echo ploopi\str::htmlentities(str_replace('<NB>', $intTotal, _SYSTEM_USERS_TOOMANY)); ?></div></div>
        <?php
    }
    ?>

    <div class="p4-table-wrap">
        <table class="p4-table p4-users-table">
            <thead>
                <tr>
                    <th class="p4-chk"><input type="checkbox" data-p4-check-all aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_USERS_SELECTALL); ?>" /></th>
                    <th><button type="button" class="p4-sort" data-p4-sort="name"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_USER); ?><?php echo $objSkin->icon('arrows-sort'); ?></button></th>
                    <th class="p4-col-org"><button type="button" class="p4-sort" data-p4-sort="org"><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_ORIGIN); ?><?php echo $objSkin->icon('arrows-sort'); ?></button></th>
                    <th class="p4-col-svc"><button type="button" class="p4-sort" data-p4-sort="svc"><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_SERVICE); ?><?php echo $objSkin->icon('arrows-sort'); ?></button></th>
                    <?php if ($booWorkspace) { ?><th class="p4-col-lvl"><button type="button" class="p4-sort" data-p4-sort="lvl"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_CARD_LEVEL); ?><?php echo $objSkin->icon('arrows-sort'); ?></button></th><?php } ?>
                    <th class="p4-col-last"><button type="button" class="p4-sort" data-p4-sort="last"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_LASTCONNECTION); ?><?php echo $objSkin->icon('arrows-sort'); ?></button></th>
                    <th class="p4-col-acts"><span class="p4-sr-only"><?php echo ploopi\str::htmlentities(_SYSTEM_USERS_ACTIONS); ?></span></th>
                </tr>
            </thead>
            <tbody data-p4-users-body></tbody>
        </table>
    </div>

    <div class="p4-list-foot">
        <span class="p4-text-xs p4-muted" data-p4-users-summary></span>
        <div class="p4-pager" data-p4-pager></div>
    </div>
</div>

<div class="p4-drawer-backdrop" data-p4-close></div>
<aside class="p4-drawer p4-ui" id="p4-user-drawer" aria-labelledby="p4-user-title" aria-hidden="true">
    <div class="p4-drawer-header">
        <span class="p4-avatar" data-p4-card-avatar></span>
        <h2 id="p4-user-title" data-p4-card-title></h2>
        <button type="button" class="p4-btn p4-btn-ghost p4-btn-icon" data-p4-close aria-label="<?php echo ploopi\str::htmlentities(_SYSTEM_CLOSE); ?>"><?php echo $objSkin->icon('x'); ?></button>
    </div>
    <div class="p4-drawer-body" data-p4-card-body></div>
    <div class="p4-drawer-footer">
        <a class="p4-btn p4-btn-primary" data-p4-card-edit href="#"><?php echo $objSkin->icon('pencil', 'p4-icon-sm'); ?><?php echo ploopi\str::htmlentities(_SYSTEM_LABEL_MODIFY); ?></a>
    </div>
</aside>
