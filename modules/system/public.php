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
 * Interface publique du module système (tickets, annotations, recherche, etc..)
 *
 * @package system
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Initialisation du module
 */
ploopi_init_module('system');

$op = empty($_REQUEST['op']) ? (ploopi_getparam('system_submenu_display') ? 'tickets' : 'profile') : $_REQUEST['op'];

switch($op)
{
    case 'search':
    case 'search_next':
        include_once 'public_search.php';
    break;

    case 'annotation':
        include './modules/system/public_annotations.php';
    break;

    case 'paramsave':
        if (!empty($_POST['idmodule']) && is_numeric($_POST['idmodule']))
        {
            $param_module = new param();
            $param_module->open($_POST['idmodule'],0,$_SESSION['ploopi']['userid'], 1);
            $param_module->setvalues($_POST);
            $param_module->save();

            ploopi_redirect("admin.php?op=param&idmodule={$_POST['idmodule']}&reloadsession");
        }
        else ploopi_redirect('admin.php');
    break;

    case 'param':
        include './modules/system/public_module_param.php';
    break;

    case 'actions':
        include './modules/system/public_actions.php';
    break;

    case 'save_user':
        $user = new user();
        $user->open($_SESSION['ploopi']['userid']);

        if (!isset($_POST['user_ticketsbyemail'])) $user->fields['ticketsbyemail'] = 0;
        if (!isset($_POST['user_servertimezone'])) $user->fields['servertimezone'] = 0;

        $user->setvalues($_POST,'user_');

        // Affectation nouveau mot de passe
        $error = '';

        if (isset($_POST['useroldpass']) && isset($_POST['usernewpass']) && isset($_POST['usernewpass_confirm']))
        {
            if ($_POST['usernewpass'] != '')
            {
                // Vérification de l'ancien mot de passe
                if (strcmp($user->fields['password'], user::generate_hash($_POST['useroldpass'], $user->fields['login'])) == 0)
                {
                    // Mots de passes équivalents
                    if ($_POST['usernewpass'] == $_POST['usernewpass_confirm'])
                    {
                        // Complexité ok
                        if (!_PLOOPI_USE_COMPLEXE_PASSWORD || ploopi_checkpasswordvalidity($_POST['usernewpass']))
                        {
                            // Affectation du mot de passe
                            $user->setpassword($_POST['usernewpass']);
                            // Mise à jour htpasswd
                            if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($user->fields['login'], $_POST['usernewpass']);
                        }
                        else $error = 'passrejected';
                    }
                    else $error = 'password';
                }
                else $error = 'oldpassword';
            }
        }

        $user->save();

        if (!empty($_SESSION['system']['user_photopath']))
        {
            ploopi_makedir(_PLOOPI_PATHDATA._PLOOPI_SEP.'system');

            // photo temporaire présente => copie dans le dossier définitif
            rename($_SESSION['system']['user_photopath'], $user->getphotopath());
            unset($_SESSION['system']['user_photopath']);
        }

        // Suppression photo
        if (ploopi_getsessionvar("deletephoto_{$user->fields['id']}")) $user->deletephoto();

        if ($error) ploopi_redirect("admin.php?op=profile&error={$error}");
        else ploopi_redirect("admin.php?op=profile&reloadsession");
    break;

    case 'profile':
        include './modules/system/public_user.php';
    break;

    default:
    case 'tickets':
        include './modules/system/public_tickets.php';
    break;
}
?>
