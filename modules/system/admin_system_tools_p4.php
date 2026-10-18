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
 * Système › Outils, gabarits de génération 4 : un pavé par outil, puis l'outil choisi
 * avec son en-tête de page et un lien de retour.
 *
 * Mêmes outils et opérations que admin_system.php (op=phpinfo, diagnostic, sqldump,
 * connectedusers, serverload, actionhistory, stats) ; les fichiers tools_*.php
 * rendent leurs blocs et tableaux par system_open_bloc(), system_display_array()…
 * (include/functions.php), qui suivent le gabarit.
 *
 * Variable fournie : $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

$arrTools = array(
    'connectedusers' => array(_SYSTEM_TOOLS_T_CONNECTEDUSERS, _SYSTEM_TOOLS_CONNECTEDUSERS_DESC, 'users', 'tools_connectedusers.php', _SYSTEM_LABEL_CONNECTEDUSERS),
    'actionhistory' => array(_SYSTEM_TOOLS_T_ACTIONHISTORY, _SYSTEM_TOOLS_ACTIONHISTORY_DESC, 'history', 'tools_actionhistory.php', _SYSTEM_LABEL_ACTIONHISTORY),
    'diagnostic' => array(_SYSTEM_TOOLS_T_DIAGNOSTIC, _SYSTEM_TOOLS_DIAGNOSTIC_DESC, 'stethoscope', 'tools_diagnostic.php', _SYSTEM_LABEL_DIAGNOSTIC),
    'serverload' => array(_SYSTEM_TOOLS_T_SERVERLOAD, _SYSTEM_TOOLS_SERVERLOAD_DESC, 'activity', '', _SYSTEM_LABEL_SERVERLOAD),
    'stats' => array(_SYSTEM_TOOLS_STATS, _SYSTEM_TOOLS_STATS_DESC, 'chart-line', 'tools_stats.php', ''),
    'sqldump' => array(_SYSTEM_TOOLS_T_SQLDUMP, _SYSTEM_TOOLS_SQLDUMP_DESC, 'database-export', 'tools_sqldump.php', _SYSTEM_LABEL_SQLDUMP),
    'phpinfo' => array(_SYSTEM_TOOLS_T_PHPINFO, _SYSTEM_TOOLS_PHPINFO_DESC, 'code', '', _SYSTEM_LABEL_PHPINFO)
);

if (!isset($arrTools[$op]))
{
    echo ui::pagehead(_SYSTEM_MENU_TOOLS, _SYSTEM_TOOLS_SUBTITLE);
    ?>
    <div class="p4-tiles p4-ui">
        <?php
        foreach($arrTools as $strTool => $arrTool)
        {
            ?>
            <a class="p4-tile" href="<?php echo ploopi\crypt::urlencode("admin.php?sysToolbarItem=tools&op={$strTool}"); ?>">
                <div class="p4-tile-head">
                    <span class="p4-tile-icon"><?php echo ui::icon($arrTool[2]); ?></span>
                    <div class="p4-grow"><h3><?php echo ui::e($arrTool[0]); ?></h3></div>
                    <?php echo ui::icon('chevron-right', 'p4-muted'); ?>
                </div>
                <p><?php echo ui::e($arrTool[1]); ?></p>
            </a>
            <?php
        }
        ?>
    </div>
    <?php
    return;
}

$arrTool = $arrTools[$op];

// Titre de la page : les blocs des outils ne le répètent pas (system_open_bloc)
$GLOBALS['system_p4_pagetitle'] = trim(html_entity_decode(strip_tags($arrTool[4]), ENT_QUOTES, 'UTF-8'));
echo ui::pagehead($arrTool[0], $arrTool[1], array(), array(_SYSTEM_MENU_TOOLS, ploopi\crypt::urlencode('admin.php?sysToolbarItem=tools&op=list')));

switch($op)
{
    case 'phpinfo':
        /**
         * Configuration de PHP dans le thème de l'écran (system_phpinfo_rows) plutôt que la
         * page de phpinfo() dans un cadre au fond blanc ; ploopi2 et ploopi3 gardent le cadre.
         */
        echo ui::stats(array(
            array(_SYSTEM_TOOLS_PHPINFO_VERSION, 'PHP '.PHP_VERSION, 'code'),
            array(_SYSTEM_TOOLS_PHPINFO_SAPI, PHP_SAPI, 'server'),
            array(_SYSTEM_TOOLS_PHPINFO_INI, (string) php_ini_loaded_file(), 'file-text')
        ));

        $arrValues = array();
        foreach(system_phpinfo_rows() as $arrRow)
        {
            $arrValues[] = array('values' => array(
                'section' => array('label' => ui::e($arrRow['section'])),
                'directive' => array('label' => '<code>'.ui::e($arrRow['directive']).'</code>', 'sort_label' => $arrRow['directive']),
                'local' => array('label' => ui::e($arrRow['local'])),
                'master' => array('label' => ui::e($arrRow['master']))
            ));
        }

        echo ui::card_open('', '', array(), array('class' => 'p4-card-flush'));
        echo ui::table(
            array(
                'left' => array(
                    'section' => array('label' => _SYSTEM_TOOLS_PHPINFO_SECTION, 'options' => array('sort' => true)),
                    'directive' => array('label' => _SYSTEM_TOOLS_PHPINFO_DIRECTIVE, 'options' => array('sort' => true))
                ),
                'auto' => array('local' => array('label' => _SYSTEM_TOOLS_PHPINFO_LOCAL)),
                'right' => array('master' => array('label' => _SYSTEM_TOOLS_PHPINFO_MASTER))
            ),
            $arrValues,
            array('sortable' => true, 'filter' => _SYSTEM_TOOLS_PHPINFO_FILTER, 'count' => _SYSTEM_TOOLS_PHPINFO_COUNT, 'limit' => 100, 'class' => 'p4-phpinfo-table', 'empty' => _SYSTEM_TOOLS_PHPINFO_NONE)
        );
        echo ui::card_close();
    break;

    case 'serverload':
        echo ui::card_open('', '', array(), array('class' => 'p4-legacy-card'));
        ?>
        <div id="system_serverload" data-p4-serverload><?php include './modules/system/tools_serverload.php'; ?></div>
        <?php
        echo ui::card_close();
    break;

    default:
        include "./modules/system/{$arrTool[3]}";
    break;
}

echo ui::script();
