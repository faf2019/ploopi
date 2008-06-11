/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2008 Ovensia
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

    if (ploopi_validatefield('Nom du Dossier', form.docfolder_name, 'string'))
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

    if (res && sid)
    {
        if (ploopi_xmlhttprequest(cgipath+'/upload.cgi', 'test') == 'ok') setTimeout('doc_upload(\''+sid+'\');',250);
        else
        {
            alert('Problème de configuration CGI. Envoi du fichier impossible.');
            res = false;
        }
    }

    return res;
}

function doc_upload(sid)
{
    if ($('doc_progressbar'))
    {
        $('doc_progressbar').style.display = 'block';

        rc = ploopi_xmlhttprequest('index-quick.php', 'ploopi_op=doc_getstatus&sid='+sid);
        if (rc=='')
        {
            $('doc_progressbar_bg').style.width = ($('doc_progressbar').offsetWidth-2)+'px';
            $('doc_progressbar_txt').innerHTML = '<b>Terminé</b>';
        }
        else
        {
            if (rc == 'notfound')
            {
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
            
                    $('doc_progressbar_bg').style.width = ((($('doc_progressbar').offsetWidth-2)*rc[5])/100)+'px';
                    $('doc_progressbar_txt').innerHTML = '<b>'+rc[5]+'%</b> ('+rc[0]+'/'+rc[1]+'ko)<br />Envoi de <b>'+rc[3]+'</b> à <i>'+rc[4]+' ko/s</i>';
                }
                
                setTimeout('doc_upload(\''+sid+'\');',500);
            }
        }
    }
}


function doc_browser(currentfolder, orderby, op)
{
    if (!currentfolder) currentfolder = 0;

    op = (op) ? op : 'doc_browser';
    orderby = (orderby) ? '&orderby='+orderby : '';

    ploopi_xmlhttprequest_todiv('admin-light.php','op='+op+'&currentfolder='+currentfolder+orderby,'','doc_browser');
}


function doc_explorer(currentfolder, orderby)
{
    if (!currentfolder) currentfolder = 0;

    orderby = (orderby) ? '&orderby='+orderby : '';

    ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_explorer&currentfolder='+currentfolder+orderby,'','doc_explorer');
}

function doc_folderform(currentfolder, addfolder)
{
    if (!currentfolder) currentfolder = 0;
    addfolder = (addfolder) ? 1 : 0;

    ploopi_ajaxloader('doc_browser');
    ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_folderform&currentfolder='+currentfolder+'&addfolder='+addfolder,'','doc_browser');

}

function doc_folderdelete(docfolder_id)
{
    if (confirm('Êtes vous certain de vouloir supprimer ce dossier ?'))
    {
        ploopi_ajaxloader('doc_explorer');
        ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_folderdelete&docfolder_id='+docfolder_id,'','doc_explorer');
    }
}

function doc_folderpublish(currentfolder, docfolder_id)
{
    if (!currentfolder) currentfolder = 0;

    if (confirm('Êtes vous certain de vouloir publier ce dossier (et son contenu) ?'))
    {
        ploopi_ajaxloader('doc_explorer');
        ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_folderpublish&currentfolder='+currentfolder+'&docfolder_id='+docfolder_id,'','doc_explorer');
    }
}


function doc_fileform(currentfolder, docfile_md5id)
{
    if (!currentfolder) currentfolder = 0;
    docfile_md5id = (docfile_md5id) ? '&docfile_md5id='+docfile_md5id : '';

    ploopi_ajaxloader('doc_browser');
    ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_fileform&currentfolder='+currentfolder+docfile_md5id,'','doc_browser');
}

function doc_fileindex(currentfolder, docfile_md5id)
{
    if (!currentfolder) currentfolder = 0;

    ploopi_ajaxloader('doc_browser');
    ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_fileindex&currentfolder='+currentfolder+'&docfile_md5id='+docfile_md5id,'','doc_browser');
}

function doc_filedelete(currentfolder, docfile_md5id, draft)
{
    if (!currentfolder) currentfolder = 0;
    if (!draft) draft = '';

    if (confirm('Êtes vous certain de vouloir supprimer ce fichier ?'))
    {
        ploopi_ajaxloader('doc_explorer');
        ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_file'+draft+'delete&currentfolder='+currentfolder+'&docfile'+draft+'_md5id='+docfile_md5id,'','doc_explorer');
    }
}

function doc_filepublish(currentfolder, docfiledraft_md5id)
{
    if (!currentfolder) currentfolder = 0;

    if (confirm('Êtes vous certain de vouloir publier ce fichier ?'))
    {
        ploopi_ajaxloader('doc_explorer');
        ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_filepublish&currentfolder='+currentfolder+'&docfiledraft_md5id='+docfiledraft_md5id,'','doc_explorer');
    }
}

