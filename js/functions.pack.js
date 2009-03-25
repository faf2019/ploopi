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

function ploopi_annotation(id_annotation)
{
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation&id_annotation='+id_annotation, 'ploopiannotation_'+id_annotation);
}

var tag_timer;
var tag_search;
var tag_results = new Array();

var tag_last_array = new Array();
var tag_new_array = new Array();

var tag_lastedit = '';
var tag_modified = -1

function ploopi_annotation_tag_init(id_annotation)
{
    $('ploopi_annotationtags_'+id_annotation).onkeyup = ploopi_annotation_tag_keyup;
    $('ploopi_annotationtags_'+id_annotation).onkeypress = ploopi_annotation_tag_keypress;
}

function ploopi_annotation_tag_search(id_annotation, search)
{
    clearTimeout(tag_timer);
    tag_search = search;
    tag_timer = setTimeout("ploopi_annotation_tag_searchtimeout('"+id_annotation+"')", 100);
}

function ploopi_annotation_tag_searchtimeout(id_annotation)
{
    // replace(/(^\s*)|(\s*$)/g,'') = TRIM
    list_tags = tag_search.split(' ');

    if (list_tags.length>0) ploopi_xmlhttprequest_tofunction('index-quick.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_searchtags&tag='+list_tags[list_tags.length-1],ploopi_annotation_tag_display,id_annotation);
}

function ploopi_annotation_tag_display(result,ticket)
{
    if (result != '')
    {
        tag_results = new Array();

        splited_result = result.split('|');
        tagstoprint = '';

        for (i=0;i<splited_result.length;i++)
        {
            detail = splited_result[i].split(';');
            if (tagstoprint != '') tagstoprint += ' ';
            if (i==0) tagstoprint += '<b>';
            tagstoprint += '<a href="javascript:ploopi_annotation_tag_complete(\''+ticket+'\','+i+')">'+detail[0]+'</a> ('+detail[1]+')';
            if (i==0) tagstoprint += '</b>';
            tag_results[i] = detail[0];
        }

        $('tagsfound_'+ticket).innerHTML = tagstoprint;
    }
    else
    {
        $('tagsfound_'+ticket).innerHTML = '';
        tag_results = new Array();
    }
}

function ploopi_annotation_tag_prevent(e)
{
    if (window.event) window.event.returnValue = false
    else e.preventDefault()
}

function ploopi_annotation_tag_keypress(e)
{
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target;

    switch(e.keyCode)
    {
        case 38: case 40:
            ploopi_annotation_tag_prevent(e)
        break
        case 9:
            ploopi_annotation_tag_prevent(e)
        break
        case 13:
            ploopi_annotation_tag_prevent(e)
        break
        default:
            tag_lastedit = $(src.id).value;
        break;
    }
}

function ploopi_annotation_tag_keyup(e)
{
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target; // get source field
    idrecord = src.id.split('_')[2]; // get id record from source field id

    switch(e.keyCode)
    {
        case 38: case 40:
            ploopi_annotation_tag_prevent(e);
        break
        case 9:
            ploopi_annotation_tag_complete(idrecord);
            ploopi_annotation_tag_prevent(e);
        break
        case 13:
            ploopi_annotation_tag_complete(idrecord);
            ploopi_annotation_tag_prevent(e);
        break
        case 35: //end
        case 36: //home
        case 39: //right
        case 37: //left
        //case 32: //space
        break
        default:
            tag_last_array = new Array();
            tag_new_array = new Array();

            tag_last_array = tag_lastedit.split(' ');
            tag_new_array = $(src.id).value.split(' ');

            tag_modified = -1;
            for (i=0;i<tag_new_array.length;i++)
            {
                if (tag_new_array[i] != tag_last_array[i])
                {
                    if (tag_modified == -1) tag_modified = i;
                    else tag_modified = -2
                }
            }
            if (tag_modified>=0) ploopi_annotation_tag_search(idrecord, tag_new_array[tag_modified]);
        break;
    }
}

function ploopi_annotation_tag_complete(idrecord, idtag)
{
    if (!(idtag>=0)) idtag = 0;

    if (tag_results[idtag])
    {
        tag_new_array[tag_modified] = tag_results[idtag];

        taglist = '';
        for (i=0;i<tag_new_array.length;i++)
        {
            if (taglist != '') taglist += ' ';
            taglist += tag_new_array[i]
        }

        $('ploopi_annotationtags_'+idrecord).value = taglist.replace(/(^\s*)|(\s*$)/g,'')+' ';
        $('tagsfound_'+idrecord).innerHTML = '';
    }

    tag_results = new Array();
}

function ploopi_annotation_delete(id_annotation, id)
{
    if (confirm('�tes vous certain de vouloir supprimer cette annotation ?')) ploopi_xmlhttprequest('index-quick.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=annotation_delete&ploopi_annotation_id='+id);
    ploopi_annotation(id_annotation);
}

function ploopi_annotation_validate(form)
{
    if (ploopi_validatefield('Titre',form.ploopi_annotationtags,"string")) return true;

    return false;
}
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

function ploopi_calendar_open(inputfield_id, event)
{
    ploopi_showpopup(ploopi_xmlhttprequest('index-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=calendar_open&selected_date='+$(inputfield_id).value+'&inputfield_id='+inputfield_id), 192, event, false, 'ploopi_popup_calendar');
}
/* DHTML Color Picker : v1.0.4 : 2008/04/17 */
/* http://www.colorjack.com/software/dhtml+color+picker.html */

/**
 * Script original de colorjack.
 * Adapt� pour Ploopi par St�phane Escaich / OVENSIA
 *
 * @link http://www.colorjack.com/software/dhtml+color+picker.html
 */

function $S(o) { o=$(o); if(o) return(o.style); }
function abPos(o) { var o=(typeof(o)=='object'?o:$(o)), z={X:0,Y:0}; while(o!=null) { z.X+=o.offsetLeft; z.Y+=o.offsetTop; o=o.offsetParent; }; return(z); }
function agent(v) { return(Math.max(navigator.userAgent.toLowerCase().indexOf(v),0)); }
function toggle(v) { $S(v).display=($S(v).display=='none'?'block':'none'); }
function within(v,a,z) { return((v>=a && v<=z)?true:false); }
function XY(e,v) { var z=agent('msie')?[event.clientX+document.body.scrollLeft,event.clientY+document.body.scrollTop]:[e.pageX,e.pageY]; return(z[zero(v)]); }
function zero(v) { v=parseInt(v); return(!isNaN(v)?v:0); }

/* COLOR PICKER */

