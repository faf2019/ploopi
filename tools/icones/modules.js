/*
    Copyright (c) 2026 Ovensia et contributeurs
    GNU General Public License (GPL) version 2 ou ultérieure

    Icônes des modules : même jeu (Tabler Icons) et même palette que l'administration
    (correspondances.js), appliqués à la source, dans install/<module>/files/img/, et aux
    icônes de types de fichiers de img/mimetypes/ (GED, vignettes du socle). Les images
    gardent leur nom et leur taille : le code des modules ne change pas, les modules
    changent de version pour que la mise à jour recopie leurs images.

    Champs : ceux de correspondances.js, plus
      fond     clé de COULEURS : image opaque sur ce fond (images sans transparence,
               ou affichées sur un fond imposé)
      echelle  côté du glyphe rapporté au plus petit côté de l'image (1 par défaut)

    Les GIF et les JPEG sont écrits par ImageMagick (convert) à partir du rendu PNG.

    Ne sont pas remplacées, et restent donc absentes d'ici : les illustrations et
    images de contenu (webedit/default_img.png, nanogallery/icone-video-2.png,
    news2/desk/64/icon.png), les images de fond (booking/menu.png, doc/sep.png), les
    traits d'arborescence de nanogallery, et les GIF que plus rien n'appelle (forms :
    calendar, checked, unchecked, ico_up, ico_pagebreak, download_csv, download_xls ;
    doc : ico_add).
*/

// Types de fichiers, communs à dbreport, directory et forms (img/mime/)
const MIME = [
    { cible: 'mime/csv.png',  glyphe: 'file-type-csv',    couleur: 'neutre' },
    { cible: 'mime/doc.png',  glyphe: 'file-type-doc',    couleur: 'info' },
    { cible: 'mime/html.png', glyphe: 'file-type-html',   couleur: 'neutre' },
    { cible: 'mime/json.png', glyphe: 'json',             couleur: 'neutre' },
    { cible: 'mime/ods.png',  glyphe: 'file-spreadsheet', couleur: 'succes' },
    { cible: 'mime/odt.png',  glyphe: 'file-text',        couleur: 'info' },
    { cible: 'mime/pdf.png',  glyphe: 'file-type-pdf',    couleur: 'danger' },
    { cible: 'mime/png.png',  glyphe: 'file-type-png',    couleur: 'neutre' },
    { cible: 'mime/rtf.png',  glyphe: 'file-text',        couleur: 'info' },
    { cible: 'mime/sql.png',  glyphe: 'file-type-sql',    couleur: 'neutre' },
    { cible: 'mime/sxc.png',  glyphe: 'file-spreadsheet', couleur: 'succes' },
    { cible: 'mime/sxw.png',  glyphe: 'file-text',        couleur: 'info' },
    { cible: 'mime/xls.png',  glyphe: 'file-type-xls',    couleur: 'succes' },
    { cible: 'mime/xml.png',  glyphe: 'file-type-xml',    couleur: 'neutre' }
];

// Seulement les types que le module livre
function mime(arrTypes)
{
    return MIME.filter(i => arrTypes.includes(i.cible.slice(5, -4)));
}

// Sélecteurs de vue des plannings (booking, planning) : vue courante / autres vues
function vues()
{
    const arrVues = { today: 'calendar-event', day: 'calendar-time', week: 'calendar-week', month: 'calendar-month' };
    return Object.entries(arrVues).flatMap(([strVue, strGlyphe]) => [
        { cible: `ico_${strVue}.png`,        glyphe: strGlyphe, couleur: 'primaire' },
        { cible: `ico_${strVue}_notsel.png`, glyphe: strGlyphe, couleur: 'attenue' }
    ]);
}

