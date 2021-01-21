/*
    Copyright (c) 2007-2018 Ovensia
    Contributors hold Copyright (c) to their code submissions.

    This file is part of Ploopi.

    Ploopi is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Ploopi is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Ploopi; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

var doc_upload_error = false;

function doc_folder_validate(form, tovalidate)
{
    next = false;
    doc_upload_error = false;

    if (ploopi.validatefield('Nom du Dossier', form.docfolder_name, 'string'))
    if (tovalidate)
    {
        next = confirm('Cette action va envoyer\nune demande de publication\naux validateurs du dossier parent\n\nÊtes-vous certain de vouloir continuer ?');
    }
    else next = true;

    if (next) return true;

    return false;
}

function doc_file_validate(form, newfile, tovalidate, sid, cgipath)
{
    res = false;

    if (newfile)
    {
        if (tovalidate) res = confirm('Cette action va envoyer\nune demande de publication\naux validateurs de ce dossier\n\nÊtes-vous certain de vouloir continuer ?');
        else res = true;
    }
    else
    {
        if (tovalidate && form.docfile_file.value != '') res = confirm('Cette action va envoyer\nune demande de publication\naux validateurs de ce dossier\n\nÊtes-vous certain de vouloir continuer ?');
        else res = true;
    }

    return res;
}

function doc_upload(sid)
{
    if (jQuery('#doc_progressbar')[0])
    {
        jQuery('#doc_progressbar')[0].style.display = 'block';

        rc = ploopi.xhr.send('index-quick.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=doc_getstatus&sid='+sid);
        if (rc=='')
        {
            jQuery('#doc_progressbar_bg')[0].style.width = (jQuery('#doc_progressbar')[0].offsetWidth-2)+'px';
            jQuery('#doc_progressbar_txt')[0].innerHTML = '<b>Terminé</b>';
        }
        else
        {
            if (rc == 'notfound')
            {
                alert("Impossible d'envoyer ce fichier,\nvérifiez qu'il n'est pas trop volumineux.");
                //document.location.reload();
            }
            else
            {
                rc = rc.split('|');

                //alert(rc.length);

                if (rc.length == 6)
                {
                    // 0 : taille uploadée
                    // 1 : taille totale
                    // 2 : ?
                    // 3 : fichier en cours d'upload
                    // 4 : vitesse ko/s
                    // 5 : % avancement

                    jQuery('#doc_progressbar_bg')[0].style.width = (((jQuery('#doc_progressbar')[0].offsetWidth-2)*rc[5])/100)+'px';
                    jQuery('#doc_progressbar_txt')[0].innerHTML = '<b>'+rc[5]+'%</b> ('+rc[0]+'/'+rc[1]+'ko)<br />Envoi de <b>'+rc[3]+'</b> à <i>'+rc[4]+' ko/s</i>';
                }

                setTimeout('doc_upload(\''+sid+'\');',500);
            }
        }
    }
}

function doc_parser_add()
{
    jQuery('#docparser_form')[0].style.display = 'block';
    jQuery('#docparser_id')[0].value = '';
    jQuery('#docparser_label')[0].value = '';
    jQuery('#docparser_extension')[0].value = '';
    jQuery('#docparser_path')[0].value = '';
    jQuery('#docparser_label')[0].focus();
}

function doc_search()
{
    ploopi.xhr.ajaxloader('doc_browser');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=doc_search&currentfolder=0', 'doc_browser');
}

function doc_search_next()
{
    ploopi.xhr.ajaxloader('doc_search_result');
    ploopi.xhr.todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=doc_search_next&doc_search_keywords='+encodeURIComponent(jQuery('#doc_search_keywords')[0].value)+'&doc_search_filetype='+encodeURIComponent(jQuery('#doc_search_filetype')[0].value)+'&doc_search_user='+jQuery('#doc_search_user')[0].value+'&doc_search_workspace='+jQuery('#doc_search_workspace')[0].value+'&doc_search_date1='+jQuery('#doc_search_date1')[0].value+'&doc_search_date2='+jQuery('#doc_search_date2')[0].value+'&doc_search_stem='+(jQuery('#doc_search_stem')[0].checked?1:0)+'&doc_search_phonetic='+(jQuery('#doc_search_phonetic')[0].checked?1:0)+'&doc_search_and='+(jQuery('#doc_search_and')[0].checked?1:0),'doc_search_result');
}

function doc_openhelp(e)
{
    ploopi.popup.show('', 300, e, 'click', 'dochelp');
    ploopi.xhr.ajaxloader('dochelp');
    ploopi.xhr.todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=doc_help', 'dochelp');
}

function doc_fckexplorer_set_folder(idfolder, ploopi_op)
{
    cf = jQuery('#doc_choosefolder')[0];
    trouve = false;
    i=0;
    while (i<=cf.length && !trouve)
    {
        if (cf.options[i].value == idfolder) {cf.selectedIndex = i; trouve=true;}
        i++;
    }

    doc_fckexplorer_switch_folder(idfolder, ploopi_op)
}


function doc_fckexplorer_selectfile(fileUrl, text, urldecod)
{
    if (ploopi.get_param('target') != undefined) {
        window.opener.document.getElementById(ploopi.get_param('target')).value = urldecod;
    } else {
        window.parent.opener.CKEDITOR.tools.callFunction( jQuery('#CKEditorFuncNum')[0].value, fileUrl, function() {
            // Get the reference to a dialog window.
            var dialog = this.getDialog();
            // Check if this is the Image Properties dialog window.
            if ( dialog.getName() == 'image' ) {
                // Get the reference to a text field that stores the "alt" attribute.
                var element = dialog.getContentElement( 'info', 'txtAlt' );
                // Assign the new value.
                if ( element )
                    element.setValue( text );
            }
        });
    }
    window.parent.close();
};

function doc_fckexplorer_switch_folder(idfolder, ploopi_op)
{

    var request = jQuery.ajax({
        type: 'GET',
        url: 'admin-light.php',
        data: {
            ploopi_env: _PLOOPI_ENV,
            ploopi_op:  'doc_getfiles',
            idfolder: idfolder,
            filter: ploopi_op
        },
        dataType: 'json',
        success: function (json, status) {

            fb = jQuery('#doc_filebrowser')[0];
            fb.innerHTML = '';

            for (i=0;i<json.length;i++)
            {
                var filesize = Math.round(parseInt(json[i]['size'],10)/1024);

                fb.innerHTML +=     '<a class="doc_fckexplorer_vignette" href="javascript:void(0);" onclick="javascript:doc_fckexplorer_selectfile(\''+json[i]['url']+'\', \''+json[i]['name']+'\', \''+json[i]['urldecod']+'\');">'+
                                        '<img style="height:75px;" src="index-quick.php?ploopi_op=doc_image_get&docfile_md5id='+json[i]['md5id']+'&version='+json[i]['version']+'&width=125&height=75" />'+
                                        '<div style="font-weight:bold;">'+json[i]['name']+'</div>'+
                                        '<div>'+filesize+' ko</div>'+
                                    '</a>';

            }

        }
    });
}
