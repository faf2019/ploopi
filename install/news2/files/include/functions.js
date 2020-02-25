
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
var id_field_url;
SetUrl = function(url) { if ($(id_field_url)) $(id_field_url).value = url; };

