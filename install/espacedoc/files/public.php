<?php
/**
 * Gestion de l'interface publique (dossiers/stats)
 *
 * @package espacedoc
 * @subpackage public
 * @author Stéphane Escaich
 * @copyright SZSIC Metz / OVENSIA
 */

/**
 * Initialisation du module
 */

ploopi_init_module('espacedoc');

/**
 * On récupère la variable op
 */

$op = (isset($_REQUEST['op'])) ? $_REQUEST['op'] : '';

switch($_SESSION['espacedoc']['espacedoc_menu'])
{

    case 'documents':
        if (!ploopi_isactionallowed(_ESPACEDOC_ACTION_DOCUMENTS)) ploopi_die();

        echo $skin->create_pagetitle("{$_SESSION['ploopi']['modulelabel']} - Dossiers");

        if (isset($_GET['espacedoc_documents_tab'])) $_SESSION['espacedoc']['espacedoc_documents_tab'] = $_GET['espacedoc_documents_tab'];
        if (empty($_SESSION['espacedoc']['espacedoc_documents_tab'])) $_SESSION['espacedoc']['espacedoc_documents_tab'] = 'ajouter';

        /**
         * Définition des onglets
         */
        $tabs = array();
        $tabs['ajouter'] =
            array(
                'title' => 'Mise en ligne d\'un document',
                'url' => "admin.php?espacedoc_menu=documents&espacedoc_documents_tab=ajouter"
            );

        $tabs['modifier'] =
            array(
                'title' => 'Modification d\'un document',
                'url' => "admin.php?ploopi_action=public&espacedoc_menu=documents&espacedoc_documents_tab=modifier"
            );

        $tabs['supprimer'] =
            array(
                'title' =>  'Retrait de documents',
                'url' => "admin.php?ploopi_action=public&espacedoc_menu=documents&espacedoc_documents_tab=supprimer"
            );

        echo $skin->create_tabs($tabs, $_SESSION['espacedoc']['espacedoc_documents_tab']);

        echo $skin->open_simplebloc();

        switch($_SESSION['espacedoc']['espacedoc_documents_tab'])
        {
            case 'ajouter':
                include './modules/espacedoc/public_document_ajouter.php';
            break;

            case 'modifier':
                include './modules/espacedoc/public_document_supprimer.php';
            break;

            case 'supprimer':
                include './modules/espacedoc/public_document_supprimer.php';
            break;
        }

        echo $skin->close_simplebloc();
    break;

    case 'recherche':
        echo $skin->create_pagetitle("{$_SESSION['ploopi']['modulelabel']} - Recherche documentaire");

        if (isset($_GET['espacedoc_recherche_tab'])) $_SESSION['espacedoc']['espacedoc_recherche_tab'] = $_GET['espacedoc_recherche_tab'];
        if (empty($_SESSION['espacedoc']['espacedoc_recherche_tab'])) $_SESSION['espacedoc']['espacedoc_recherche_tab'] = 'derniers';

        /**
         * Définition des onglets
         */
        $tabs = array();
        $tabs['derniers'] =
            array(
                'title' => 'Dernières mises en ligne',
                'url' => "admin.php?ploopi_action=public&espacedoc_menu=recherche&espacedoc_recherche_tab=derniers"
            );

        $tabs['theme'] =
            array(
                'title' => 'Recherche thématique',
                'url' => "admin.php?ploopi_action=public&espacedoc_menu=recherche&espacedoc_recherche_tab=theme"
            );

        echo $skin->create_tabs($tabs, $_SESSION['espacedoc']['espacedoc_recherche_tab']);

        echo $skin->open_simplebloc();

        switch($_SESSION['espacedoc']['espacedoc_recherche_tab'])
        {
            case 'derniers':
                include './modules/espacedoc/public_recherche_derniers.php';
            break;

            case 'theme':
                include './modules/espacedoc/public_recherche_theme.php';
            break;
        }

        echo $skin->close_simplebloc();
    break;


    case 'admin':
        if (!ploopi_isactionallowed(_ESPACEDOC_ACTION_ADMIN)) ploopi_die();
        echo $skin->create_pagetitle("{$_SESSION['ploopi']['modulelabel']} - Administration");

        if (isset($_GET['espacedoc_admin_tab'])) $_SESSION['espacedoc']['espacedoc_admin_tab'] = $_GET['espacedoc_admin_tab'];
        if (empty($_SESSION['espacedoc']['espacedoc_admin_tab'])) $_SESSION['espacedoc']['espacedoc_admin_tab'] = '';

        /**
         * Définition des onglets
         */
        $tabs = array();
        $tabs['theme'] =
            array(
                'title' => ploopi_getparam('espacedoc_theme').'s',
                'url' => "admin.php?ploopi_action=public&espacedoc_menu=admin&espacedoc_admin_tab=theme"
            );

        $tabs['sstheme'] =
            array(
                'title' => ploopi_getparam('espacedoc_sstheme').'s',
                'url' => "admin.php?ploopi_action=public&espacedoc_menu=admin&espacedoc_admin_tab=sstheme"
            );

        echo $skin->create_tabs($tabs, $_SESSION['espacedoc']['espacedoc_admin_tab']);

        echo $skin->open_simplebloc();

        switch($_SESSION['espacedoc']['espacedoc_admin_tab'])
        {
            case 'theme':
                include './modules/espacedoc/admin_theme.php';
            break;

            case 'sstheme':
                include './modules/espacedoc/admin_sstheme.php';
            break;
        }

        echo $skin->close_simplebloc();


    break;

}
?>
