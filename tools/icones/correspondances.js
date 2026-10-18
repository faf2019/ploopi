/*
    Copyright (c) 2026 Ovensia et contributeurs
    GNU General Public License (GPL) version 2 ou ultérieure

    Jeu d'icônes de l'administration pour le gabarit ploopi3 : chaque image du gabarit
    est remplacée par un glyphe Tabler Icons (MIT, voir tabler/LICENSE), rastérisé par
    generer.js à la taille exacte de l'image d'origine. Les noms de fichiers ne changent
    pas : le code qui les appelle ne voit aucune différence, et ploopi2 garde les siennes.

    Champs :
      cible   chemin relatif à templates/backoffice/ploopi3/img/
      glyphe  nom du glyphe Tabler (tabler/outline/<glyphe>.svg, ou filled/ si rempli)
      couleur clé de COULEURS ci-dessous
      rempli  glyphe plein (tabler/filled/)
      taille  [largeur, hauteur] si l'image n'existe pas encore (sinon : taille d'origine)
      alignement 'gauche' pour les nœuds d'arborescence (marge à droite de l'image)
      trait   épaisseur du trait (2 par défaut, en unités de la grille 24)
      pastille diamètre d'un disque plein, au lieu d'un glyphe (puces d'état)

    Une cible en .svg reçoit le glyphe vectoriel coloré (images de fond des boutons).
*/

// Couleurs : celles des variables --p3-* de css/base_main.css
const COULEURS = {
    neutre:    '#4a5a63',   // actions courantes, contenus
    primaire:  '#2c6e7f',   // navigation, objets de l'administration
    attenue:   '#9aa8b0',   // action indisponible, case non cochée
    danger:    '#b42318',   // suppression, interdiction, administrateur système
    succes:    '#1e7b45',   // validé, coché, utilisateur, tableur
    alerte:    '#b54708',   // avertissement
    ambre:     '#b08800',   // annotations, gestionnaire d'espace
    orange:    '#c4320a',   // administrateur d'espace
    info:      '#1f5f99',   // information, lu
    violet:    '#6941c6',   // validation
    blanc:     '#ffffff',   // sur fond sombre (titre des fenêtres)
    fond:      '#eef1f4',   // fond des images opaques (photo absente, album sans vignette)
};

