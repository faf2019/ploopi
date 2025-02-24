/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {

    // Comportement de la touche "ENTER" (inversé par rapport à la config par défaut)
    config.enterMode = CKEDITOR.ENTER_BR;
    config.shiftEnterMode = CKEDITOR.ENTER_P;

    // Paramétrage du filtre ACF (filtre du contenu)
    // http://docs.ckeditor.com/#!/guide/dev_allowed_content_rules
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config-cfg-allowedContent
    // file://ckeditor/samples/datafiltering.html
    // config.allowedContent = 'a[!href]; ul; li{text-align}(someclass)';
    // config.allowedContent = 'span[*]{*}(*)';

    config.allowedContent =
        'h1 h2 h3 p blockquote strong em;' +
        'a[!href];' +
        'img(left,right)[!src,alt,width,height];' +
        'table tr th td caption;' +
        'span{!font-family};' +
        'span{!color};' +
        'span(!marker);' +
        'span(!ckTag);' + // Autorisation de la classe associée au tags
        'del ins';

    config.allowedContent = true; // Désactivation complète du filtre


    // Barre d'outil personnalisée
    config.toolbar = [
        ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ],
        ['Styles','Format','Font','FontSize'],
        ['TextColor','BGColor'],
    ];

    // Plugins additionnels
    config.extraPlugins = 'autogrow';
    config.removePlugins = 'elementspath';
    config.resize_enabled = false;

    // Hauteur de démarrage
    config.height = 100;
    // Hauteur automatique en fonction du contenu
    config.autoGrow_onStartup = true;
    config.autoGrow_minHeight = 100;

    // Dimension de boites de sélection de fichier
    config.filebrowserWindowWidth = '800';
    config.filebrowserWindowHeight = '600';
    config.filebrowserImageWindowWidth = '800';
    config.filebrowserImageWindowHeight = '600';

    config.versionCheck = false;
    // Define changes to default configuration here. For example:
    // config.language = 'fr';
    // config.uiColor = '#AADC6E';
};
