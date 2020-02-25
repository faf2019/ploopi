
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