function doc_parser_add()
{
    $('docparser_form').style.display = 'block';
    $('docparser_id').value = '';
    $('docparser_label').value = '';
    $('docparser_extension').value = '';
    $('docparser_path').value = '';
    $('docparser_label').focus();
}

function doc_search()
{
    ploopi_ajaxloader('doc_browser');
    ploopi_xmlhttprequest_todiv('admin-light.php','op=doc_search&currentfolder=0','','doc_browser');
}

function doc_search_next()
{
    ploopi_ajaxloader('doc_search_result');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=doc_search_next&doc_search_keywords='+$('doc_search_keywords').value+'&doc_search_filetype='+$('doc_search_filetype').value+'&doc_search_user='+$('doc_search_user').value+'&doc_search_workspace='+$('doc_search_workspace').value+'&doc_search_date1='+$('doc_search_date1').value+'&doc_search_date2='+$('doc_search_date2').value,'','doc_search_result');
}

// utile pour contourner un bug de FF lors d'un appel AJAX depuis une iframe
// merci à http://www.fleegix.org/articles/2006/10/21/xmlhttprequest-and-0x80004005-ns_error_failure-error
function doc_browser_from_iframe(idfolder)
{
    setTimeout('doc_browser('+idfolder+')', 0);
}


function doc_openhelp(e)
{
    ploopi_showpopup('', 300, e, 'click', 'dochelp');
    ploopi_ajaxloader('dochelp');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_op=doc_help','','dochelp');
}



function doc_fckexplorer_set_folder(idfolder, ploopi_op)
{
    cf = $('doc_choosefolder');
    trouve = false;
    i=0;
    while (i<=cf.length && !trouve)
    {
        if (cf.options[i].value == idfolder) {cf.selectedIndex = i; trouve=true;}
        i++;
    }

    doc_fckexplorer_switch_folder(idfolder, ploopi_op)
}

function doc_fckexplorer_switch_folder(idfolder, ploopi_op)
{
    new Ajax.Request('admin-light.php',
        {
            method:     'get',
            parameters: {'ploopi_op': 'doc_getfiles', 'idfolder': idfolder, 'filter': ploopi_op},
            onSuccess:  function(transport, json)
            {
                if (json)
                {

                    fb = $('doc_filebrowser');
                    fb.innerHTML = '';
                    for (i=0;i<json.length;i++)
                    {
                        var filesize = Math.round(parseInt(json[i]['size'],10)/1024);

                        if (ploopi_op == 'doc_selectimage')
                        {
                            fb.innerHTML +=     '<a class="doc_fckexplorer_vignette" href="javascript:void(0);" onclick="javascript:ploopi_getelem(\'txtUrl\',opener.document).value=\'./index-quick.php?ploopi_op=doc_file_download&docfile_md5id='+json[i]['md5id']+'\';opener.UpdatePreview();window.close();">'+
                                                    '<img style="height:75px;" src="index-quick.php?ploopi_op=doc_image_get&docfile_md5id='+json[i]['md5id']+'&height=75" />'+
                                                    '<div style="font-weight:bold;">'+json[i]['name']+'</div>'+
                                                    '<div>'+filesize+' ko</div>'+
                                                '</a>';
                        }
                        else if (ploopi_op == 'doc_selectflash')
                        {
                            fb.innerHTML +=     '<a class="doc_fckexplorer_vignette" href="javascript:void(0);" onclick="javascript:ploopi_getelem(\'txtUrl\',opener.document).value=\'./index-quick.php?ploopi_op=doc_file_download&docfile_md5id='+json[i]['md5id']+'\';opener.UpdatePreview();window.close();">'+
                                                    '<div style="font-weight:bold;">'+json[i]['name']+'</div>'+
                                                    '<div>'+filesize+' ko</div>'+
                                                '</a>';
                        }
                        else
                        {
                            fb.innerHTML +=     '<a class="doc_fckexplorer_file" href="javascript:void(0);" onclick="javascript:ploopi_getelem(\'txtUrl\',opener.document).value=\'./index-quick.php?ploopi_op=doc_file_download&docfile_md5id='+json[i]['md5id']+'\';ploopi_getelem(\'cmbLinkProtocol\',opener.document).value=\'\';window.close();">'+
                                                    '<div style="font-weight:bold;">'+json[i]['name']+'</div>'+
                                                    '<div>'+filesize+' ko</div>'+
                                                '</a>';
                        }

                    }
                }
            }
        }
    );
}
