<?php
ploopi\module::init('wiki');

echo ploopi\skin::get()->create_pagetitle(ploopi\str::htmlentities($_SESSION['ploopi']['modulelabel']));

// Menu principal
$strWikiMenu = isset($_GET['wiki_menu']) ? $_GET['wiki_menu'] : '';

switch($strWikiMenu)
{
    case 'index_title':
    case 'index_date':
        include_once './modules/wiki/public_index.php';
    break;

    case 'reindex':
        include_once './modules/wiki/public_reindex.php';
    break;

    default: // navigation
        include_once './modules/wiki/public_view.php';
    break;

}
?>