var maxValue={'H':360,'S':100,'V':100}, HSV={H:360, S:100, V:100};

var slideHSV={H:360, S:100, V:100}, zINDEX=15, stop=1;

function HSVslide(d,o,e) {

    function tXY(e) { tY=XY(e,1)-ab.Y; tX=XY(e)-ab.X; }
    function mkHSV(a,b,c) { return(Math.min(a,Math.max(0,Math.ceil((parseInt(c)/b)*a)))); }
    function ckHSV(a,b) { if(within(a,0,b)) return(a); else if(a>b) return(b); else if(a<0) return('-'+oo); }
    function drag(e) { if(!stop) { if(d!='drag') tXY(e);

        if(d=='SVslide') { ds.left=ckHSV(tX-oo,162)+'px'; ds.top=ckHSV(tY-oo,162)+'px';

            slideHSV.S=mkHSV(100,162,ds.left); slideHSV.V=100-mkHSV(100,162,ds.top); HSVupdate();

        }
        else if(d=='Hslide') { var ck=ckHSV(tY-oo,163), r='HSV', z={};

            ds.top=(ck)+'px'; slideHSV.H=mkHSV(360,163,ck);

            for(var i in r) { i=r.substr(i,1); z[i]=(i=='H')?maxValue[i]-mkHSV(maxValue[i],163,ck):HSV[i]; }

            HSVupdate(z); $S('SV').backgroundColor='#'+color.HSV_HEX({H:HSV.H, S:100, V:100});

        }
        else if(d=='drag') { ds.left=XY(e)+oX-eX+'px'; ds.top=XY(e,1)+oY-eY+'px'; }

    }}

    if(stop) { stop=''; var ds=$S(d!='drag'?d:o);

        if(d=='drag') { var oX=parseInt(ds.left), oY=parseInt(ds.top), eX=XY(e), eY=XY(e,1); $S(o).zIndex=zINDEX++; }

        else { var ab=abPos($(o)), tX, tY, oo=(d=='Hslide')?2:4; ab.X+=10; ab.Y+=22; if(d=='SVslide') slideHSV.H=HSV.H; }

        document.onmousemove=drag; document.onmouseup=function(){ stop=1; document.onmousemove=''; document.onmouseup=''; }; drag(e);

    }
};

function HSVupdate(v) { v=color.HSV_HEX(HSV=v?v:slideHSV);

    $S('plugCUR').background='#'+v;
    $('colorpicker_inputcolor').value = '#'+v;

    return(v);

};

function loadSV() { var z='';

    for(var i=165; i>=0; i--) { z+="<div style=\"background: #"+color.HSV_HEX({H:Math.round((360/165)*i), S:100, V:100})+";\"><br /><\/div>"; }

    $('Hmodel').innerHTML=z;

};

/* COLOR LIBRARY */

color={};

color.cords=function(W) {

    var W2=W/2, rad=(hsv.H/360)*(Math.PI*2), hyp=(hsv.S+(100-hsv.V))/100*(W2/2);

    $S('mCur').left=Math.round(Math.abs(Math.round(Math.sin(rad)*hyp)+W2+3))+'px';
    $S('mCur').top=Math.round(Math.abs(Math.round(Math.cos(rad)*hyp)-W2-21))+'px';

};

color.HEX=function(o) { o=Math.round(Math.min(Math.max(0,o),255));

    return("0123456789ABCDEF".charAt((o-o%16)/16)+"0123456789ABCDEF".charAt(o%16));

};

color.RGB_HEX=function(o) { var fu=color.HEX; return(fu(o.R)+fu(o.G)+fu(o.B)); };

color.HEX_RGB=function(o) { return({'R':parseInt(o.substring(1,3),16), 'G':parseInt(o.substring(3,5),16), 'B':parseInt(o.substring(5,7),16), 'A':1}); };

color.HSV_RGB=function(o) {

    var R, G, A, B, C, S=o.S/100, V=o.V/100, H=o.H/360;

    if(S>0) { if(H>=1) H=0;

        H=6*H; F=H-Math.floor(H);
        A=Math.round(255*V*(1-S));
        B=Math.round(255*V*(1-(S*F)));
        C=Math.round(255*V*(1-(S*(1-F))));
        V=Math.round(255*V);

        switch(Math.floor(H)) {

            case 0: R=V; G=C; B=A; break;
            case 1: R=B; G=V; B=A; break;
            case 2: R=A; G=V; B=C; break;
            case 3: R=A; G=B; B=V; break;
            case 4: R=C; G=A; B=V; break;
            case 5: R=V; G=A; B=B; break;

        }

        return({'R':R?R:0, 'G':G?G:0, 'B':B?B:0, 'A':1});

    }
    else return({'R':(V=Math.round(V*255)), 'G':V, 'B':V, 'A':1});

};

color.RGB_HSV=function(o) { var M=Math.max(o.R,o.G,o.B), delta=M-Math.min(o.R,o.G,o.B), H, S, V;

    if(M!=0) { S=Math.round(delta/M*100);

        if(o.R==M) H=(o.G-o.B)/delta; else if(o.G==M) H=2+(o.B-o.R)/delta; else if(o.B==M) H=4+(o.R-o.G)/delta;

        var H=Math.min(Math.round(H*60),360); if(H<0) H+=360;

    }

    return({'H':H?H:0, 'S':S?S:0, 'V':Math.round((M/255)*100)});

};

color.HSV_HEX=function(o) { return(color.RGB_HEX(color.HSV_RGB(o))); };

color.HEX_HSV=function(o) { return(color.RGB_HSV(color.HEX_RGB(o))); }

function ploopi_colorpicker_open(inputfield_id, event)
{
    if ($(inputfield_id).value == '') $(inputfield_id).value = '#ffffff';

    ploopi_showpopup(
        ploopi_xmlhttprequest(
            'admin-light.php',
            'ploopi_op=colorpicker_open&inputfield_id='+inputfield_id+'&colorpicker_value='+escape($(inputfield_id).value)
        ),
        220,
        event,
        false,
        'popup_colorpicker'
    );

    loadSV();

    var hsv = color.HEX_HSV($(inputfield_id).value);
    HSVupdate(hsv);

    $S('SV').backgroundColor = '#'+color.HSV_HEX({H:hsv.H, S:100, V:100});

    $S('SVslide').left = (Math.ceil((hsv.S * 165) / 100) - 4) + 'px';
    $S('SVslide').top = (165 - Math.ceil((hsv.V * 165) / 100) - 4) + 'px';
    $S('Hslide').top = (165 - Math.ceil((hsv.H * 165) / 360) - 2) + 'px';
}
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

