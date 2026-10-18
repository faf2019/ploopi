/**
 * Génération de la TOC d'une page wiki
 */
wiki_toc = function() {
    var toc = null;
    var div = null;
    var ul = new Array();
    var c = 0;

    // Récupération des titres de rubriques
    var headers = jQuery('#wiki_page h1,h2,h3,h4');
    console.log(headers.length);

    // Nombre minimal de titres pour afficher la TOC
    if (headers.length < 5) return;

    ul[0] = $('<div></div>').addClass('wiki_page_toc');

    // Insertion de la TOC dans le contenu
    jQuery('#wiki_page').prepend(ul[0]);

    // Pour chaque titre de la page
    headers.each(function(index) {
        title = this;

        // Lien courant
        var a = null;
        // Conteneur actuel des liens
        var linkcontainer = null;
        // Profondeur de titre
        var h = parseInt(title.tagName.substring(1,2));

        // Création des sous-listes non définies
        for(var i = 1; i < h; i++) {
            if (!ul[i]) {
                ul[i] = jQuery('<ul></ul>');
                jQuery(ul[i-1]).append(ul[i]);
            }
        }

        if (h > 1) {
            linkcontainer = jQuery('<li></li>');
            // Création d'un nouvel item
            jQuery(ul[h-1]).append(linkcontainer);
        }
        else linkcontainer = ul[0];

        // Suppression des listes terminées
        for(var i = h; i <= 3; i++) {
            ul[i] = null;
        }

        // Insertion d'un lien vers le titre
        jQuery(linkcontainer).append(
            jQuery('<a></a>').attr({href:'#toc'+c}).addClass('h'+h).html(jQuery(title).html())
        );

        // Insertion d'une ancre avant le titre concerné
        jQuery(title).before(
            jQuery('<a></a>').attr({name:'toc'+c})
        );

        c++;
    });
};

/**
 * Initialisations par attributs (aucun script en ligne : CSP stricte) : coloration du
 * code, sommaire de la page (data-wiki-toc), barre d'outils de l'éditeur
 * (data-wiki-toolbar : adresse de l'aide)
 */
jQuery(function() {
    if (window.hljs) hljs.initHighlighting();
    if (document.querySelector('[data-wiki-toc]')) wiki_toc();

    var textarea = document.querySelector('textarea[data-wiki-toolbar]');
    if (textarea && window.jsToolBar) {
        var toolbar = new jsToolBar(textarea);
        var aide = jQuery('<a href="#">Aide</a>').attr({ 'data-ploopi-topopup': 'popup_wiki_help', 'data-ploopi-width': '350', 'data-ploopi-script': textarea.getAttribute('data-wiki-toolbar') });
        toolbar.setHelpLink('Formatage du texte: ' + aide[0].outerHTML);
        toolbar.draw();
    }
});

ploopi.actions.wiki_print = function(el, event, url) {
    ploopi.openwin(url, 800, 600);
};
