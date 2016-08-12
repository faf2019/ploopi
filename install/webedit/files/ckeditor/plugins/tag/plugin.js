// http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
// http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_2
// https://github.com/ckeditor/ckeditor-dev/blob/master/plugins/basicstyles/plugin.js

CKEDITOR.plugins.add( 'tag', {
    init: function( editor ) {

        // Définition du bouton "Tag"
        editor.ui.addButton( 'Tag', {
            label: 'Insérer un objet',
            command: 'tagDialog', // Commande à appeler (autre test : 'insertTag')
            icon: this.path + 'icons/ploopiobjects.gif',
            //toolbar: 'insert' // Permet de l'intégrer directement dans une toolbar
        });

        editor.addCommand( 'tagDialog', new CKEDITOR.dialogCommand( 'tagDialog' ) );

        // On capte l'événement double click sur l'éditeur
        editor.on( 'doubleclick', function( evt ) {

            var selection = editor.getSelection();
            var selectedElement = selection.getStartElement();

            if ( selectedElement && selectedElement.is( 'span' )  && selectedElement.hasClass( 'ckTag' ) ) {

                // Sélection de l'élément complet
                selection.selectElement(selectedElement);

                var element = selectedElement.$;

                // Ouverture de la boite de dialogue
                evt.data.dialog = 'tagDialog';
            }

        }, null, null, 0 );


        // Ajout d'une boite de dialogue
        CKEDITOR.dialog.add( 'tagDialog', function ( editor ) {

            return {
                title: 'Insérer un objet',
                minWidth: 250,
                minHeight: 150,
                contents: [{
                    id:         'tab1',
                    label:      '',
                    title:      '',
                    accessKey:  'Q',
                    elements: [
                        {
                            type: 'select',
                            id: 'object',
                            label: 'Insertion d\'un objet',
                            items: [],

                            onLoad: function( api ) {
                                // var sel = this.getInputElement().$;
                            },
                            onChange: function( api ) {
                                // this = CKEDITOR.ui.dialog.select
                                // alert( 'Current value: ' + this.getValue() );
                            }
                        },

                    ]
                }],

                onShow: function() {

                    // Détection de l'élément sélectionné (cf doubleclick)
                    var selection = editor.getSelection();
                    var selectedElement = selection.getStartElement();

                    var dialog = this;

                    // Détection de l'élément sélectionné
                    var tag = '';

                    // L'élément est bien celui qu'on cherche
                    if ( selectedElement && selectedElement.is( 'span' )  && selectedElement.hasClass( 'ckTag' ) ) {
                        tag = selectedElement.$.innerHTML.replace('[[', '').replace(']]', '').split('/')[0];
                    }

                    var sel = dialog.getContentElement('tab1', 'object').getInputElement().$;

                    // Nettoyage de la liste existante
                    sel.select('option').invoke('remove');

                    sel.appendChild(newOpt = document.createElement('option'));
                    newOpt.value = '';
                    newOpt.text = '(Choisir)';

                    // Remplissage de la liste avec une requête AJAX/JSON
                    new Ajax.Request(editor.config.objectBrowserUrl, {
                        method: 'get',
                        onSuccess:  function(transport, json) {
                            if(null == json) {
                                json = transport.responseText.evalJSON();
                            }

                            if (json) {
                                console.log(json);
                                for(key in json) {
                                    sel.appendChild(newOpt = document.createElement('option'));
                                    newOpt.value = key;
                                    newOpt.text = json[key];

                                    if (tag == key) sel.selectedIndex = sel.length - 1;
                                }
                            }
                        },
                        onFailure: function(message) { alert(message); }
                    });
                },

                onOk: function() {
                    var dialog = this;
                    var sel = dialog.getContentElement('tab1', 'object').getInputElement().$;

                    // console.log(dialog.getValueOf('tab1', 'object'));
                    var key = sel.value;
                    var label = sel[sel.selectedIndex].text;

                    if (key != '') editor.insertHtml('<span class="ckTag">[['+key+'/'+label+']]</span>');
                }
            };
        });
    }
});
