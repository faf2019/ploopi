
/**
 * Sélectionne un article ou une rubrique en lien depuis une news
 */
function news2_select_article_or_heading(url, title)
{
    console.log("Appel de news2_select_article_or_heading de news2");
    document.getElementById('news_url').value = url;
    ploopi.popup.hide('news2_popup_selectredirect');
}

/**
 * Recherche image
 */

function news2_search_img(elem) {
    ploopi.openwin(news_doc_selectimage+'&target='+elem, 800, 600, 'popup');
    return false;
}

/**
 * Actions appelées par attributs data-ploopi-action (js/actions.js du socle), sans
 * gestionnaire en ligne (CSP stricte)
 */
ploopi.actions.news2_search_img = function(el, event, elem) {
    news2_search_img(elem);
};

ploopi.actions.news2_selectredirect = function(el, event) {
    ploopi.popup.show(ploopi.xhr.send('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&entity=admin&action=op_selectredirect', false), 300, event, true, 'news2_popup_selectredirect');
};

ploopi.actions.news2_select_article_or_heading = function(el, event, url, title) {
    event.preventDefault();
    news2_select_article_or_heading(url, title);
};
