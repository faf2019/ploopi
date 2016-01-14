<?php
/**
 * Gestion du bloc de menu
 *
 * @package espacedoc
 * @subpackage block
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Initialisation du module
 */

ploopi_init_module('espacedoc', false, false, false);


/**
 * Si on est connecté au module mais qu'aucun menu n'est sélectionné
 */
if ($menu_moduleid == $_SESSION['ploopi']['moduleid'] && empty($_SESSION['espacedoc']['espacedoc_menu']))
{
    if (ploopi_isactionallowed(_ESPACEDOC_ACTION_DOCUMENTS))
    {
        $_SESSION['espacedoc']['espacedoc_menu'] = 'recherche';
        $_SESSION['espacedoc']['espacedoc_recherche_tab'] = 'derniers';

    }
    elseif (ploopi_isactionallowed(_ESPACEDOC_ACTION_ADMIN))
    {
        $_SESSION['espacedoc']['espacedoc_menu'] = 'admin';
        $_SESSION['espacedoc']['espacedoc_admin_tab'] = 'theme';
    }
    else
    {
        $_SESSION['espacedoc']['espacedoc_menu'] = 'recherche';
        $_SESSION['espacedoc']['espacedoc_recherche_tab'] = 'derniers';

    }
}

/**
 * Menus
 */

if (isset($_GET['espacedoc_menu']))
{
    $_SESSION['espacedoc']['espacedoc_menu'] = $_GET['espacedoc_menu'];
    unset($_SESSION['espacedoc']['espacedoc_documents_tab']);
    unset($_SESSION['espacedoc']['espacedoc_admin_tab']);
}

if (ploopi_isactionallowed(_ESPACEDOC_ACTION_DOCUMENTS, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
    $block->addmenu('Documents', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&espacedoc_menu=documents"), $menu_moduleid == $_SESSION['ploopi']['moduleid'] && $_SESSION['espacedoc']['espacedoc_menu'] == 'documents');

$block->addmenu('Recherche documentaire', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&espacedoc_menu=recherche"), $menu_moduleid == $_SESSION['ploopi']['moduleid'] && $_SESSION['espacedoc']['espacedoc_menu'] == 'recherche');

if (ploopi_isactionallowed(_ESPACEDOC_ACTION_ADMIN, $_SESSION['ploopi']['workspaceid'], $menu_moduleid))
    $block->addmenu('<b>Administration</b>', ploopi_urlencode("admin.php?ploopi_moduleid={$menu_moduleid}&ploopi_action=public&espacedoc_menu=admin"), $menu_moduleid == $_SESSION['ploopi']['moduleid'] && $_SESSION['espacedoc']['espacedoc_menu'] == 'admin');

?>
