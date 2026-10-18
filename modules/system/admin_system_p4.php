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
 * Niveau « système » de l'administration, gabarits de génération 4 : chaque entrée
 * de la section Système du menu latéral est un écran avec son en-tête de page.
 *
 * Mêmes opérations que admin_system.php (qui inclut ce fichier) ; seules les vues
 * changent. Variables fournies : $toolbar, $strSysVersion, $op.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

use ploopi\system\ui;

?>
<div class="p4-page p4-sys-page">
<?php
switch($_SESSION['system']['sysToolbarItem'])
{
    case 'systemupdate':
        if (!empty($strSysVersion)) include './modules/system/admin_system_update_p4.php';
    break;

    case 'install':
        switch($op)
        {
            case 'update':
                include './modules/system/admin_system_installmodules_updateproc.php';
            break;

            case 'install':
                include './modules/system/admin_system_installmodules_installproc.php';
            break;

            case 'demo':
            case 'demo_done':
                include './modules/system/admin_system_demo.php';
            break;

            case 'uninstall':
                global $admin_redirect;
                $admin_redirect = true;

                include './modules/system/admin_system_installmodules_uninstallproc.php';

                // Un script de désinstallation de module peut afficher un message (admin_redirect à false)
                if ($admin_redirect) ploopi\output::redirect("admin.php?reloadsession");
                echo '<div class="p4-row p4-mt-16 p4-ui">'.ui::button(array('label' => _PLOOPI_CONTINUE, 'icon' => 'arrow-right', 'class' => 'primary', 'href' => ploopi\crypt::urlencode('admin.php?reloadsession'))).'</div>';
            break;

            default:
                include './modules/system/admin_system_modules_p4.php';
            break;
        }
    break;

    case 'directory':
        // Recherche de l'écran historique, rendu de admin_system_directory_p4.php
        include './modules/system/admin_system_directory.php';
    break;

    case 'tools':
        include './modules/system/admin_system_tools_p4.php';
    break;

    case 'params':
        if ($op == 'save')
        {
            if (!empty($_POST['idmodule']) && is_numeric($_POST['idmodule']))
            {
                $param_module = new ploopi\param();

                $module = new ploopi\module();
                $module->open($_POST['idmodule']);
                ploopi\user_action_log::record(_SYSTEM_ACTION_PARAMMODULE, $module->fields['label']);

                $param_module->open($_POST['idmodule']);
                $param_module->setvalues($_POST);
                $param_module->save();

                ploopi\output::redirect("admin.php?idmodule={$_POST['idmodule']}&reloadsession");
            }
            ploopi\output::redirect("admin.php");
        }

        echo ui::pagehead(_SYSTEM_MENU_PARAMS, _SYSTEM_PARAMF_SUBTITLE);
        $strParamContext = 'system';
        include './modules/system/admin_params_p4.php';
    break;
}
?>
</div>