// Dossiers de la GED : privé (sarcelle), partagé (bleu), public (ambre), protégé (cadenas)
function dossiers(strPrefixe)
{
    return [
        { cible: `${strPrefixe}folder.png`,               glyphe: 'folder',       couleur: 'primaire' },
        { cible: `${strPrefixe}folder_locked.png`,        glyphe: 'folder-lock',  couleur: 'primaire' },
        { cible: `${strPrefixe}folder_shared.png`,        glyphe: 'folder-share', couleur: 'info' },
        { cible: `${strPrefixe}folder_shared_locked.png`, glyphe: 'folder-lock',  couleur: 'info' },
        { cible: `${strPrefixe}folder_public.png`,        glyphe: 'folder',       couleur: 'ambre' },
        { cible: `${strPrefixe}folder_public_locked.png`, glyphe: 'folder-lock',  couleur: 'ambre' },
        { cible: `${strPrefixe}folder_home.png`,          glyphe: 'folder-user',  couleur: 'primaire' }
    ];
}

// Types de fichiers de la GED (doc/img/mimetypes/, en 16 et 24 px)
function typesdoc()
{
    const arrTypes = {
        default: 'file', document: 'file-text', spreadsheet: 'file-spreadsheet',
        presentation: 'presentation', image: 'photo', audio: 'file-music', video: 'movie',
        archive: 'file-zip', html: 'file-type-html'
    };
    return Object.entries(arrTypes).flatMap(([strType, strGlyphe]) => [
        { cible: `mimetypes/${strType}.png`,     glyphe: strGlyphe, couleur: 'neutre' },
        { cible: `mimetypes/ico_${strType}.png`, glyphe: strGlyphe, couleur: 'neutre' }
    ]);
}

// Types de fichiers du socle (img/mimetypes/) : icône 16 px et vignette par défaut
function typessocle()
{
    const arrTypes = {
        default: 'file', document: 'file-text', text: 'file-type-txt', spreadsheet: 'file-spreadsheet',
        presentation: 'presentation', image: 'photo', audio: 'file-music', video: 'movie',
        archive: 'file-zip', package: 'package', html: 'file-type-html', script: 'file-code',
        exec: 'terminal-2', font: 'typography', calendar: 'calendar-event', cd: 'disc',
        certificate: 'certificate'
    };
    return Object.entries(arrTypes).flatMap(([strType, strGlyphe]) => [
        { cible: `ico_${strType}.png`,   glyphe: strGlyphe, couleur: 'neutre' },
        { cible: `thumb_${strType}.png`, glyphe: strGlyphe, couleur: 'attenue', trait: 1.25 }
    ]);
}