/* DOCUMENTS FUNCTIONS */

function ploopi_documents_openfolder(currentfolder, documentsfolder_id, event)
{
    ploopi_showpopup('', 400, event, 'click', 'ploopi_documents_openfolder_popup');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_openfolder&currentfolder='+currentfolder+'&documentsfolder_id='+documentsfolder_id,'ploopi_documents_openfolder_popup');
}

function ploopi_documents_openfile(currentfolder, documentsfile_id, event)
{
    ploopi_showpopup('', 400, event, 'click', 'ploopi_documents_openfile_popup');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_openfile&currentfolder='+currentfolder+'&documentsfile_id='+documentsfile_id,'ploopi_documents_openfile_popup');
}

function ploopi_documents_deletefile(currentfolder, documents_id, documentsfile_id)
{
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_deletefile&currentfolder='+currentfolder+'&documentsfile_id='+documentsfile_id,'ploopidocuments_'+documents_id);
}

function ploopi_documents_deletefolder(currentfolder, documents_id, documentsfolder_id)
{
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_deletefolder&currentfolder='+currentfolder+'&documentsfolder_id='+documentsfolder_id,'ploopidocuments_'+documents_id);
}

function ploopi_documents_browser(documents_id, currentfolder, mode, orderby, asynchronous)
{
    if (typeof(currentfolder) == 'undefined') currentfolder = '';
    if (typeof(asynchronous) == 'undefined') asynchronous = false;
    if (typeof(orderby) == 'undefined') orderby = '';
    if (typeof(mode) == 'undefined') mode = '';

    var option = (orderby != '') ? '&orderby='+orderby : '';

    if (asynchronous)
    {
        ploopi_ajaxloader('ploopidocuments_'+documents_id);
        ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_browser&mode='+mode+'&currentfolder='+currentfolder+option,'ploopidocuments_'+documents_id);
    }
    else ploopi_innerHTML('ploopidocuments_'+documents_id, ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_browser&mode='+mode+'&currentfolder='+currentfolder+option));
}

function ploopi_documents_validate(form)
{
    if (form.documentsfile_name)
    {
        if (!ploopi_validatefield('Fichier',form.documentsfile_name,"string")) return false;
    }
    else if (!ploopi_validatefield('Fichier',form.documentsfile_file,"string")) return false;

    if (ploopi_validatefield('Libell�',form.documentsfile_label,"string"))
    return true;

    return false;
}

function ploopi_documents_popup(id_object, id_record, id_module, destfield, event)
{
    var documents_id = ploopi_base64_encode(id_module+'_'+id_object+'_'+ploopi_addslashes(id_record)+'_popup');
    ploopi_showpopup(''+ploopi_xmlhttprequest('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=documents_selectfile&id_object='+id_object+'&id_record='+id_record+'&documents_id='+documents_id+'&destfield='+destfield)+'', 600, event, 'click', 'ploopi_documents_popup');
}
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

var ploopi_window_onload_functions = new Array();
var ploopi_window_onunload_functions = new Array();

function ploopi_window_onload_stock(func)
{
    ploopi_window_onload_functions[ploopi_window_onload_functions.length] = func;
}

function ploopi_window_onload_launch()
{
    window.onload = function()
    {
        for (var i = 0; i < ploopi_window_onload_functions.length; i++) ploopi_window_onload_functions[i]();
    }
}

function ploopi_window_onunload_stock(func)
{
    ploopi_window_onunload_functions[ploopi_window_onunload_functions.length] = func;
}

function ploopi_window_onunload_launch()
{
    window.onunload = function()
    {
        for (var i = 0; i < ploopi_window_onunload_functions.length; i++) ploopi_window_onunload_functions[i]();
    }
}

function ploopi_dispatch_onchange(inputfield_id)
{
    if (Prototype.Browser.IE)
    {
        $(inputfield_id).fireEvent('onChange');
    }
    else
    {
        var e = document.createEvent('HTMLEvents');
        e.initEvent('change', false, false);
        $(inputfield_id).dispatchEvent(e);
    }
}

function ploopi_dispatch_onclick(inputfield_id)
{
    if (Prototype.Browser.IE)
    {
        $(inputfield_id).fireEvent('onclick');
    }
    else
    {
	    var e = document.createEvent('MouseEvents');
	    e.initEvent('click', false, false);
	    $(inputfield_id).dispatchEvent(e);
    }
}


ploopi_window_onload_launch();
ploopi_window_onunload_launch();
/*
    Copyright (c) 2008 Ovensia
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

/* FILE EXPLORER FUNCTIONS */

function ploopi_filexplorer_popup(filexplorer_id, event)
{
    ploopi_showpopup(ploopi_ajaxloader_content, 600, event, true, 'ploopi_filexplorer_popup');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=filexplorer_browser&filexplorer_id='+filexplorer_id, 'ploopi_filexplorer_popup')
}

function ploopi_filexplorer_browser(filexplorer_id, folder)
{
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=filexplorer_browser&filexplorer_id='+filexplorer_id+'&filexplorer_folder='+folder, 'ploopi_filexplorer_popup')
}/*
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

var ploopi_nbpopup = 0;

function ploopi_showpopup(popup_content, w, e, centered, id, pposx, pposy)
{
    var ploopi_popup;
    var active_effect = false;

    if (!id) id = 'ploopi_popup';

    if (!$(id))
    {
        bodys = document.getElementsByTagName('body');

        ploopi_nbpopup++;
        ploopi_popup = document.createElement('div');
        ploopi_popup.setAttribute('class', 'ploopi_popup');
        ploopi_popup.setAttribute('className', 'ploopi_popup'); // IE
        ploopi_popup.setAttribute('id', id);
        ploopi_popup.setAttribute('style', 'z-index:'+(1000+ploopi_nbpopup)+';');
        ploopi_popup.style.display = 'none';

        bodys[0].appendChild(ploopi_popup);

        active_effect = true;
    }
    else ploopi_popup = $(id);

    w = parseInt(w);
    if (!w) w = 200;

    posx = 0;
    posy = 0;

    pposx = parseInt(pposx);
    pposy = parseInt(pposy);

    if (pposx) posx = pposx;
    if (pposy) posy = pposy;

    if(e) // event ok
    {
        if (e.pageX || e.pageY) {
            posx = e.pageX;
            posy = e.pageY;
        }
        else if (e.clientX || e.clientY) {
            posx = e.clientX + document.body.scrollLeft;
            posy = e.clientY + document.body.scrollTop;
        }
    }
    else
    {
        switch(centered)
        {
           case false:
           break;

           default:
           case true:
                var p_width = parseInt(document.body.offsetWidth);
                var p_left = parseInt(document.body.scrollLeft);
                var posx = (p_width/2)-(w/2)+p_left;
            break;
        }
    }

    with (ploopi_popup.style)
    {
        if (typeof(popup_content) != 'undefined') ploopi_innerHTML(id, popup_content);

        tmpleft = parseInt(posx) + 20;
        tmptop = parseInt(posy);

        if (w > 0) width = w+'px';
        else w = parseInt(ploopi_popup.offsetWidth);

        if (20 + w + parseInt(tmpleft) > parseInt(document.body.offsetWidth))
        {
            tmpleft = parseInt(tmpleft) - w - 40;
        }

        left = tmpleft+'px';
        top = tmptop+'px';
    }

    if (active_effect) new Effect.Appear(id, { duration: 0.4, from: 0.0, to: 1 });
}

function ploopi_movepopup(id, e, pposx, pposy, popup_content)
{
    var ploopi_popup;

    if (!id) id = 'ploopi_popup';

    ploopi_popup = $(id);

    posx = 0;
    posy = 0;

    pposx = parseInt(pposx);
    pposy = parseInt(pposy);

    if (pposx) posx = pposx;
    if (pposy) posy = pposy;

    if(e) // event ok
    {
        if (e.pageX || e.pageY) {
            posx = e.pageX;
            posy = e.pageY;
        }
        else if (e.clientX || e.clientY) {
            posx = e.clientX + document.body.scrollLeft;
            posy = e.clientY + document.body.scrollTop;
        }
    }

    with (ploopi_popup.style)
    {
        ploopi_innerHTML(id, popup_content);

        tmpleft = parseInt(posx) + 20;
        tmptop = parseInt(posy);

        w = parseInt(ploopi_popup.offsetWidth);

        if (20 + w + parseInt(tmpleft) > parseInt(document.body.offsetWidth))
        {
            tmpleft = parseInt(tmpleft) - w - 40;
        }

        left = tmpleft+'px';
        top = tmptop+'px';
    }

}

function ploopi_hidepopup(id)
{
    if (!id) id = 'ploopi_popup';

    if ($(id))
    {
        new Effect.Fade(id,
                {
                    duration: 0.3,
                    afterFinish:function()
                    {
                        var bodys = document.getElementsByTagName('body');
                        bodys[0].removeChild($(id));
                    }
                }
             );
    }
}

function ploopi_hideallpopups()
{
    var popups = document.getElementsByClassName('ploopi_popup');
    var bodys = document.getElementsByTagName('body');
    var l = popups.length;
    for (var i = 0; i < l; i++) bodys[0].removeChild(popups[i]);
}
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

ploopi_skin_array_renderupdate_done = new Array();

/**
 * Met � jour l'affichage des tableaux g�n�r�s par la classe skin.
 * Il faut corriger certains probl�mes li�s � l'affichage ou non d'une barre de d�filement vertical.
 * Il faut �galement corriger les lacunes de IE.
 */

function ploopi_skin_array_renderupdate(array_id)
{
    greater = $('ploopi_explorer_values_inner_'+array_id).offsetHeight > $('ploopi_explorer_values_outer_'+array_id).offsetHeight;

    if (greater)
    {
        if (typeof(ploopi_skin_array_renderupdate_done[array_id]) == 'undefined')
        {
            $('ploopi_explorer_title_'+array_id).innerHTML = '<div style=\'float:right;width:16px;\'>&nbsp;</div>'+$('ploopi_explorer_title_'+array_id).innerHTML;

            columns = $('ploopi_explorer_main_'+array_id).getElementsByClassName('ploopi_explorer_column');
            for (j=0;j<columns.length;j++)
            {
                if (columns[j].style.right != '')
                {
                    diff = (Prototype.Browser.IE) ? 22 : 16;
                    columns[j].style.right = (parseInt(columns[j].style.right)+diff)+'px';
                }
            }

            ploopi_skin_array_renderupdate_done[array_id] = true;
        }
    }

    if (Prototype.Browser.IE)
    {
        columns = $('ploopi_explorer_main_'+array_id).getElementsByClassName('ploopi_explorer_column');
        for (j=0;j<columns.length;j++)
        {
            columns[j].style.height = $('ploopi_explorer_main_'+array_id).offsetHeight+'px';
        }
    }
}

function ploopi_skin_treeview_shownode(node_id, query, script)
{
    if (typeof(script) == 'undefined') script = 'admin-light.php';

    elt = $('t'+node_id);
    dest = $('n'+node_id);

    if (elt.src.indexOf('plus')  != -1) elt.src = elt.src.replace('plus', 'minus');
    else if (elt.src.indexOf('minus')  != -1) elt.src = elt.src.replace('minus', 'plus');

    if ($(dest))
    {
        if ($(dest).style.display == 'none')
        {
            $(dest).style.display='block';
            if ($(dest).innerHTML.length < 20)
            {
                ploopi_ajaxloader(dest);
                ploopi_xmlhttprequest_todiv(script, query, dest);
            }
        }
        else $(dest).style.display='none';
    }
}

function ploopi_skin_array_refresh(array_id, array_orderby)
{
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=ploopi_skin_array_refresh&array_id='+array_id+'&array_orderby='+array_orderby+'&ploopi_randomize='+Math.random(),'ploopi_explorer_main_'+array_id);
}
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

// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

function ploopi_base64_encode(input) {
   var output = "";
   var chr1, chr2, chr3;
   var enc1, enc2, enc3, enc4;
   var i = 0;

   do {
      chr1 = input.charCodeAt(i++);
      chr2 = input.charCodeAt(i++);
      chr3 = input.charCodeAt(i++);

      enc1 = chr1 >> 2;
      enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
      enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
      enc4 = chr3 & 63;

      if (isNaN(chr2)) {
         enc3 = enc4 = 64;
      } else if (isNaN(chr3)) {
         enc4 = 64;
      }

      output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) +
         keyStr.charAt(enc3) + keyStr.charAt(enc4);
   } while (i < input.length);

   return output;
}

