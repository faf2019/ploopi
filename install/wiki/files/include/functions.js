/**
 * Génération de la TOC d'une page wiki
 */
wiki_toc = function() {
    var toc = null;
    var div = null;
    var ul = new Array();
    var c = 0;

    // Récupération des titres de rubriques
    var headers = $('wiki_page').select('h1,h2,h3,h4');
    
    // Nombre minimal de titres pour afficher la TOC
    if (headers.length < 5) return;

    // Insertion de la TOC dans le contenu
    $('wiki_page').insert({
        top: ul[0] = new Element('div', {
            'class': 'wiki_page_toc'
        })
    });
    
    // Pour chaque titre de la page
    headers.each(function(title) {
        // Lien courant
        var a = null;
        // Conteneur actuel des liens
        var linkcontainer = null;
        // Profondeur de titre
        var h = parseInt(title.tagName.substring(1,2));

        // Création des sous-listes non définies
        for(var i = 1; i < h; i++) {
            if (!ul[i]) {
                ul[i-1].insert({
                    bottom: ul[i] = new Element('ul')
                });
            }
        }
        
        if (h > 1) {
            // Création d'un nouvel item
            ul[h-1].insert({
                bottom: linkcontainer = new Element('li')
            });
        }
        else linkcontainer = ul[0];

        // Suppression des listes terminées
        for(var i = h; i <= 3; i++) {
            ul[i] = null;
        }

        // Insertion d'un lien vers le titre
        linkcontainer.insert({
            bottom: a = new Element('a', {
                'class': 'h'+h, 
                'href': '#toc'+c 
            })
        });
        
        a.innerHTML = title.innerHTML;

        // Insertion d'une ancre avant le titre concerné
        title.insert({
            before: new Element('a', {
                'name': 'toc'+c 
            })
        });
        
        c++;
    });
};