const JEUX = [
    { racine: 'install/booking/files/img', icones: [
        ...vues(),
        { cible: 'ico_checkbox.png', glyphe: 'checkbox',    couleur: 'neutre' },
        { cible: 'ico_new.png',      glyphe: 'circle-plus', couleur: 'primaire' },
        { cible: 'ico_trash.png',    glyphe: 'trash',       couleur: 'danger' }
    ] },

    { racine: 'install/dbreport/files/img', icones: [
        { cible: 'arrow_up.png',       glyphe: 'arrow-up',            couleur: 'neutre' },
        { cible: 'arrow_down.png',     glyphe: 'arrow-down',          couleur: 'neutre' },
        { cible: 'ico_new.png',        glyphe: 'file-plus',           couleur: 'primaire' },
        { cible: 'ico_add_table.png',  glyphe: 'table-plus',          couleur: 'primaire' },
        { cible: 'ico_add_field.png',  glyphe: 'text-plus',           couleur: 'primaire' },
        { cible: 'ico_add_field2.png', glyphe: 'variable-plus',       couleur: 'primaire' },
        { cible: 'ico_add_field3.png', glyphe: 'table-column',        couleur: 'primaire' },
        { cible: 'ico_back.png',       glyphe: 'arrow-back-up',       couleur: 'neutre' },
        { cible: 'ico_clone.png',      glyphe: 'copy',                couleur: 'neutre' },
        { cible: 'ico_close.png',      glyphe: 'x',                   couleur: 'danger', trait: 3 },
        { cible: 'ico_delete.png',     glyphe: 'trash',               couleur: 'danger' },
        { cible: 'ico_execute.png',    glyphe: 'player-play',         couleur: 'succes' },
        { cible: 'ico_graph.png',      glyphe: 'chart-bar',           couleur: 'primaire' },
        { cible: 'ico_modify.png',     glyphe: 'pencil',              couleur: 'neutre' },
        { cible: 'ico_param.png',      glyphe: 'settings',            couleur: 'neutre' },
        { cible: 'ico_rename.png',     glyphe: 'forms',               couleur: 'neutre' },
        ...mime(['csv', 'doc', 'html', 'json', 'ods', 'odt', 'pdf', 'rtf', 'sql', 'sxc', 'sxw', 'xls', 'xml'])
    ] },

    { racine: 'install/directory/files/img', icones: [
        { cible: 'ico_add_contact.png',   glyphe: 'user-plus',   couleur: 'primaire' },
        { cible: 'ico_new.png',           glyphe: 'circle-plus', couleur: 'primaire' },
        { cible: 'ico_newlist.png',       glyphe: 'list-check',  couleur: 'primaire' },
        { cible: 'ico_newroot.png',       glyphe: 'folder-plus', couleur: 'primaire' },
        { cible: 'ico_heading.png',       glyphe: 'folder',      couleur: 'primaire' },
        { cible: 'ico_heading_false.png', glyphe: 'folder-off',  couleur: 'attenue' },   // rubrique dont on n'est pas valideur
        { cible: 'ico_viewall.png',       glyphe: 'list-tree',   couleur: 'primaire' },
        { cible: 'ico_open.png',          glyphe: 'id',          couleur: 'neutre' },
        { cible: 'ico_modify.png',        glyphe: 'pencil',      couleur: 'neutre' },
        { cible: 'ico_cut.png',           glyphe: 'scissors',    couleur: 'neutre' },
        { cible: 'ico_delete.png',        glyphe: 'trash',       couleur: 'danger' },
        { cible: 'ico_fav_add.png',       glyphe: 'star',        couleur: 'attenue' },             // pas encore favori
        { cible: 'ico_fav_modify.png',    glyphe: 'star',        couleur: 'ambre', rempli: true }, // déjà favori
        { cible: 'ico_email.png',         glyphe: 'mail',        couleur: 'neutre' },
        { cible: 'ico_ticket.png',        glyphe: 'mail-plus',   couleur: 'primaire' },
        { cible: 'ico_phone.png',         glyphe: 'device-mobile', couleur: 'neutre' },
        { cible: 'ico_print.png',         glyphe: 'printer',     couleur: 'neutre' },
        { cible: 'ico_import.png',        glyphe: 'file-import', couleur: 'primaire' },
        { cible: 'ico_search.png',        glyphe: 'search',      couleur: 'neutre' },
        { cible: 'ico_loupe.png',         glyphe: 'search',      couleur: 'neutre' },
        { cible: 'nopic.gif',             glyphe: 'user',        couleur: 'attenue', fond: 'fond', echelle: 0.7, trait: 1.5 },
        ...mime(['csv', 'xls', 'xml'])
    ] },

    { racine: 'install/doc/files/img', icones: [
        ...dossiers(''),
        ...dossiers('ico_'),
        { cible: 'ico_home.png',           glyphe: 'home',          couleur: 'primaire' },
        { cible: 'ico_newfolder.png',      glyphe: 'folder-plus',   couleur: 'primaire' },
        { cible: 'ico_newfolder_grey.png', glyphe: 'folder-plus',   couleur: 'attenue' },
        { cible: 'ico_newfile.png',        glyphe: 'file-plus',     couleur: 'primaire' },
        { cible: 'ico_newfile_grey.png',   glyphe: 'file-plus',     couleur: 'attenue' },
        { cible: 'ico_main.png',           glyphe: 'file-pencil',   couleur: 'neutre' },
        { cible: 'ico_modify.png',         glyphe: 'pencil',        couleur: 'neutre' },
        { cible: 'ico_modify_grey.png',    glyphe: 'pencil',        couleur: 'attenue' },
        { cible: 'ico_trash.png',          glyphe: 'trash',         couleur: 'danger' },
        { cible: 'ico_trash_grey.png',     glyphe: 'trash',         couleur: 'attenue' },
        { cible: 'ico_validate.png',       glyphe: 'circle-check',  couleur: 'succes' },
        { cible: 'ico_open.png',           glyphe: 'eye',           couleur: 'neutre' },
        { cible: 'ico_download.png',       glyphe: 'download',      couleur: 'neutre' },
        { cible: 'ico_download_zip.png',   glyphe: 'file-zip',      couleur: 'neutre' },
        { cible: 'ico_history.png',        glyphe: 'history',       couleur: 'neutre' },
        { cible: 'ico_keywords.png',       glyphe: 'tags',          couleur: 'neutre' },
        { cible: 'ico_meta.png',           glyphe: 'list-details',  couleur: 'neutre' },
        { cible: 'ico_search.png',         glyphe: 'search',        couleur: 'neutre' },
        { cible: 'edit-find.png',          glyphe: 'file-search',   couleur: 'neutre' },
        { cible: 'ico_help.png',           glyphe: 'help-circle',   couleur: 'info' },
        { cible: 'ico_pdf.png',            glyphe: 'file-type-pdf', couleur: 'danger' },
        { cible: 'pdf.png',                glyphe: 'file-type-pdf', couleur: 'danger' },
        { cible: 'ico_show_list.png',      glyphe: 'list',          couleur: 'neutre' },
        { cible: 'ico_show_thumb.png',     glyphe: 'layout-grid',   couleur: 'neutre' },
        { cible: 'notallowed.png',         glyphe: 'ban',           couleur: 'danger', trait: 2.5 },
        // En-têtes des fiches (dossier, fichier, recherche)
        { cible: 'newfolder.png',          glyphe: 'folder-plus',   couleur: 'primaire' },
        { cible: 'newfile.png',            glyphe: 'file-plus',     couleur: 'primaire' },
        { cible: 'edit.png',               glyphe: 'file-pencil',   couleur: 'primaire' },
        { cible: 'search.png',             glyphe: 'search',        couleur: 'primaire' },
        { cible: 'download.png',           glyphe: 'download',      couleur: 'neutre' },
        { cible: 'downloadzip.png',        glyphe: 'file-zip',      couleur: 'neutre' },
        { cible: 'send.png',               glyphe: 'upload',        couleur: 'neutre' },
        ...typesdoc()
    ] },

    { racine: 'install/forms/files/img', icones: [
        { cible: 'arrow_up.png',   glyphe: 'chevron-up',   couleur: 'neutre', trait: 2.5 },   // tri des réponses
        { cible: 'arrow_down.png', glyphe: 'chevron-down', couleur: 'neutre', trait: 2.5 },
        { cible: 'ico_up2.png',    glyphe: 'arrow-up',     couleur: 'neutre' },               // ordre des champs
        { cible: 'ico_down2.png',  glyphe: 'arrow-down',   couleur: 'neutre' },
        { cible: 'ico_clone.png',  glyphe: 'copy',         couleur: 'neutre' },
        { cible: 'ico_display.png', glyphe: 'eye',         couleur: 'neutre' },
        { cible: 'ico_preview.png', glyphe: 'eye',         couleur: 'neutre' },
        { cible: 'ico_view.png',   glyphe: 'table',        couleur: 'neutre' },
        { cible: 'ico_modify.png', glyphe: 'pencil',       couleur: 'neutre' },
        { cible: 'ico_renew.png',  glyphe: 'database-cog', couleur: 'neutre' },
        { cible: 'ico_trash.png',  glyphe: 'trash',        couleur: 'danger' },
        { cible: 'mail.png',       glyphe: 'mail',         couleur: 'neutre' },
        { cible: 'link.gif',       glyphe: 'paperclip',    couleur: 'neutre' },
        ...mime(['csv', 'doc', 'html', 'json', 'ods', 'odt', 'pdf', 'png', 'rtf', 'sql', 'sxc', 'sxw', 'xls', 'xml'])
    ] },

    // Les images « _alpha » sont transparentes, les autres sur fond blanc ; toutes sont
    // réduites à l'affichage (20 à 30 px)
    { racine: 'install/nanogallery/files/img', icones: [
        ...[
            ['angle_double-left', 'chevrons-left', 'neutre'], ['edit', 'pencil', 'neutre'],
            ['eye', 'eye', 'neutre'], ['folder', 'folder', 'primaire'], ['forbidden', 'ban', 'danger'],
            ['images', 'photo', 'primaire'], ['trash', 'trash', 'danger']
        ].flatMap(([strNom, strGlyphe, strCouleur]) => [
            { cible: `${strNom}_alpha.png`, glyphe: strGlyphe, couleur: strCouleur },
            { cible: `${strNom}.png`,       glyphe: strGlyphe, couleur: strCouleur, fond: 'blanc' }
        ]),
        { cible: 'Folder-Icon.jpg',             glyphe: 'folder',       couleur: 'primaire', fond: 'fond', echelle: 0.5, trait: 1.5 },   // album sans vignette
        { cible: 'treeview/plus3.png',          glyphe: 'square-plus',  couleur: 'neutre', trait: 1.5 },
        { cible: 'treeview/plusbottom3.png',    glyphe: 'square-plus',  couleur: 'neutre', trait: 1.5 },
        { cible: 'treeview/minus3.png',         glyphe: 'square-minus', couleur: 'neutre', trait: 1.5 },
        { cible: 'treeview/minusbottom3.png',   glyphe: 'square-minus', couleur: 'neutre', trait: 1.5 }
    ] },

    { racine: 'install/news2/files/img', icones: [
        ...[
            ['edit', 'pencil', 'neutre'], ['folder', 'folder', 'primaire'], ['forbidden', 'ban', 'danger'],
            ['image', 'photo', 'primaire'], ['publish', 'world-upload', 'succes'], ['trash', 'trash', 'danger'],
            ['withdraw', 'world-off', 'alerte']
        ].flatMap(([strNom, strGlyphe, strCouleur]) => [
            { cible: `${strNom}_alpha.png`, glyphe: strGlyphe, couleur: strCouleur },
            { cible: `${strNom}.png`,       glyphe: strGlyphe, couleur: strCouleur, fond: 'blanc' }
        ]),
        { cible: 'find_image-32_clr.png',  glyphe: 'photo-search', couleur: 'primaire' },
        { cible: 'ico_choose_article.png', glyphe: 'file-search',  couleur: 'neutre' },
        { cible: 'ico_modify.png',         glyphe: 'pencil',       couleur: 'neutre' },
        { cible: 'ico_publish.png',        glyphe: 'world-upload', couleur: 'succes' },
        { cible: 'ico_withdraw.png',       glyphe: 'world-off',    couleur: 'alerte' },
        { cible: 'ico_trash.png',          glyphe: 'trash',        couleur: 'danger' }
    ] },

    // ico_user et ico_group sont posées sur la couleur de la ressource (background-color)
    { racine: 'install/planning/files/img', icones: [
        ...vues(),
        { cible: 'ico_checkbox.png', glyphe: 'checkbox',    couleur: 'neutre' },
        { cible: 'ico_new.png',      glyphe: 'circle-plus', couleur: 'primaire' },
        { cible: 'ico_printer.png',  glyphe: 'printer',     couleur: 'neutre' },
        { cible: 'ico_search.png',   glyphe: 'search',      couleur: 'neutre' },
        { cible: 'ico_trash.png',    glyphe: 'trash',       couleur: 'danger' },
        { cible: 'ico_group.png',    glyphe: 'users',       couleur: 'neutre', trait: 2.5 },
        { cible: 'ico_user.png',     glyphe: 'user',        couleur: 'neutre', rempli: true, echelle: 2 }
    ] },

    { racine: 'install/webedit/files/img', icones: [
        { cible: 'base.png',               glyphe: 'world',             couleur: 'primaire' },
        { cible: 'base_add.png',           glyphe: 'world-plus',        couleur: 'primaire' },
        { cible: 'folder.png',             glyphe: 'folder',            couleur: 'primaire' },
        { cible: 'folder_add.png',         glyphe: 'folder-plus',       couleur: 'primaire' },
        { cible: 'folder_del.png',         glyphe: 'folder-x',          couleur: 'danger' },
        { cible: 'blocs.png',              glyphe: 'box-multiple',      couleur: 'primaire' },
        { cible: 'chart.png',              glyphe: 'chart-bar',         couleur: 'primaire' },
        { cible: 'doc.png',                glyphe: 'file-text',         couleur: 'neutre' },
        { cible: 'doc0.png',               glyphe: 'file-check',        couleur: 'succes' },   // article publié
        { cible: 'doc1.png',               glyphe: 'file-pencil',       couleur: 'alerte' },   // modifié depuis la publication
        { cible: 'doc2.png',               glyphe: 'file-off',          couleur: 'danger' },   // non publié
        { cible: 'doc_add.png',            glyphe: 'file-plus',         couleur: 'primaire' },
        { cible: 'doc_del.png',            glyphe: 'file-x',            couleur: 'danger' },
        { cible: 'doc_move.png',           glyphe: 'file-arrow-right',  couleur: 'neutre' },
        { cible: 'fullscreen.png',         glyphe: 'maximize',          couleur: 'neutre' },
        { cible: 'ico_choose_article.png', glyphe: 'file-search',       couleur: 'neutre' },
        { cible: 'ico_clear_article.png',  glyphe: 'eraser',            couleur: 'neutre' },
        { cible: 'ico_modify.png',         glyphe: 'pencil',            couleur: 'neutre' },
        { cible: 'ico_publish.png',        glyphe: 'world-upload',      couleur: 'succes' },
        { cible: 'ico_withdraw.png',       glyphe: 'world-off',         couleur: 'alerte' },
        { cible: 'ico_trash.png',          glyphe: 'trash',             couleur: 'danger' },
        { cible: 'radio-off.png',          glyphe: 'circle',            couleur: 'attenue' },
        { cible: 'radio-on.png',           glyphe: 'circle-dot',        couleur: 'primaire', rempli: true }
    ] },

    // Les ico_link* (8 px) suivent les liens dans le texte des pages
    { racine: 'install/wiki/files/img', icones: [
        { cible: 'ico_view.png',        glyphe: 'eye',           couleur: 'neutre' },
        { cible: 'ico_modify.png',      glyphe: 'pencil',        couleur: 'neutre' },
        { cible: 'ico_rename.png',      glyphe: 'forms',         couleur: 'neutre' },
        { cible: 'ico_history.png',     glyphe: 'history',       couleur: 'neutre' },
        { cible: 'ico_printer.png',     glyphe: 'printer',       couleur: 'neutre' },
        { cible: 'ico_lock.png',        glyphe: 'lock',          couleur: 'neutre' },
        { cible: 'ico_unlock.png',      glyphe: 'lock-open',     couleur: 'neutre' },
        { cible: 'ico_delete.png',      glyphe: 'trash',         couleur: 'danger' },
        { cible: 'ico_link.png',        glyphe: 'link',          couleur: 'primaire', trait: 3 },
        { cible: 'ico_link_anchor.png', glyphe: 'hash',          couleur: 'primaire', trait: 3 },
        { cible: 'ico_link_ext.png',    glyphe: 'external-link', couleur: 'primaire', trait: 3 },
        { cible: 'ico_new.png',         glyphe: 'plus',          couleur: 'primaire', trait: 3 }
    ] },

    { racine: 'img/mimetypes', icones: typessocle() }
];

module.exports = { JEUX };