const ICONES = [
    // Barres d'outils de l'administration (tuiles 24 px)
    { cible: 'system/icons/tab_workspace.png',             glyphe: 'building',               couleur: 'primaire' },
    { cible: 'system/icons/tab_workspace_child.png',       glyphe: 'building-plus',          couleur: 'primaire' },
    { cible: 'system/icons/tab_workspace_copy.png',        glyphe: 'copy',                   couleur: 'primaire' },
    { cible: 'system/icons/tab_workspace_copy_gray.png',   glyphe: 'copy',                   couleur: 'attenue' },
    { cible: 'system/icons/tab_workspace_delete.png',      glyphe: 'trash',                  couleur: 'danger' },
    { cible: 'system/icons/tab_workspace_delete_gray.png', glyphe: 'trash',                  couleur: 'attenue' },
    { cible: 'system/icons/tab_group.png',                 glyphe: 'users',                  couleur: 'primaire' },
    { cible: 'system/icons/tab_group_child.png',           glyphe: 'users-plus',             couleur: 'primaire' },
    { cible: 'system/icons/tab_group_copy.png',            glyphe: 'copy',                   couleur: 'primaire' },
    { cible: 'system/icons/tab_group_copy_gray.png',       glyphe: 'copy',                   couleur: 'attenue' },
    { cible: 'system/icons/tab_group_delete.png',          glyphe: 'users-minus',            couleur: 'danger' },
    { cible: 'system/icons/tab_group_delete_gray.png',     glyphe: 'users-minus',            couleur: 'attenue' },
    { cible: 'system/icons/tab_module.png',                glyphe: 'puzzle',                 couleur: 'primaire' },
    { cible: 'system/icons/tab_systemparams.png',          glyphe: 'adjustments-horizontal', couleur: 'primaire' },
    { cible: 'system/icons/tab_directory.png',             glyphe: 'address-book',           couleur: 'primaire' },
    { cible: 'system/icons/tab_user.png',                  glyphe: 'user-check',             couleur: 'primaire' },
    { cible: 'system/icons/tab_role.png',                  glyphe: 'shield-lock',            couleur: 'primaire' },
    { cible: 'system/icons/tab_install_module.png',        glyphe: 'package-import',         couleur: 'primaire' },
    { cible: 'system/icons/tab_systemupdate.png',          glyphe: 'refresh',                couleur: 'primaire' },
    { cible: 'system/icons/tab_tools.png',                 glyphe: 'tool',                   couleur: 'primaire' },
    { cible: 'system/icons/tab_logs.png',                  glyphe: 'history',                couleur: 'primaire' },
    { cible: 'system/icons/tab_profile.png',               glyphe: 'id',                     couleur: 'primaire' },

    // Outils du système
    { cible: 'system/tools/tool_diagnostic.png',  glyphe: 'stethoscope',     couleur: 'primaire' },
    { cible: 'system/tools/tool_phpinfo.png',     glyphe: 'brand-php',       couleur: 'primaire' },
    { cible: 'system/tools/tool_serverload.png',  glyphe: 'activity',        couleur: 'primaire' },
    { cible: 'system/tools/tool_logactions.png',  glyphe: 'history',         couleur: 'primaire' },
    { cible: 'system/tools/tool_logusers.png',    glyphe: 'users',           couleur: 'primaire' },
    { cible: 'system/tools/tool_sqldump.png',     glyphe: 'database-export', couleur: 'primaire' },

    // Niveaux d'administration : un glyphe par niveau, pas seulement une couleur
    { cible: 'system/adminlevels/level_user.png',         glyphe: 'user',        couleur: 'succes' },
    { cible: 'system/adminlevels/level_groupmanager.png', glyphe: 'user-star',   couleur: 'ambre' },
    { cible: 'system/adminlevels/level_groupadmin.png',   glyphe: 'user-cog',    couleur: 'orange' },
    { cible: 'system/adminlevels/level_systemadmin.png',  glyphe: 'user-shield', couleur: 'danger' },

    // Actions des listes
    { cible: 'system/btn_edit.png',    glyphe: 'pencil',   couleur: 'neutre' },
    { cible: 'system/btn_delete.png',  glyphe: 'trash',    couleur: 'danger' },
    { cible: 'system/btn_cut.png',     glyphe: 'scissors', couleur: 'neutre' },
    { cible: 'system/btn_attach.png',  glyphe: 'link',     couleur: 'neutre' },
    { cible: 'system/btn_noway.png',   glyphe: 'ban',      couleur: 'danger' },
    { cible: 'system/check_on.png',    glyphe: 'square-check', couleur: 'succes' },
    { cible: 'system/check_off.png',   glyphe: 'square',   couleur: 'attenue' },
    { cible: 'system/arrow_up.png',    glyphe: 'arrow-up',    couleur: 'neutre' },
    { cible: 'system/arrow_down.png',  glyphe: 'arrow-down',  couleur: 'neutre' },
    { cible: 'system/arrow_all.png',   glyphe: 'corner-down-right', couleur: 'neutre' },   // « appliquer à la sélection »
    { cible: 'system/link.png',        glyphe: 'link',     couleur: 'neutre' },
    { cible: 'system/fullscreen.png',  glyphe: 'maximize', couleur: 'neutre' },
    { cible: 'system/comment.png',     glyphe: 'message',  couleur: 'neutre' },
    { cible: 'system/annotation.png',  glyphe: 'note',     couleur: 'ambre' },
    { cible: 'system/attention.png',   glyphe: 'alert-triangle', couleur: 'alerte' },
    { cible: 'system/information.png', glyphe: 'info-circle',    couleur: 'info' },

    // Utilisateurs, groupes
    { cible: 'system/ico_user.png',      glyphe: 'user',         couleur: 'neutre' },
    { cible: 'system/ico_group.png',     glyphe: 'users',        couleur: 'primaire' },
    { cible: 'system/ico_workgroup.png', glyphe: 'users-group',  couleur: 'primaire' },
    { cible: 'system/ico_role.png',      glyphe: 'shield-lock',  couleur: 'primaire' },

    // Messagerie (email.png remplace email.gif, appelé par les listes d'utilisateurs)
    { cible: 'system/email.png',          glyphe: 'mail',           couleur: 'neutre', taille: [16, 16] },
    { cible: 'system/email_new.png',      glyphe: 'mail',           couleur: 'primaire', rempli: true },
    { cible: 'system/email_read.png',     glyphe: 'mail-opened',    couleur: 'neutre' },
    { cible: 'system/email_reply.png',    glyphe: 'arrow-back-up',  couleur: 'neutre' },
    { cible: 'system/email_quote.png',    glyphe: 'quote',          couleur: 'neutre' },
    { cible: 'system/email_modify.png',   glyphe: 'pencil',         couleur: 'neutre' },
    { cible: 'system/email_delete.png',   glyphe: 'trash',          couleur: 'danger' },
    { cible: 'system/email_validate.png', glyphe: 'mail-check',     couleur: 'succes' },
    { cible: 'tickets/newticket.png',     glyphe: 'mail-plus',      couleur: 'primaire' },
    { cible: 'tickets/incomingbox.png',   glyphe: 'inbox',          couleur: 'primaire' },
    { cible: 'tickets/sendbox.png',       glyphe: 'send',           couleur: 'primaire' },
    { cible: 'tickets/alltickets.png',    glyphe: 'messages',       couleur: 'primaire' },
    { cible: 'tickets/attachment.png',    glyphe: 'paperclip',      couleur: 'neutre' },
    { cible: 'tickets/search.png',        glyphe: 'search',         couleur: 'neutre' },
    { cible: 'tickets/newmail.png',       glyphe: 'mail',           couleur: 'primaire' },

    // Puces d'état (p_<couleur>.png, choisies dynamiquement)
    { cible: 'system/p_green.png',  pastille: 8, couleur: 'succes' },
    { cible: 'system/p_red.png',    pastille: 8, couleur: 'danger' },
    { cible: 'system/p_orange.png', pastille: 8, couleur: 'alerte' },
    { cible: 'system/p_yellow.png', pastille: 8, couleur: 'ambre' },
    { cible: 'system/p_blue.png',   pastille: 8, couleur: 'info' },
    { cible: 'system/p_grey.png',   pastille: 8, couleur: 'attenue' },

    // Abonnement (subscription<0|1>.png, choisie selon l'état), partage, validation
    { cible: 'system/subscription0.png',  glyphe: 'bell',         couleur: 'attenue' },
    { cible: 'system/subscription1.png',  glyphe: 'bell-ringing', couleur: 'primaire' },
    { cible: 'share/share.png',           glyphe: 'share',     couleur: 'alerte' },
    { cible: 'share/search.png',          glyphe: 'search',    couleur: 'neutre' },
    { cible: 'validation/validation.png', glyphe: 'checklist', couleur: 'violet' },
    { cible: 'validation/search.png',     glyphe: 'search',    couleur: 'neutre' },

    // Tableaux : tri
    { cible: 'arrays/arrows.png',     glyphe: 'selector',     couleur: 'attenue' },
    { cible: 'arrays/arrow_up.png',   glyphe: 'chevron-up',   couleur: 'neutre', trait: 2.5 },
    { cible: 'arrays/arrow_down.png', glyphe: 'chevron-down', couleur: 'neutre', trait: 2.5 },

    // Sélecteur de date (calendar.png remplace calendar.gif, appelé par date::open_calendar)
    { cible: 'calendar/calendar.png', glyphe: 'calendar',       couleur: 'primaire', taille: [18, 18] },
    { cible: 'calendar/prev.png',     glyphe: 'chevron-left',   couleur: 'neutre', trait: 3 },
    { cible: 'calendar/next.png',     glyphe: 'chevron-right',  couleur: 'neutre', trait: 3 },
    { cible: 'calendar/prevx2.png',   glyphe: 'chevrons-left',  couleur: 'neutre', trait: 3 },
    { cible: 'calendar/nextx2.png',   glyphe: 'chevrons-right', couleur: 'neutre', trait: 3 },
    { cible: 'calendar/close.png',    glyphe: 'x',              couleur: 'neutre', trait: 3 },

    // Exports des listes d'utilisateurs (export/ : copies de img/export/ dans ploopi2)
    { cible: 'export/xls.png', glyphe: 'file-type-xls',    couleur: 'succes',   taille: [16, 16] },
    { cible: 'export/ods.png', glyphe: 'file-spreadsheet', couleur: 'succes',   taille: [16, 16] },
    { cible: 'export/csv.png', glyphe: 'file-type-csv',    couleur: 'neutre',   taille: [16, 16] },
    { cible: 'export/xml.png', glyphe: 'file-type-xml',    couleur: 'neutre',   taille: [16, 16] },
    { cible: 'export/pdf.png', glyphe: 'file-type-pdf',    couleur: 'danger',   taille: [16, 16] },
    { cible: 'export/vcf.png', glyphe: 'address-book',     couleur: 'primaire', taille: [16, 16] },

    // Arborescences : nœuds (les traits et les +/- de l'arbre ne changent pas)
    { cible: 'treeview/group.png',                 glyphe: 'users',      couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/user.png',                  glyphe: 'user',       couleur: 'neutre',   alignement: 'gauche' },
    { cible: 'treeview/workspace.png',             glyphe: 'building',   couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/workspace-frontoffice.png', glyphe: 'world',      couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/folder.png',                glyphe: 'folder',     couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/base.png',                  glyphe: 'home',       couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/go-home.png',               glyphe: 'home',       couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/network-server.png',        glyphe: 'server',     couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/applications-internet.png', glyphe: 'world',      couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/internet-web-browser.png',  glyphe: 'browser',    couleur: 'primaire', alignement: 'gauche' },
    { cible: 'treeview/system-file-manager.png',   glyphe: 'folders',    couleur: 'primaire', alignement: 'gauche' },

    // Documents attachés (classe ploopi\documents)
    { cible: 'documents/ico_folder.png',       glyphe: 'folder',      couleur: 'primaire' },
    { cible: 'documents/ico_folder_home.png',  glyphe: 'folder-user', couleur: 'primaire' },
    { cible: 'documents/ico_folder_locked.png', glyphe: 'folder-x',   couleur: 'attenue' },
    { cible: 'documents/ico_home.png',         glyphe: 'home',        couleur: 'primaire' },
    { cible: 'documents/ico_newfile.png',      glyphe: 'file-plus',   couleur: 'primaire' },
    { cible: 'documents/ico_newfiles.png',     glyphe: 'files',       couleur: 'primaire' },
    { cible: 'documents/ico_newfolder.png',    glyphe: 'folder-plus', couleur: 'primaire' },
    { cible: 'documents/ico_modify.png',       glyphe: 'pencil',      couleur: 'neutre' },
    { cible: 'documents/ico_trash.png',        glyphe: 'trash',       couleur: 'danger' },
    { cible: 'documents/ico_download.png',     glyphe: 'download',    couleur: 'neutre' },
    { cible: 'documents/ico_download_zip.png', glyphe: 'file-zip',    couleur: 'neutre' },
    { cible: 'documents/ico_search.png',       glyphe: 'search',      couleur: 'neutre' },
    { cible: 'documents/mimetypes/ico_default.png',     glyphe: 'file',             couleur: 'neutre' },
    { cible: 'documents/mimetypes/ico_document.png',    glyphe: 'file-text',        couleur: 'neutre' },
    { cible: 'documents/mimetypes/ico_spreadsheet.png', glyphe: 'file-spreadsheet', couleur: 'neutre' },
    { cible: 'documents/mimetypes/ico_image.png',       glyphe: 'photo',            couleur: 'neutre' },
    { cible: 'documents/mimetypes/ico_audio.png',       glyphe: 'file-music',       couleur: 'neutre' },
    { cible: 'documents/mimetypes/ico_video.png',       glyphe: 'movie',            couleur: 'neutre' },
    { cible: 'documents/mimetypes/ico_archive.png',     glyphe: 'file-zip',         couleur: 'neutre' },
    { cible: 'documents/mimetypes/ico_html.png',        glyphe: 'file-type-html',   couleur: 'neutre' },

    // GED du socle (classe documents) : fonds des boutons (button.icon_* de css/base_skin.css,
    // passés en blanc par le filtre de button.invert) et icônes des listes (folder*.svg,
    // file-<type>.svg, choisies selon l'extension ou le type du fichier)
    { cible: 'documents/cloud.svg',          glyphe: 'cloud',            couleur: 'primaire' },
    { cible: 'documents/folder.svg',         glyphe: 'folder',           couleur: 'primaire' },
    { cible: 'documents/folder_locked.svg',  glyphe: 'folder-lock',      couleur: 'primaire' },
    { cible: 'documents/folder-plus.svg',    glyphe: 'folder-plus',      couleur: 'primaire' },
    { cible: 'documents/folder-minus.svg',   glyphe: 'folder-minus',     couleur: 'primaire' },
    { cible: 'documents/folder-open.svg',    glyphe: 'folder-open',      couleur: 'primaire' },
    { cible: 'documents/file-import.svg',    glyphe: 'file-import',      couleur: 'primaire' },
    { cible: 'documents/download.svg',       glyphe: 'download',         couleur: 'neutre' },
    { cible: 'documents/edit.svg',           glyphe: 'pencil',           couleur: 'neutre' },
    { cible: 'documents/trash-alt.svg',      glyphe: 'trash',            couleur: 'danger' },
    { cible: 'documents/lock.svg',           glyphe: 'lock',             couleur: 'neutre' },
    { cible: 'documents/search.svg',         glyphe: 'search',           couleur: 'neutre' },
    { cible: 'documents/server.svg',         glyphe: 'server',           couleur: 'primaire' },
    { cible: 'documents/file-alt.svg',       glyphe: 'file',             couleur: 'neutre' },
    { cible: 'documents/file-document.svg',  glyphe: 'file-text',        couleur: 'neutre' },
    { cible: 'documents/file-spreadsheet.svg', glyphe: 'file-spreadsheet', couleur: 'neutre' },
    { cible: 'documents/file-pdf.svg',       glyphe: 'file-type-pdf',    couleur: 'neutre' },
    { cible: 'documents/file-image.svg',     glyphe: 'photo',            couleur: 'neutre' },
    { cible: 'documents/file-audio.svg',     glyphe: 'file-music',       couleur: 'neutre' },
    { cible: 'documents/file-video.svg',     glyphe: 'movie',            couleur: 'neutre' },
    { cible: 'documents/file-archive.svg',   glyphe: 'file-zip',         couleur: 'neutre' },
    { cible: 'documents/file-html.svg',      glyphe: 'file-type-html',   couleur: 'neutre' },
    { cible: 'documents/file-code.svg',      glyphe: 'file-code',        couleur: 'neutre' },
    { cible: 'documents/file-download.svg',  glyphe: 'file-download',    couleur: 'neutre' },

    // Champ obligatoire des formulaires générés (fond de label.required)
    { cible: 'forms/required.png', glyphe: 'asterisk', couleur: 'danger', trait: 3 },

    // Fermeture des fenêtres (affichée réduite, sur le titre sombre)
    { cible: 'template/close_popup.png', glyphe: 'x', couleur: 'blanc', taille: [64, 64] }
];

module.exports = { COULEURS, ICONES };
