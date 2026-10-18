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
 * Interface publique du module système (tickets, annotations, recherche, etc..)
 *
 * @package system
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

/**
 * Initialisation du module
 */
ploopi\module::init('system');

$op = empty($_REQUEST['op']) ? (ploopi\param::get('system_submenu_display') ? 'tickets' : 'profile') : $_REQUEST['op'];

// Gabarits de génération 4 : même gouttière que les écrans d'administration (hors enregistrements, qui redirigent)
$booSystemPublicP4 = ploopi\skin::get()->version() >= 4 && !in_array($op, array('paramsave', 'save_user'));
if ($booSystemPublicP4) echo '<div class="p4-page p4-mys-page">';

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
        // Seulement un module proposé par l'écran : ceux des espaces de l'utilisateur
        $arrParamModules = array();
        foreach($_SESSION['ploopi']['workspaces'] as $arrParamWorkspace)
        {
            if (!empty($arrParamWorkspace['adminlevel']) && !empty($arrParamWorkspace['modules'])) $arrParamModules = array_merge($arrParamModules, $arrParamWorkspace['modules']);
        }

        if (!empty($_POST['idmodule']) && is_numeric($_POST['idmodule']) && in_array($_POST['idmodule'], $arrParamModules))
        {
            $param_module = new ploopi\param();
            $param_module->open($_POST['idmodule'],0,$_SESSION['ploopi']['userid'], 1);
            $param_module->setvalues($_POST);
            $param_module->save();

            ploopi\output::redirect("admin.php?op=param&idmodule={$_POST['idmodule']}&reloadsession");
        }
        else ploopi\output::redirect('admin.php');
    break;

    case 'param':
        include './modules/system/public_module_param.php';
    break;

    case 'actions':
        include './modules/system/public_actions.php';
    break;

    case 'save_user':

        // Protection contre modification
        if (ploopi\param::get('system_profile_edit_allowed') == '0') ploopi\output::redirect("admin.php?op=profile");

        $user = new ploopi\user();
        $user->open($_SESSION['ploopi']['userid']);

        if (!isset($_POST['user_ticketsbyemail'])) $user->fields['ticketsbyemail'] = 0;
        if (!isset($_POST['user_servertimezone'])) $user->fields['servertimezone'] = 0;

        // Seuls les champs du formulaire de profil : ni l'identifiant, ni le mot de passe, ni l'état du compte
        $arrProfileFields = array('lastname', 'firstname', 'civility', 'entity', 'service', 'service2', 'function', 'rank', 'number', 'phone', 'mobile', 'fax', 'email', 'building', 'floor', 'office', 'address', 'postalcode', 'city', 'country', 'ticketsbyemail', 'servertimezone', 'timezone', 'color', 'comments');
        $arrProfileValues = array();
        foreach($arrProfileFields as $strField) if (isset($_POST["user_{$strField}"])) $arrProfileValues["user_{$strField}"] = $_POST["user_{$strField}"];

        $user->setvalues($arrProfileValues, 'user_');

        // Affectation nouveau mot de passe
        $error = '';

        if (isset($_POST['useroldpass']) && isset($_POST['usernewpass']) && isset($_POST['usernewpass_confirm']))
        {
            if ($_POST['usernewpass'] != '')
            {
                // Vérification de l'ancien mot de passe
                // bcrypt tire un sel à chaque empreinte : comparer deux empreintes ne
                // réussissait jamais, il faut vérifier le mot de passe contre l'empreinte
                if (user::password_verify($_POST['useroldpass'], $user->fields['login'], $user->fields['password']))
                {
                    // Mots de passes équivalents
                    if ($_POST['usernewpass'] == $_POST['usernewpass_confirm'])
                    {
                        // Complexité ok
                        if (!_PLOOPI_USE_COMPLEXE_PASSWORD || ploopi\security::checkpasswordvalidity($_POST['usernewpass']))
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
            ploopi\fs::makedir(_PLOOPI_PATHDATA._PLOOPI_SEP.'system');

            // photo temporaire présente => copie dans le dossier définitif
            rename($_SESSION['system']['user_photopath'], $user->getphotopath());
            unset($_SESSION['system']['user_photopath']);
        }

        // Suppression photo
        if (ploopi\session::getvar("deletephoto_{$user->fields['id']}")) $user->deletephoto();

        if ($error) ploopi\output::redirect("admin.php?op=profile&error={$error}");
        else ploopi\output::redirect("admin.php?op=profile&reloadsession");
    break;

    case 'profile':
        include './modules/system/public_user.php';
    break;

    default:
    case 'tickets':
        include './modules/system/public_tickets.php';
    break;
}

if ($booSystemPublicP4) echo '</div>';
?>