function ploopi_base64_decode(input) {
   var output = "";
   var chr1, chr2, chr3;
   var enc1, enc2, enc3, enc4;
   var i = 0;

   // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
   input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

   do {
      enc1 = keyStr.indexOf(input.charAt(i++));
      enc2 = keyStr.indexOf(input.charAt(i++));
      enc3 = keyStr.indexOf(input.charAt(i++));
      enc4 = keyStr.indexOf(input.charAt(i++));

      chr1 = (enc1 << 2) | (enc2 >> 4);
      chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
      chr3 = ((enc3 & 3) << 6) | enc4;

      output = output + String.fromCharCode(chr1);

      if (enc3 != 64) {
         output = output + String.fromCharCode(chr2);
      }
      if (enc4 != 64) {
         output = output + String.fromCharCode(chr3);
      }
   } while (i < input.length);

   return output;
}

function ploopi_addslashes(str)
{
    str = String(str);
    str = str.replace(/\\/g,"\\\\");
    str = str.replace(/\'/g,"\\'");
    str = str.replace(/\"/g,"\\\"");
    return(str);
}
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

function ploopi_subscription(ploopi_subscription_id, next)
{
    if (typeof(next) == 'undefined') next = '';
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=subscription&ploopi_subscription_id='+ploopi_subscription_id+'&next='+next, 'ploopi_subscription_'+ploopi_subscription_id);
}

function ploopi_subscription_checkaction(id_subscription, id_action)
{
    var ck = (id_action == -1) ? $('ploopi_subscription_unsubscribe') : $('ploopi_subscription_action_'+id_action);

    ck.checked = !ck.checked

    if (id_action == -1 && ck.checked) ploopi_checkall($('ploopi_form_subscription_'+id_subscription), 'ploopi_subscription_action_', false, true)

    if (id_action > -1 && $('ploopi_subscription_unsubscribe') && $('ploopi_subscription_unsubscribe').checked) $('ploopi_subscription_unsubscribe').checked = false;

    if (id_action == 0 && ck.checked) ploopi_checkall($('ploopi_form_subscription_'+id_subscription), 'ploopi_subscription_action_', true, true)

    if (id_action > 0 && !ck.checked && $('ploopi_subscription_action_0').checked) $('ploopi_subscription_action_0').checked = false;
}

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

/**
 * Ouverture d'un popup pour envoyer un nouveau message
 */
 
function ploopi_tickets_new(event, id_object, id_record, object_label, id_user, reload, x, y)
{
    var data = '';

    if (typeof(object_label) != 'undefined' && object_label != null) data += '&ploopi_tickets_object_label='+object_label;
    if (typeof(id_object) != 'undefined' && id_object != null) data += '&ploopi_tickets_id_object='+id_object;
    if (typeof(id_record) != 'undefined' && id_record != null) data += '&ploopi_tickets_id_record='+id_record;
    if (typeof(reload) != 'undefined' && reload != null) data += '&ploopi_tickets_reload='+reload;
    if (typeof(id_user) != 'undefined' && id_user != null) data += '&ploopi_tickets_id_user='+id_user;

    if (typeof(x) == 'undefined' || x == null) x = 0;
    if (typeof(y) == 'undefined' || y == null) y = 0;

    ploopi_showpopup('', 550, event, true, 'system_popupticket', x, y);
    ploopi_ajaxloader('system_popupticket');
    ploopi_xmlhttprequest_todiv('admin-light.php','ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_new'+data,'system_popupticket');
}

/**
 * Rafraichissement de la zone indiquant le nombre de tickets non lus + alerte sur nouveau ticket
 */

function ploopi_tickets_refresh(lastnewticket, timeout, str_left, str_right)
{
    var intPloopiLastNewTicket = lastnewticket;
    var boolAlert = false;

    if (typeof(str_left) == 'undefined') str_left = '';
    if (typeof(str_right) == 'undefined') str_right = '';

    new PeriodicalExecuter( function(pe) {
        new Ajax.Request('index-quick.php?ploopi_op=tickets_getnum',
            {
                method:     'get',
                encoding:   'iso-8859-15',
                onSuccess:  function(transport) {
                                 var res = transport.responseText.split(',');
                                 if (res.length == 2)
                                 {
                                     var nb = parseInt(res[0],10);
                                     var last = parseInt(res[1],10);

                                     $('tpl_ploopi_tickets_new').innerHTML =  str_left+nb+str_right;

                                     if (last > intPloopiLastNewTicket && !boolAlert)
                                     {
                                         ploopi_tickets_alert();
                                         boolAlert = true;
                                     }
                                     intPloopiLastNewTicket = last;
                                 }
                            }
            }
        );
    }
    ,timeout
    );
}

/**
 * Recherche de destinataires pour un message
 */

function ploopi_tickets_select_users(query, filtertype, filter, dest)
{
    new Ajax.Request('admin-light.php?'+query,
        {
            method:     'post',
            parameters: {'ploopi_ticket_userfilter': filter, 'ploopi_ticket_typefilter': filtertype},
            encoding:   'iso-8859-15',
            onSuccess:  function(transport) {
               ploopi_innerHTML(dest, transport.responseText);
            }
        } 
    );
}

/**
 * Affiche un popup d'alerte � la r�ception de nouveaux messages
 */

function ploopi_tickets_alert()
{
    ploopi_showpopup('', 350, null, true, 'popup_tickets_new_alert', 0, 200);
    ploopi_ajaxloader('popup_tickets_new_alert');
    ploopi_xmlhttprequest_todiv('admin-light.php', 'ploopi_env='+_PLOOPI_ENV+'&ploopi_op=tickets_alert', 'popup_tickets_new_alert');
}


/**
 * Recherche utilisateur
 */

function ploopi_tickets_selectusers_init()
{
    $('ploopi_ticket_userfilter').onkeyup = ploopi_tickets_selectusers_keypress;
    $('ploopi_ticket_userfilter').onkeypress = ploopi_tickets_selectusers_keypress;
}

function ploopi_tickets_selectusers_prevent(e)
{
    if (window.event) window.event.returnValue = false
    else e.preventDefault()
}

function ploopi_tickets_selectusers_keypress(e)
{
    e=e||window.event;
    src = (e.srcElement) ? e.srcElement : e.target;

    switch(e.keyCode)
    {
        case 9: case 13:
            ploopi_tickets_selectusers_prevent(e);
            ploopi_dispatch_onclick('ploopi_ticket_search_btn');
        break;
        
        default:
        break;
    }
}/*
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

function ploopi_openwin(url,w,h,name)
{
   var top = (screen.height-(h+60))/2;
   var left = (screen.width-w)/2;

   if(!name) name = 'ploopiwin';

   var ploopiwin = window.open(url,name,'top='+top+',left='+left+',width='+w+', height='+h+', status=no, menubar=no, toolbar=no, scrollbars=yes, resizable=yes, screenY=20, screenX=20');

   ploopiwin.focus();

   return(ploopiwin);
}

function ploopi_confirmform(form, message)
{
    if (confirm(message)) form.submit();
}

function ploopi_confirmlink(link, message)
{
    if (confirm(message)) location.href = link;
}

function ploopi_switchdisplay(id)
{
    e = $(id);
    if (e) e.style.display = (e.style.display == 'none') ? 'block' : 'none';
}

// clic sur une zone checkbox/radio
// g�n�re un event �quivalent au clic direct sur l'�l�ment

function ploopi_checkbox_click(e, inputfield_id)
{
    src = (e.srcElement) ? e.srcElement : e.target;

    if (typeof(src.id) == 'undefined' || src.id != inputfield_id)
    {
        if (Prototype.Browser.IE)
        {
            switch ($(inputfield_id).type)
            {
                case 'radio':
                    $(inputfield_id).checked = true;
                break;

                default:
                    $(inputfield_id).checked = !$(inputfield_id).checked;
                break;
            }

            $(inputfield_id).fireEvent('onchange');
        }
        else
        {

            var e = document.createEvent('MouseEvents');
            e.initEvent('click', false, false);
            $(inputfield_id).dispatchEvent(e);
        }
    }
}

function ploopi_checkall(form, mask, value, byid)
{
    var len = form.elements.length;
    var reg = new RegExp(mask,"g");

    if (!byid) byid = false;

    for (var i = 0; i < len; i++)
    {
        var e = form.elements[i];

        if (byid)
        {
            if (e.id.match(reg)) e.checked = value;
        }
        else
        {
            if (e.name.match(reg)) e.checked = value;
        }
    }
}

function ploopi_getelem(elem, obj)
{
    if (typeof(obj) != 'object') obj = document;

    return (obj.getElementById) ? obj.getElementById(elem) : eval("document.all['"+ploopi_addslashes(elem)+"']");
}

function ploopi_innerHTML(div, html)
{
    if ($(div))
    {
        $(div).innerHTML = html;
        $(div).innerHTML.evalScripts();
    }
}/*
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

/**
 * A class to parse color values
 * @author Stoyan Stefanov <sstoo@gmail.com>
 * @link   http://www.phpied.com/rgb-color-parser-in-javascript/
 * @license Use it if you like it
 */

function ploopi_rgbcolor(color_string)
{
    this.ok = false;

    // strip any leading #
    if (color_string.charAt(0) == '#') { // remove # if any
        color_string = color_string.substr(1,6);
    }

    color_string = color_string.replace(/ /g,'');
    color_string = color_string.toLowerCase();

    // before getting into regexps, try simple matches
    // and overwrite the input
    var simple_colors = {
        aliceblue: 'f0f8ff',
        antiquewhite: 'faebd7',
        aqua: '00ffff',
        aquamarine: '7fffd4',
        azure: 'f0ffff',
        beige: 'f5f5dc',
        bisque: 'ffe4c4',
        black: '000000',
        noir: '000000',
        blanchedalmond: 'ffebcd',
        blue: '0000ff',
        bleu: '0000ff',
        blueviolet: '8a2be2',
        brown: 'a52a2a',
        burlywood: 'deb887',
        cadetblue: '5f9ea0',
        chartreuse: '7fff00',
        chocolate: 'd2691e',
        coral: 'ff7f50',
        cornflowerblue: '6495ed',
        cornsilk: 'fff8dc',
        crimson: 'dc143c',
        cyan: '00ffff',
        darkblue: '00008b',
        darkcyan: '008b8b',
        darkgoldenrod: 'b8860b',
        darkgray: 'a9a9a9',
        darkgreen: '006400',
        darkkhaki: 'bdb76b',
        darkmagenta: '8b008b',
        darkolivegreen: '556b2f',
        darkorange: 'ff8c00',
        darkorchid: '9932cc',
        darkred: '8b0000',
        darksalmon: 'e9967a',
        darkseagreen: '8fbc8f',
        darkslateblue: '483d8b',
        darkslategray: '2f4f4f',
        darkturquoise: '00ced1',
        darkviolet: '9400d3',
        deeppink: 'ff1493',
        deepskyblue: '00bfff',
        dimgray: '696969',
        dodgerblue: '1e90ff',
        feldspar: 'd19275',
        firebrick: 'b22222',
        floralwhite: 'fffaf0',
        forestgreen: '228b22',
        fuchsia: 'ff00ff',
        gainsboro: 'dcdcdc',
        ghostwhite: 'f8f8ff',
        gold: 'ffd700',
        goldenrod: 'daa520',
        gray: '808080',
        gris: '808080',
        green: '008000',
        vert: '008000',
        greenyellow: 'adff2f',
        honeydew: 'f0fff0',
        hotpink: 'ff69b4',
        indianred : 'cd5c5c',
        indigo : '4b0082',
        ivory: 'fffff0',
        khaki: 'f0e68c',
        lavender: 'e6e6fa',
        lavenderblush: 'fff0f5',
        lawngreen: '7cfc00',
        lemonchiffon: 'fffacd',
        lightblue: 'add8e6',
        lightcoral: 'f08080',
        lightcyan: 'e0ffff',
        lightgoldenrodyellow: 'fafad2',
        lightgrey: 'd3d3d3',
        lightgreen: '90ee90',
        lightpink: 'ffb6c1',
        lightsalmon: 'ffa07a',
        lightseagreen: '20b2aa',
        lightskyblue: '87cefa',
        lightslateblue: '8470ff',
        lightslategray: '778899',
        lightsteelblue: 'b0c4de',
        lightyellow: 'ffffe0',
        lime: '00ff00',
        limegreen: '32cd32',
        linen: 'faf0e6',
        magenta: 'ff00ff',
        maroon: '800000',
        mediumaquamarine: '66cdaa',
        mediumblue: '0000cd',
        mediumorchid: 'ba55d3',
        mediumpurple: '9370d8',
        mediumseagreen: '3cb371',
        mediumslateblue: '7b68ee',
        mediumspringgreen: '00fa9a',
        mediumturquoise: '48d1cc',
        mediumvioletred: 'c71585',
        midnightblue: '191970',
        mintcream: 'f5fffa',
        mistyrose: 'ffe4e1',
        moccasin: 'ffe4b5',
        navajowhite: 'ffdead',
        navy: '000080',
        oldlace: 'fdf5e6',
        olive: '808000',
        olivedrab: '6b8e23',
        orange: 'ffa500',
        orangered: 'ff4500',
        orchid: 'da70d6',
        palegoldenrod: 'eee8aa',
        palegreen: '98fb98',
        paleturquoise: 'afeeee',
        palevioletred: 'd87093',
        papayawhip: 'ffefd5',
        peachpuff: 'ffdab9',
        peru: 'cd853f',
        pink: 'ffc0cb',
        rose: 'ffc0cb',
        plum: 'dda0dd',
        powderblue: 'b0e0e6',
        purple: '800080',
        red: 'ff0000',
        rouge: 'ff0000',
        rosybrown: 'bc8f8f',
        royalblue: '4169e1',
        saddlebrown: '8b4513',
        salmon: 'fa8072',
        sandybrown: 'f4a460',
        seagreen: '2e8b57',
        seashell: 'fff5ee',
        sienna: 'a0522d',
        silver: 'c0c0c0',
        skyblue: '87ceeb',
        slateblue: '6a5acd',
        slategray: '708090',
        snow: 'fffafa',
        springgreen: '00ff7f',
        steelblue: '4682b4',
        tan: 'd2b48c',
        teal: '008080',
        thistle: 'd8bfd8',
        tomato: 'ff6347',
        turquoise: '40e0d0',
        violet: 'ee82ee',
        violetred: 'd02090',
        wheat: 'f5deb3',
        white: 'ffffff',
        blanc: 'ffffff',
        whitesmoke: 'f5f5f5',
        yellow: 'ffff00',
        jaune: 'ffff00',
        yellowgreen: '9acd32'
    };
    for (var key in simple_colors) {
        if (color_string == key) {
            color_string = simple_colors[key];
        }
    }
    // emd of simple type-in colors

    // array of color definition objects
    var color_defs = [
        {
            re: /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,
            example: ['rgb(123, 234, 45)', 'rgb(255,234,245)'],
            process: function (bits){
                return [
                    parseInt(bits[1]),
                    parseInt(bits[2]),
                    parseInt(bits[3])
                ];
            }
        },
        {
            re: /^(\w{2})(\w{2})(\w{2})$/,
            example: ['#00ff00', '336699'],
            process: function (bits){
                return [
                    parseInt(bits[1], 16),
                    parseInt(bits[2], 16),
                    parseInt(bits[3], 16)
                ];
            }
        },
        {
            re: /^(\w{1})(\w{1})(\w{1})$/,
            example: ['#fb0', 'f0f'],
            process: function (bits){
                return [
                    parseInt(bits[1] + bits[1], 16),
                    parseInt(bits[2] + bits[2], 16),
                    parseInt(bits[3] + bits[3], 16)
                ];
            }
        }
    ];

    // search through the definitions to find a match
    for (var i = 0; i < color_defs.length; i++) {
        var re = color_defs[i].re;
        var processor = color_defs[i].process;
        var bits = re.exec(color_string);
        if (bits) {
            channels = processor(bits);
            this.r = channels[0];
            this.g = channels[1];
            this.b = channels[2];
            this.ok = true;
        }

    }

    // validate/cleanup values
    this.r = (this.r < 0 || isNaN(this.r)) ? 0 : ((this.r > 255) ? 255 : this.r);
    this.g = (this.g < 0 || isNaN(this.g)) ? 0 : ((this.g > 255) ? 255 : this.g);
    this.b = (this.b < 0 || isNaN(this.b)) ? 0 : ((this.b > 255) ? 255 : this.b);

    // some getters
    this.toRGB = function () {
        return 'rgb(' + this.r + ', ' + this.g + ', ' + this.b + ')';
    }
    this.toHex = function () {
        var r = this.r.toString(16);
        var g = this.g.toString(16);
        var b = this.b.toString(16);
        if (r.length == 1) r = '0' + r;
        if (g.length == 1) g = '0' + g;
        if (b.length == 1) b = '0' + b;
        return '#' + r + g + b;
    }

    // help
    this.getHelpXML = function () {

        var examples = new Array();
        // add regexps
        for (var i = 0; i < color_defs.length; i++) {
            var example = color_defs[i].example;
            for (var j = 0; j < example.length; j++) {
                examples[examples.length] = example[j];
            }
        }
        // add type-in colors
        for (var sc in simple_colors) {
            examples[examples.length] = sc;
        }

        var xml = document.createElement('ul');
        xml.setAttribute('id', 'rgbcolor-examples');
        for (var i = 0; i < examples.length; i++) {
            try {
                var list_item = document.createElement('li');
                var list_color = new ploopi_rgbcolor(examples[i]);
                var example_div = document.createElement('div');
                example_div.style.cssText =
                        'margin: 3px; '
                        + 'border: 1px solid black; '
                        + 'background:' + list_color.toHex() + '; '
                        + 'color:' + list_color.toHex()
                ;
                example_div.appendChild(document.createTextNode('test'));
                var list_item_value = document.createTextNode(
                    ' ' + examples[i] + ' -> ' + list_color.toRGB() + ' -> ' + list_color.toHex()
                );
                list_item.appendChild(example_div);
                list_item.appendChild(list_item_value);
                xml.appendChild(list_item);

            } catch(e){}
        }
        return xml;

    }
}

function ploopi_validatefield(field_label, field_object, field_type)
{
    var ok = true;
    var i;
    var nbpoint = 0;
    var msg = new String();
    var reg = new RegExp("<FIELD_LABEL>","gi");

    if (field_object)
    {
        field_value = field_object.value;

        /* V�rifie qu'un �l�ment de liste a �t� s�lectionn� */
        if (field_type == 'selected')
        {
            msg = lstmsg[9];
            ok = (field_object.selectedIndex > 0 && field_object.value != '');
        }

        /* V�rifie qu'une checkbox (ou bouton radio) � �t� coch�e */
        if (field_type == 'checked')
        {
            msg = lstmsg[9];
            ok = false;
            for (c = 0; c < field_object.length; c++)
            {
                if (field_object[c].checked) ok = true;
            }
        }

        /* V�rifie que le champ contient une num�ro de t�l�phone valide */
        if (field_type == 'phone' || field_type == 'emptyphone')
        {
            ok = (field_value.search(/^\+?(\([0-9 ]+\))?[0-9 ]+$/) != -1);
            if (field_type == 'emptyphone') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'phone' && field_value.length == 0) ? lstmsg[4] : lstmsg[11];
        }

        /* V�rifie que le champ contient une adresse email valide */
        if (field_type == 'email' || field_type == 'emptyemail')
        {
            ok = (field_value.search(/^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,4}$/) != -1);
            if (field_type == 'emptyemail') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'email' && field_value.length == 0) ? lstmsg[4] : lstmsg[0];
        }

        /* V�rifie que le champ contient une couleur valide */
        if (field_type == 'color')
        {
            var color = new ploopi_rgbcolor(field_value);
            if (!color.ok)
            {
                ok = false;
                msg = lstmsg[10];
            }
        }

        /* V�rifie que le champ contient une cha�ne non vide (espace non compris) */
        if (field_type == 'string')
        {
            ok = (field_value.replace(/(^\s*)|(\s*$)/g,'').length > 0)
            if (!ok) msg = lstmsg[4];
        }

        /* V�rifie que le champ contient une valeur enti�re ou vide */
        if (field_type == 'int' || field_type == 'emptyint')
        {
            ok = (field_value.search(/^(\-?[0-9]+)$/) != -1);
            if (field_type == 'emptyint') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'int' && field_value.length == 0) ? lstmsg[4] : lstmsg[5];
        }

        /* V�rifie que le champ contient une valeur r�elle ou vide */
        if (field_type == 'float' || field_type == 'emptyfloat')
        {
            ok = (field_value.search(/^(\-?((([0-9]+(\.)?)|([0-9]*\.[0-9]+))))$/) != -1);
            if (field_type == 'emptyfloat') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'float' && field_value.length == 0) ? lstmsg[4] : lstmsg[6];
        }

        /* V�rifie que le champ contient une date valide ou vide */
        if (field_type == 'date' || field_type == 'emptydate')
        {
            ok = (field_value.search(/^([0-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/) != -1);
            if (ok && field_value.length > 0)
            {
                var date_split = field_value.split("/");
                for (i=0;i<=2;i++) date_split[i] = parseInt(date_split[i],10);
                var datetotest = new Date(date_split[2], date_split[1]-1, date_split[0]);
                ok = ( datetotest.getDate() == date_split[0] && datetotest.getMonth() == date_split[1]-1 && datetotest.getFullYear() == date_split[2] );
            }

            if (field_type == 'emptydate') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'date' && field_value.length == 0) ? lstmsg[4] : lstmsg[7];
        }

        /* V�rifie que le champ contient une heure valide ou vide */
        if (field_type == 'time' || field_type == 'emptytime')
        {
            ok = (field_value.search(/^(0[0-9]|1[0-9]|2[0-4]):[0-5][0-9](:[0-5][0-9])?$/) != -1);
            if (field_type == 'emptytime') ok = (ok || field_value.length == 0);
            if (!ok) msg = (field_type == 'time' && field_value.length == 0) ? lstmsg[4] : lstmsg[8];
        }
    }
    else ok = false;

    if (!ok)
    {
        alert(msg.replace(reg,field_label));
        if (field_type != 'checked')
        {
            field_object.style.background = error_bgcolor;
            field_object.focus();
        }
    }

    return (ok);
}/*
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

var ploopi_ajaxloader_content = '<div style="text-align:center;padding:40px 10px;"><img src="./img/ajax-loader.gif"></div>';

function ploopi_ajaxloader(div)
{
    if (div && $(div)) $(div).innerHTML = ploopi_ajaxloader_content;
    else return ajaxloader;
}

function ploopi_gethttpobject(callback)
{
    var xmlhttp = false;

    /*@cc_on
    @if (@_jscript_version >= 5)
    try
    {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch (e)
    {
        try
        {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch (E)
        {
            xmlhttp = false;
        }
    }
    @else
    xmlhttp = false;
    @end @*/

    /* on essaie de cr�er l'objet si ce n'est pas d�j� fait */
    if (!xmlhttp && typeof XMLHttpRequest != 'undefined')
    {
        try
        {
            xmlhttp = new XMLHttpRequest();
        }
        catch (e)
        {
            xmlhttp = false;
        }
    }

    return xmlhttp;
}

function ploopi_sendxmldata(method, url, data, xmlhttp, asynchronous)
{
    if (!xmlhttp)
    {
        return false;
    }

    if(method == "GET")
    {
        if(data == 'null')
        {
            xmlhttp.open("GET", url, asynchronous);
            xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=ISO-8859-15');
        }
        else
        {
            xmlhttp.open("GET", url+"?"+data, asynchronous);
            xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=ISO-8859-15');
        }
        xmlhttp.send(null);
    }
    else if(method == "POST")
    {
        xmlhttp.open("POST", url, asynchronous);
        xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=ISO-8859-15');
        xmlhttp.send(data);
    }
    return true;
}

function ploopi_xmlhttprequest(url, data, asynchronous, getxml, method)
{
    if (typeof(asynchronous) == 'undefined') asynchronous = false;
    if (typeof(getxml) == 'undefined') getxml = false;
    if (typeof(method) == 'undefined') method = 'GET';

    var xmlhttp = ploopi_gethttpobject();
    ploopi_sendxmldata(method, url, data, xmlhttp, asynchronous);

    // if asynchronous = false => return request content
    if (!asynchronous)
    {
        if (getxml) return(xmlhttp.responseXML);
        else return(xmlhttp.responseText);
    }
}

function ploopi_xmlhttprequest_tofunction(url, data, callback, ticket, getxml, method)
{
    var xmlhttp = ploopi_gethttpobject();

    if (typeof(getxml) == 'undefined') getxml = false;
    if (typeof(method) == 'undefined') method = 'GET';

    if (xmlhttp)
    {
        /* on d�finit ce qui doit se passer quand la page r�pondra */
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState == 4)
            {
                if (xmlhttp.status == 200)
                {
                    if (getxml) callback(xmlhttp.responseXML,ticket);
                    else callback(xmlhttp.responseText,ticket);
                }
            }
        }
    }

    return !ploopi_sendxmldata(method, url, data, xmlhttp, true);
}

function ploopi_xmlhttprequest_todiv(url, data, div, method)
{
    // Suite refactoring 29/07/2008
    // ploopi_xmlhttprequest_todiv\( ?['"](.*)['"] ?, ?['"](.*)['"] ?, ?['"](.*)['"] ?, ?['"](.*)['"] ?\); => ploopi_xmlhttprequest_todiv('$1', '$2', '$4')

    var xmlhttp = ploopi_gethttpobject();

    if (typeof(method) == 'undefined') method = 'GET';

    if (xmlhttp)
    {
        /* on d�finit ce qui doit se passer quand la page r�pondra */
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState == 4)
            {
                if (xmlhttp.status == 200)
                {
                    ploopi_innerHTML(div, xmlhttp.responseText);
                }
            }
        }
    }

    return !ploopi_sendxmldata(method, url, data, xmlhttp, true);
}