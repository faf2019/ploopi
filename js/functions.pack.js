ploopi={};function ploopi_annotation(a){ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=annotation&id_annotation="+a,"ploopiannotation_"+a)}var tag_timer;var tag_search;var tag_results=new Array();var tag_last_array=new Array();var tag_new_array=new Array();var tag_lastedit="";var tag_modified=-1;function ploopi_annotation_tag_init(a){$("ploopi_annotationtags_"+a).onkeyup=ploopi_annotation_tag_keyup;$("ploopi_annotationtags_"+a).onkeypress=ploopi_annotation_tag_keypress}function ploopi_annotation_tag_search(b,a){clearTimeout(tag_timer);tag_search=a;tag_timer=setTimeout("ploopi_annotation_tag_searchtimeout('"+b+"')",100)}function ploopi_annotation_tag_searchtimeout(a){list_tags=tag_search.split(" ");if(list_tags.length>0){ploopi_xmlhttprequest_tofunction("index-quick.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=annotation_searchtags&tag="+list_tags[list_tags.length-1],ploopi_annotation_tag_display,a)}}function ploopi_annotation_tag_display(a,b){if(a!=""){tag_results=new Array();splited_result=a.split("|");tagstoprint="";for(i=0;i<splited_result.length;i++){detail=splited_result[i].split(";");if(tagstoprint!=""){tagstoprint+=" "}if(i==0){tagstoprint+="<b>"}tagstoprint+="<a href=\"javascript:ploopi_annotation_tag_complete('"+b+"',"+i+')">'+detail[0]+"</a> ("+detail[1]+")";if(i==0){tagstoprint+="</b>"}tag_results[i]=detail[0]}$("tagsfound_"+b).innerHTML=tagstoprint}else{$("tagsfound_"+b).innerHTML="";tag_results=new Array()}}function ploopi_annotation_tag_prevent(a){if(window.event){window.event.returnValue=false}else{a.preventDefault()}}function ploopi_annotation_tag_keypress(a){a=a||window.event;src=(a.srcElement)?a.srcElement:a.target;switch(a.keyCode){case 38:case 40:ploopi_annotation_tag_prevent(a);break;case 9:ploopi_annotation_tag_prevent(a);break;case 13:ploopi_annotation_tag_prevent(a);break;default:tag_lastedit=$(src.id).value;break}}function ploopi_annotation_tag_keyup(a){a=a||window.event;src=(a.srcElement)?a.srcElement:a.target;idrecord=src.id.split("_")[2];switch(a.keyCode){case 38:case 40:ploopi_annotation_tag_prevent(a);break;case 9:ploopi_annotation_tag_complete(idrecord);ploopi_annotation_tag_prevent(a);break;case 13:ploopi_annotation_tag_complete(idrecord);ploopi_annotation_tag_prevent(a);break;case 35:case 36:case 39:case 37:break;default:tag_last_array=new Array();tag_new_array=new Array();tag_last_array=tag_lastedit.split(" ");tag_new_array=$(src.id).value.split(" ");tag_modified=-1;for(i=0;i<tag_new_array.length;i++){if(tag_new_array[i]!=tag_last_array[i]){if(tag_modified==-1){tag_modified=i}else{tag_modified=-2}}}if(tag_modified>=0){ploopi_annotation_tag_search(idrecord,tag_new_array[tag_modified])}break}}function ploopi_annotation_tag_complete(b,a){if(!(a>=0)){a=0}if(tag_results[a]){tag_new_array[tag_modified]=tag_results[a];taglist="";for(i=0;i<tag_new_array.length;i++){if(taglist!=""){taglist+=" "}taglist+=tag_new_array[i]}$("ploopi_annotationtags_"+b).value=taglist.replace(/(^\s*)|(\s*$)/g,"")+" ";$("tagsfound_"+b).innerHTML=""}tag_results=new Array()}function ploopi_annotation_delete(a,b){if(confirm("�tes vous certain de vouloir supprimer cette annotation ?")){ploopi_xmlhttprequest("index-quick.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=annotation_delete&ploopi_annotation_id="+b)}ploopi_annotation(a)}function ploopi_annotation_validate(a){if(ploopi_validatefield("Titre",a.ploopi_annotationtags,"string")){return true}return false}function ploopi_calendar_open(a,b){ploopi_showpopup(ploopi_xmlhttprequest("index-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=calendar_open&selected_date="+$(a).value+"&inputfield_id="+a),192,b,false,"ploopi_popup_calendar",null,null,true)}function $S(a){a=$(a);if(a){return(a.style)}}function abPos(b){var b=(typeof(b)=="object"?b:$(b)),a={X:0,Y:0};while(b!=null){a.X+=b.offsetLeft;a.Y+=b.offsetTop;b=b.offsetParent}return(a)}function agent(a){return(Math.max(navigator.userAgent.toLowerCase().indexOf(a),0))}function toggle(a){$S(a).display=($S(a).display=="none"?"block":"none")}function within(d,b,f){return((d>=b&&d<=f)?true:false)}function XY(b,a){var d=agent("msie")?[event.clientX+document.body.scrollLeft,event.clientY+document.body.scrollTop]:[b.pageX,b.pageY];return(d[zero(a)])}function zero(a){a=parseInt(a);return(!isNaN(a)?a:0)}var maxValue={H:360,S:100,V:100},HSV={H:360,S:100,V:100};var slideHSV={H:360,S:100,V:100},zINDEX=15,stop=1;function HSVslide(p,b,n){function r(d){s=XY(d,1)-u.Y;t=XY(d)-u.X}function q(o,d,x){return(Math.min(o,Math.max(0,Math.ceil((parseInt(x)/d)*o))))}function k(o,d){if(within(o,0,d)){return(o)}else{if(o>d){return(d)}else{if(o<0){return("-"+l)}}}}function g(y){if(!stop){if(p!="drag"){r(y)}if(p=="SVslide"){f.left=k(t-l,162)+"px";f.top=k(s-l,162)+"px";slideHSV.S=q(100,162,f.left);slideHSV.V=100-q(100,162,f.top);HSVupdate()}else{if(p=="Hslide"){var d=k(s-l,163),x="HSV",A={};f.top=(d)+"px";slideHSV.H=q(360,163,d);for(var o in x){o=x.substr(o,1);A[o]=(o=="H")?maxValue[o]-q(maxValue[o],163,d):HSV[o]}HSVupdate(A);$S("SV").backgroundColor="#"+color.HSV_HEX({H:HSV.H,S:100,V:100})}else{if(p=="drag"){f.left=XY(y)+m-a+"px";f.top=XY(y,1)+h-v+"px"}}}}}if(stop){stop="";var f=$S(p!="drag"?p:b);if(p=="drag"){var m=parseInt(f.left),h=parseInt(f.top),a=XY(n),v=XY(n,1);$S(b).zIndex=zINDEX++}else{var u=abPos($(b)),t,s,l=(p=="Hslide")?2:4;u.X+=10;u.Y+=22;if(p=="SVslide"){slideHSV.H=HSV.H}}document.onmousemove=g;document.onmouseup=function(){stop=1;document.onmousemove="";document.onmouseup=""};g(n)}}function HSVupdate(a){a=color.HSV_HEX(HSV=a?a:slideHSV);$S("plugCUR").background="#"+a;$("colorpicker_inputcolor").value="#"+a;return(a)}function loadSV(){var b="";for(var a=165;a>=0;a--){b+='<div style="background: #'+color.HSV_HEX({H:Math.round((360/165)*a),S:100,V:100})+';"><br /></div>'}$("Hmodel").innerHTML=b}color={};color.cords=function(b){var d=b/2,a=(hsv.H/360)*(Math.PI*2),f=(hsv.S+(100-hsv.V))/100*(d/2);$S("mCur").left=Math.round(Math.abs(Math.round(Math.sin(a)*f)+d+3))+"px";$S("mCur").top=Math.round(Math.abs(Math.round(Math.cos(a)*f)-d-21))+"px"};color.HEX=function(a){a=Math.round(Math.min(Math.max(0,a),255));return("0123456789ABCDEF".charAt((a-a%16)/16)+"0123456789ABCDEF".charAt(a%16))};color.RGB_HEX=function(b){var a=color.HEX;return(a(b.R)+a(b.G)+a(b.B))};color.HEX_RGB=function(a){return({R:parseInt(a.substring(1,3),16),G:parseInt(a.substring(3,5),16),B:parseInt(a.substring(5,7),16),A:1})};color.HSV_RGB=function(d){var k,m,f,b,a,h=d.S/100,g=d.V/100,l=d.H/360;if(h>0){if(l>=1){l=0}l=6*l;F=l-Math.floor(l);f=Math.round(255*g*(1-h));b=Math.round(255*g*(1-(h*F)));a=Math.round(255*g*(1-(h*(1-F))));g=Math.round(255*g);switch(Math.floor(l)){case 0:k=g;m=a;b=f;break;case 1:k=b;m=g;b=f;break;case 2:k=f;m=g;b=a;break;case 3:k=f;m=b;b=g;break;case 4:k=a;m=f;b=g;break;case 5:k=g;m=f;b=b;break}return({R:k?k:0,G:m?m:0,B:b?b:0,A:1})}else{return({R:(g=Math.round(g*255)),G:g,B:g,A:1})}};color.RGB_HSV=function(f){var h=Math.max(f.R,f.G,f.B),g=h-Math.min(f.R,f.G,f.B),d,b,a;if(h!=0){b=Math.round(g/h*100);if(f.R==h){d=(f.G-f.B)/g}else{if(f.G==h){d=2+(f.B-f.R)/g}else{if(f.B==h){d=4+(f.R-f.G)/g}}}var d=Math.min(Math.round(d*60),360);if(d<0){d+=360}}return({H:d?d:0,S:b?b:0,V:Math.round((h/255)*100)})};color.HSV_HEX=function(a){return(color.RGB_HEX(color.HSV_RGB(a)))};color.HEX_HSV=function(a){return(color.RGB_HSV(color.HEX_RGB(a)))};function ploopi_colorpicker_open(a,d){if($(a).value==""){$(a).value="#ffffff"}ploopi_showpopup(ploopi_xmlhttprequest("admin-light.php","ploopi_op=colorpicker_open&inputfield_id="+a+"&colorpicker_value="+escape($(a).value)),220,d,false,"ploopi_popup_colorpicker",null,null,true);loadSV();var b=color.HEX_HSV($(a).value);HSVupdate(b);$S("SV").backgroundColor="#"+color.HSV_HEX({H:b.H,S:100,V:100});$S("SVslide").left=(Math.ceil((b.S*165)/100)-4)+"px";$S("SVslide").top=(165-Math.ceil((b.V*165)/100)-4)+"px";$S("Hslide").top=(165-Math.ceil((b.H*165)/360)-2)+"px"}function ploopi_documents_openfolder(b,a){ploopi_showpopup("",400,a,"click","ploopi_documents_openfolder_popup");ploopi_xmlhttprequest_todiv("admin-light.php",b,"ploopi_documents_openfolder_popup")}function ploopi_documents_openfile(b,a){ploopi_showpopup("",400,a,"click","ploopi_documents_openfile_popup");ploopi_xmlhttprequest_todiv("admin-light.php",b,"ploopi_documents_openfile_popup")}function ploopi_documents_deletefile(a,b){ploopi_xmlhttprequest_todiv("admin-light.php",a,"ploopidocuments_"+b)}function ploopi_documents_deletefolder(a,b){ploopi_xmlhttprequest_todiv("admin-light.php",a,"ploopidocuments_"+b)}function ploopi_documents_browser(b,d,a){if(typeof(a)=="undefined"){a=false}if(a){ploopi_ajaxloader("ploopidocuments_"+d);ploopi_xmlhttprequest_todiv("admin-light.php",b,"ploopidocuments_"+d)}else{ploopi_innerHTML("ploopidocuments_"+d,ploopi_xmlhttprequest("admin-light.php",b))}}function ploopi_documents_validate(a){if(a.documentsfile_name){if(!ploopi_validatefield("Fichier",a.documentsfile_name,"string")){return false}}else{if(!ploopi_validatefield("Fichier",a.documentsfile_file,"string")){return false}}if(ploopi_validatefield("Libell�",a.documentsfile_label,"string")){return true}return false}function ploopi_documents_popup(f,d,h,a,b){var g=ploopi_base64_encode(h+"_"+f+"_"+ploopi_addslashes(d)+"_popup");ploopi_showpopup(""+ploopi_xmlhttprequest("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=documents_selectfile&id_object="+f+"&id_record="+d+"&documents_id="+g+"&destfield="+a)+"",600,b,"click","ploopi_documents_popup")}var ploopi_window_onload_functions=new Array();var ploopi_window_onunload_functions=new Array();function ploopi_window_onload_stock(a){ploopi_window_onload_functions[ploopi_window_onload_functions.length]=a}function ploopi_window_onload_launch(){window.onload=function(){for(var a=0;a<ploopi_window_onload_functions.length;a++){ploopi_window_onload_functions[a]()}}}function ploopi_window_onunload_stock(a){ploopi_window_onunload_functions[ploopi_window_onunload_functions.length]=a}function ploopi_window_onunload_launch(){window.onunload=function(){for(var a=0;a<ploopi_window_onunload_functions.length;a++){ploopi_window_onunload_functions[a]()}}}function ploopi_dispatch_onchange(a){if(Prototype.Browser.IE){$(a).fireEvent("onChange")}else{var b=document.createEvent("HTMLEvents");b.initEvent("change",false,false);$(a).dispatchEvent(b)}}function ploopi_dispatch_onclick(a){if(Prototype.Browser.IE){$(a).fireEvent("onclick")}else{var b=document.createEvent("MouseEvents");b.initEvent("click",false,false);$(a).dispatchEvent(b)}}ploopi_window_onload_launch();ploopi_window_onunload_launch();function ploopi_filexplorer_popup(b,a){ploopi_showpopup(ploopi_ajaxloader_content,600,a,true,"ploopi_filexplorer_popup");ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=filexplorer_browser&filexplorer_id="+b,"ploopi_filexplorer_popup")}function ploopi_filexplorer_browser(b,a){ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=filexplorer_browser&filexplorer_id="+b+"&filexplorer_folder="+a,"ploopi_filexplorer_popup")}var ploopi_nbpopup=0;var ploopi_arrpopup=new Array();function ploopi_showpopup(popup_content,w,e,centered,id,pposx,pposy,enable_esc){var ploopi_popup;var active_effect=false;if(!id){id="ploopi_popup"}if(typeof(enable_esc)=="undefined"){enable_esc=false}if(!$(id)){bodys=document.getElementsByTagName("body");ploopi_nbpopup++;ploopi_popup=document.createElement("div");ploopi_popup.setAttribute("class","ploopi_popup");ploopi_popup.setAttribute("className","ploopi_popup");ploopi_popup.setAttribute("id",id);ploopi_popup.setAttribute("style","z-index:"+(1000+ploopi_nbpopup)+";");ploopi_popup.style.display="none";bodys[0].appendChild(ploopi_popup);active_effect=true;if(enable_esc){ploopi_arrpopup.push(id)}}else{ploopi_popup=$(id);if(enable_esc&&ploopi_arrpopup[ploopi_arrpopup.length-1]!=id){var ploopi_arrpopup_tmp=new Array();for(var i=0;i<ploopi_arrpopup.length;++i){if(ploopi_arrpopup[i]!=id){ploopi_arrpopup_tmp.push(ploopi_arrpopup[i])}}ploopi_arrpopup=ploopi_arrpopup_tmp;ploopi_arrpopup.push(id)}}w=parseInt(w);if(!w){w=200}var posx=0;var posy=0;pposx=parseInt(pposx);pposy=parseInt(pposy);if(pposx){posx=pposx}if(pposy){posy=pposy}if(e){if(e.pageX||e.pageY){posx=e.pageX;posy=e.pageY}else{if(e.clientX||e.clientY){var coordScroll=document.viewport.getScrollOffsets();posx=e.clientX+coordScroll.left;posy=e.clientY+coordScroll.top}}}else{switch(centered){case false:break;default:case true:var coordScroll=document.viewport.getScrollOffsets();var posx=parseInt(document.viewport.getWidth()/2)-parseInt(w/2)+coordScroll.left;var posy=parseInt(coordScroll.top)+20;break}}with(ploopi_popup.style){if(typeof(popup_content)!="undefined"){ploopi_innerHTML(id,popup_content)}tmpleft=parseInt(posx)+20;tmptop=parseInt(posy);if(w>0){width=w+"px"}else{w=parseInt(ploopi_popup.offsetWidth)}if(e&&((20+w+parseInt(tmpleft))>parseInt(document.viewport.getWidth()))){tmpleft=parseInt(tmpleft)-w-40}left=tmpleft+"px";top=tmptop+"px"}if(active_effect){new Effect.Appear(id,{duration:0.4,from:0,to:1})}if(enable_esc){Event.stopObserving(document,"keydown");ploopi_popupEnableEscape();Event.observe(id,"click",function(event){if(ploopi_arrpopup[ploopi_arrpopup.length-1]!=id){var ploopi_arrpopup_tmp=new Array();for(var i=0;i<ploopi_arrpopup.length;++i){if(ploopi_arrpopup[i]!=id){ploopi_arrpopup_tmp.push(ploopi_arrpopup[i])}}ploopi_arrpopup=ploopi_arrpopup_tmp;ploopi_arrpopup.push(id)}})}}function ploopi_movepopup(id,e,pposx,pposy,popup_content){var ploopi_popup;if(!id){id="ploopi_popup"}ploopi_popup=$(id);posx=0;posy=0;pposx=parseInt(pposx);pposy=parseInt(pposy);if(pposx){posx=pposx}if(pposy){posy=pposy}if(e){if(e.pageX||e.pageY){posx=e.pageX;posy=e.pageY}else{if(e.clientX||e.clientY){posx=e.clientX+document.body.scrollLeft;posy=e.clientY+document.body.scrollTop}}}with(ploopi_popup.style){ploopi_innerHTML(id,popup_content);tmpleft=parseInt(posx)+20;tmptop=parseInt(posy);w=parseInt(ploopi_popup.offsetWidth);if(20+w+parseInt(tmpleft)>parseInt(document.body.offsetWidth)){tmpleft=parseInt(tmpleft)-w-40}left=tmpleft+"px";top=tmptop+"px"}}function ploopi_hidepopup(a){if(!a){a="ploopi_popup"}if($(a)){new Effect.Fade(a,{duration:0.3,afterFinish:function(){var b=document.getElementsByTagName("body");b[0].removeChild($(a));if(ploopi_arrpopup.length>0){var f=new Array();for(var d=0;d<ploopi_arrpopup.length;++d){if(ploopi_arrpopup[d]!=a){f.push(ploopi_arrpopup[d])}}ploopi_arrpopup=f;Event.stopObserving(a,"click");if(ploopi_arrpopup.length==0){Event.stopObserving(document,"keydown")}}}})}}function ploopi_hideallpopups(){var d=document.getElementsByClassName("ploopi_popup");var b=document.getElementsByTagName("body");var a=d.length;for(var f=0;f<a;f++){b[0].removeChild(d[f]);Event.stopObserving(d[f],"click")}if(ploopi_arrpopup.length>0){ploopi_arrpopup=new Array();Event.stopObserving(document,"keydown")}}function ploopi_popupize(k,d,f,b,a){if($(k)){$(k).setAttribute("class","ploopi_popup");$(k).setAttribute("className","ploopi_popup");$(k).setAttribute("style","z-index:"+(1000+ploopi_nbpopup)+";");d=parseInt(d);if(!d){d=200}bodys=document.getElementsByTagName("body");switch(f){case false:b=parseInt(b);a=parseInt(a);break;default:case true:var h=parseInt(bodys[0].offsetWidth);var g=parseInt(bodys[0].scrollLeft);b=(h/2)-(d/2)+g;break}$(k).style.left=b+"px";$(k).style.top=a+"px";bodys[0].appendChild($(k));new Effect.Appear(k,{duration:0.4,from:0,to:1})}}function ploopi_popupEnableEscape(){Event.observe(document,"keydown",function(a){if(a.keyCode==Event.KEY_ESC){ploopi_hidepopup(ploopi_arrpopup[ploopi_arrpopup.length-1]);ploopi_arrpopup.pop()}})}ploopi_skin_array_renderupdate_done=new Array();function ploopi_skin_array_renderupdate(a){greater=$("ploopi_explorer_values_inner_"+a).offsetHeight>$("ploopi_explorer_values_outer_"+a).offsetHeight;if(greater){if(typeof(ploopi_skin_array_renderupdate_done[a])=="undefined"){$("ploopi_explorer_title_"+a).innerHTML="<div style='float:right;width:16px;'>&nbsp;</div>"+$("ploopi_explorer_title_"+a).innerHTML;columns=$("ploopi_explorer_main_"+a).getElementsByClassName("ploopi_explorer_column");for(j=0;j<columns.length;j++){if(columns[j].style.right!=""){diff=(Prototype.Browser.IE)?22:16;columns[j].style.right=(parseInt(columns[j].style.right)+diff)+"px"}}ploopi_skin_array_renderupdate_done[a]=true}}if(Prototype.Browser.IE){columns=$("ploopi_explorer_main_"+a).getElementsByClassName("ploopi_explorer_column");for(j=0;j<columns.length;j++){columns[j].style.height=$("ploopi_explorer_main_"+a).offsetHeight+"px"}}}function ploopi_skin_treeview_shownode(d,b,a){if(typeof(a)=="undefined"){a="admin-light.php"}elt=$("t"+d);dest=$("n"+d);treenode=$("treeview_node"+d);treenode.className="treeview_node_loading";if(elt.src.indexOf("plus")!=-1){elt.src=elt.src.replace("plus","minus")}else{if(elt.src.indexOf("minus")!=-1){elt.src=elt.src.replace("minus","plus")}}if($(dest)){if($(dest).style.display=="none"){if($(dest).innerHTML.length<20){$(dest).innerHTML=ploopi_xmlhttprequest(a,b)}new Effect.BlindDown(dest,{duration:0.2,afterFinish:function(){treenode.className="treeview_node"}})}else{new Effect.BlindUp(dest,{duration:0.2,afterFinish:function(){treenode.className="treeview_node"}})}}}function ploopi_skin_array_refresh(a,b,d){ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=ploopi_skin_array_refresh&array_id="+a+"&array_orderby="+b+"&array_page="+d+"&ploopi_randomize="+Math.random(),"ploopi_explorer_main_"+a)}var keyStr="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_=";function ploopi_base64_encode(d){var a="";var n,l,h;var m,k,g,f;var b=0;do{n=d.charCodeAt(b++);l=d.charCodeAt(b++);h=d.charCodeAt(b++);m=n>>2;k=((n&3)<<4)|(l>>4);g=((l&15)<<2)|(h>>6);f=h&63;if(isNaN(l)){g=f=64}else{if(isNaN(h)){f=64}}a=a+keyStr.charAt(m)+keyStr.charAt(k)+keyStr.charAt(g)+keyStr.charAt(f)}while(b<d.length);return a}function ploopi_base64_decode(d){var a="";var n,l,h;var m,k,g,f;var b=0;d=d.replace(/[^A-Za-z0-9\+\/\=]/g,"");do{m=keyStr.indexOf(d.charAt(b++));k=keyStr.indexOf(d.charAt(b++));g=keyStr.indexOf(d.charAt(b++));f=keyStr.indexOf(d.charAt(b++));n=(m<<2)|(k>>4);l=((k&15)<<4)|(g>>2);h=((g&3)<<6)|f;a=a+String.fromCharCode(n);if(g!=64){a=a+String.fromCharCode(l)}if(f!=64){a=a+String.fromCharCode(h)}}while(b<d.length);return a}function ploopi_addslashes(a){a=String(a);a=a.replace(/\\/g,"\\\\");a=a.replace(/\'/g,"\\'");a=a.replace(/\"/g,'\\"');return(a)}function ploopi_subscription(b,a){if(typeof(a)=="undefined"){a=""}ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=subscription&ploopi_subscription_id="+b+"&next="+a,"ploopi_subscription_"+b)}function ploopi_subscription_checkaction(a,b){var d=(b==-1)?$("ploopi_subscription_unsubscribe"):$("ploopi_subscription_action_"+b);d.checked=!d.checked;if(b==-1&&d.checked){ploopi_checkall($("ploopi_form_subscription_"+a),"ploopi_subscription_action_",false,true)}if(b>-1&&$("ploopi_subscription_unsubscribe")&&$("ploopi_subscription_unsubscribe").checked){$("ploopi_subscription_unsubscribe").checked=false}if(b==0&&d.checked){ploopi_checkall($("ploopi_form_subscription_"+a),"ploopi_subscription_action_",true,true)}if(b>0&&!d.checked&&$("ploopi_subscription_action_0").checked){$("ploopi_subscription_action_0").checked=false}}function ploopi_tickets_new(b,d,g,m,h,a,l,k){var f="";if(typeof(m)!="undefined"&&m!=null){f+="&ploopi_tickets_object_label="+m}if(typeof(d)!="undefined"&&d!=null){f+="&ploopi_tickets_id_object="+d}if(typeof(g)!="undefined"&&g!=null){f+="&ploopi_tickets_id_record="+g}if(typeof(a)!="undefined"&&a!=null){f+="&ploopi_tickets_reload="+a}if(typeof(h)!="undefined"&&h!=null){f+="&ploopi_tickets_id_user="+h}if(typeof(l)=="undefined"||l==null){l=0}if(typeof(k)=="undefined"||k==null){k=0}ploopi_showpopup("",550,b,true,"system_popupticket",l,k);ploopi_ajaxloader("system_popupticket");ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=tickets_new"+f,"system_popupticket")}function ploopi_tickets_refresh(h,g,d,b){var a=h;var f=false;if(typeof(d)=="undefined"){d=""}if(typeof(b)=="undefined"){b=""}new PeriodicalExecuter(function(k){new Ajax.Request("index-quick.php?ploopi_op=tickets_getnum",{method:"get",encoding:"iso-8859-15",onSuccess:function(o){var m=o.responseText.split(",");if(m.length==2){var l=parseInt(m[0],10);var n=parseInt(m[1],10);$("tpl_ploopi_tickets_new").innerHTML=d+l+b;if(n>a&&!f){ploopi_tickets_alert();f=true}a=n}}})},g)}function ploopi_tickets_select_users(d,f,b,a){new Ajax.Request("admin-light.php?"+d,{method:"post",parameters:{ploopi_ticket_userfilter:b,ploopi_ticket_typefilter:f},encoding:"iso-8859-15",onSuccess:function(g){ploopi_innerHTML(a,g.responseText)}})}function ploopi_tickets_alert(){ploopi_showpopup("",350,null,true,"popup_tickets_new_alert",0,200);ploopi_ajaxloader("popup_tickets_new_alert");ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=tickets_alert","popup_tickets_new_alert")}function ploopi_tickets_selectusers_init(){$("ploopi_ticket_userfilter").onkeyup=ploopi_tickets_selectusers_keypress;$("ploopi_ticket_userfilter").onkeypress=ploopi_tickets_selectusers_keypress}function ploopi_tickets_selectusers_prevent(a){if(window.event){window.event.returnValue=false}else{a.preventDefault()}}function ploopi_tickets_selectusers_keypress(a){a=a||window.event;src=(a.srcElement)?a.srcElement:a.target;switch(a.keyCode){case 9:case 13:ploopi_tickets_selectusers_prevent(a);ploopi_dispatch_onclick("ploopi_ticket_search_btn");break;default:break}}function ploopi_ticket_validateTo(b,a){var d=true;var g=new String();var f=new RegExp("<FIELD_LABEL>","gi");if(a){field_value=a.value;d=(field_value.replace(/(^\s*)|(\s*$)/g,"").length>0)}else{d=false}if(!d){g=lstmsg[4];alert(g.replace(f,b))}return(d)}function ploopi_openwin(f,b,g,d){var l=(screen.height-(g+60))/2;var k=(screen.width-b)/2;if(!d){d="ploopiwin"}var a=window.open(f,d,"top="+l+",left="+k+",width="+b+", height="+g+", status=no, menubar=no, toolbar=no, scrollbars=yes, resizable=yes, screenY=20, screenX=20");a.focus();return(a)}function ploopi_confirmform(b,a){if(confirm(a)){b.submit()}}function ploopi_confirmlink(b,a){if(confirm(a)){location.href=b}}function ploopi_switchdisplay(a){e=$(a);if(e){e.style.display=(e.style.display=="none")?"block":"none"}}function ploopi_checkbox_click(b,a){src=(b.srcElement)?b.srcElement:b.target;if(typeof(src.id)=="undefined"||src.id!=a){if(Prototype.Browser.IE){switch($(a).type){case"radio":$(a).checked=true;break;default:$(a).checked=!$(a).checked;break}$(a).fireEvent("onchange")}else{var b=document.createEvent("MouseEvents");b.initEvent("click",false,false);$(a).dispatchEvent(b)}}}function ploopi_checkall(h,b,k,d){var a=h.elements.length;var g=new RegExp(b,"g");if(!d){d=false}for(var f=0;f<a;f++){var l=h.elements[f];if(d){if(l.id.match(g)){l.checked=k}}else{if(l.name.match(g)){l.checked=k}}}}function ploopi_getelem(elem,obj){if(typeof(obj)!="object"){obj=document}return(obj.getElementById)?obj.getElementById(elem):eval("document.all['"+ploopi_addslashes(elem)+"']")}function ploopi_innerHTML(b,a){if($(b)){$(b).innerHTML=a;$(b).innerHTML.evalScripts()}}function ploopi_insertatcursor(f,d){if(document.selection){f.focus();sel=document.selection.createRange();sel.text=d}else{if(f.selectionStart||f.selectionStart=="0"){var b=f.selectionStart;var a=f.selectionEnd;f.value=f.value.substring(0,b)+d+f.value.substring(a,f.value.length)}else{f.value+=d}}}function ploopi_rgbcolor(k){this.ok=false;if(k.charAt(0)=="#"){k=k.substr(1,6)}k=k.replace(/ /g,"");k=k.toLowerCase();var a={aliceblue:"f0f8ff",antiquewhite:"faebd7",aqua:"00ffff",aquamarine:"7fffd4",azure:"f0ffff",beige:"f5f5dc",bisque:"ffe4c4",black:"000000",noir:"000000",blanchedalmond:"ffebcd",blue:"0000ff",bleu:"0000ff",blueviolet:"8a2be2",brown:"a52a2a",burlywood:"deb887",cadetblue:"5f9ea0",chartreuse:"7fff00",chocolate:"d2691e",coral:"ff7f50",cornflowerblue:"6495ed",cornsilk:"fff8dc",crimson:"dc143c",cyan:"00ffff",darkblue:"00008b",darkcyan:"008b8b",darkgoldenrod:"b8860b",darkgray:"a9a9a9",darkgreen:"006400",darkkhaki:"bdb76b",darkmagenta:"8b008b",darkolivegreen:"556b2f",darkorange:"ff8c00",darkorchid:"9932cc",darkred:"8b0000",darksalmon:"e9967a",darkseagreen:"8fbc8f",darkslateblue:"483d8b",darkslategray:"2f4f4f",darkturquoise:"00ced1",darkviolet:"9400d3",deeppink:"ff1493",deepskyblue:"00bfff",dimgray:"696969",dodgerblue:"1e90ff",feldspar:"d19275",firebrick:"b22222",floralwhite:"fffaf0",forestgreen:"228b22",fuchsia:"ff00ff",gainsboro:"dcdcdc",ghostwhite:"f8f8ff",gold:"ffd700",goldenrod:"daa520",gray:"808080",gris:"808080",green:"008000",vert:"008000",greenyellow:"adff2f",honeydew:"f0fff0",hotpink:"ff69b4",indianred:"cd5c5c",indigo:"4b0082",ivory:"fffff0",khaki:"f0e68c",lavender:"e6e6fa",lavenderblush:"fff0f5",lawngreen:"7cfc00",lemonchiffon:"fffacd",lightblue:"add8e6",lightcoral:"f08080",lightcyan:"e0ffff",lightgoldenrodyellow:"fafad2",lightgrey:"d3d3d3",lightgreen:"90ee90",lightpink:"ffb6c1",lightsalmon:"ffa07a",lightseagreen:"20b2aa",lightskyblue:"87cefa",lightslateblue:"8470ff",lightslategray:"778899",lightsteelblue:"b0c4de",lightyellow:"ffffe0",lime:"00ff00",limegreen:"32cd32",linen:"faf0e6",magenta:"ff00ff",maroon:"800000",mediumaquamarine:"66cdaa",mediumblue:"0000cd",mediumorchid:"ba55d3",mediumpurple:"9370d8",mediumseagreen:"3cb371",mediumslateblue:"7b68ee",mediumspringgreen:"00fa9a",mediumturquoise:"48d1cc",mediumvioletred:"c71585",midnightblue:"191970",mintcream:"f5fffa",mistyrose:"ffe4e1",moccasin:"ffe4b5",navajowhite:"ffdead",navy:"000080",oldlace:"fdf5e6",olive:"808000",olivedrab:"6b8e23",orange:"ffa500",orangered:"ff4500",orchid:"da70d6",palegoldenrod:"eee8aa",palegreen:"98fb98",paleturquoise:"afeeee",palevioletred:"d87093",papayawhip:"ffefd5",peachpuff:"ffdab9",peru:"cd853f",pink:"ffc0cb",rose:"ffc0cb",plum:"dda0dd",powderblue:"b0e0e6",purple:"800080",red:"ff0000",rouge:"ff0000",rosybrown:"bc8f8f",royalblue:"4169e1",saddlebrown:"8b4513",salmon:"fa8072",sandybrown:"f4a460",seagreen:"2e8b57",seashell:"fff5ee",sienna:"a0522d",silver:"c0c0c0",skyblue:"87ceeb",slateblue:"6a5acd",slategray:"708090",snow:"fffafa",springgreen:"00ff7f",steelblue:"4682b4",tan:"d2b48c",teal:"008080",thistle:"d8bfd8",tomato:"ff6347",turquoise:"40e0d0",violet:"ee82ee",violetred:"d02090",wheat:"f5deb3",white:"ffffff",blanc:"ffffff",whitesmoke:"f5f5f5",yellow:"ffff00",jaune:"ffff00",yellowgreen:"9acd32"};for(var d in a){if(k==d){k=a[d]}}var l=[{re:/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,example:["rgb(123, 234, 45)","rgb(255,234,245)"],process:function(m){return[parseInt(m[1]),parseInt(m[2]),parseInt(m[3])]}},{re:/^(\w{2})(\w{2})(\w{2})$/,example:["#00ff00","336699"],process:function(m){return[parseInt(m[1],16),parseInt(m[2],16),parseInt(m[3],16)]}},{re:/^(\w{1})(\w{1})(\w{1})$/,example:["#fb0","f0f"],process:function(m){return[parseInt(m[1]+m[1],16),parseInt(m[2]+m[2],16),parseInt(m[3]+m[3],16)]}}];for(var b=0;b<l.length;b++){var g=l[b].re;var f=l[b].process;var h=g.exec(k);if(h){channels=f(h);this.r=channels[0];this.g=channels[1];this.b=channels[2];this.ok=true}}this.r=(this.r<0||isNaN(this.r))?0:((this.r>255)?255:this.r);this.g=(this.g<0||isNaN(this.g))?0:((this.g>255)?255:this.g);this.b=(this.b<0||isNaN(this.b))?0:((this.b>255)?255:this.b);this.toRGB=function(){return"rgb("+this.r+", "+this.g+", "+this.b+")"};this.toHex=function(){var o=this.r.toString(16);var n=this.g.toString(16);var m=this.b.toString(16);if(o.length==1){o="0"+o}if(n.length==1){n="0"+n}if(m.length==1){m="0"+m}return"#"+o+n+m};this.getHelpXML=function(){var o=new Array();for(var q=0;q<l.length;q++){var n=l[q].example;for(var p=0;p<n.length;p++){o[o.length]=n[p]}}for(var v in a){o[o.length]=v}var r=document.createElement("ul");r.setAttribute("id","rgbcolor-examples");for(var q=0;q<o.length;q++){try{var s=document.createElement("li");var u=new ploopi_rgbcolor(o[q]);var x=document.createElement("div");x.style.cssText="margin: 3px; border: 1px solid black; background:"+u.toHex()+"; color:"+u.toHex();x.appendChild(document.createTextNode("test"));var m=document.createTextNode(" "+o[q]+" -> "+u.toRGB()+" -> "+u.toHex());s.appendChild(x);s.appendChild(m);r.appendChild(s)}catch(t){}}return r}}function ploopi_validatefield(o,a,n){var l=true;var g;var h=0;var d=new String();var b=new RegExp("<FIELD_LABEL>","gi");if(a){field_value=a.value;if(n=="selected"){d=lstmsg[9];l=(a.selectedIndex>=0&&a.value!="")}if(n=="checked"){l=false;if(a.length==undefined){d=lstmsg[14];if(a.checked){l=true}}else{d=lstmsg[9];for(c=0;c<a.length;c++){if(a[c].checked){l=true}}}}if(n=="phone"||n=="emptyphone"){if(n=="emptyphone"&&field_value.length==0){l=true}else{l=(field_value.search(/^\+?(\([0-9 ]+\))?[0-9 ]+$/)!=-1)}if(!l){d=(n=="phone"&&field_value.length==0)?lstmsg[4]:lstmsg[11]}}if(n=="email"||n=="emptyemail"){if(n=="emptyemail"&&field_value.length==0){l=true}else{l=(field_value.search(/^[a-zA-Z0-9]{1}([a-zA-Z0-9._-])+@[a-zA-Z0-9]{1}([a-zA-Z0-9.-])+\.[a-zA-Z]{2,4}$/)!=-1)}if(!l){d=(n=="email"&&field_value.length==0)?lstmsg[4]:lstmsg[0]}}if(n=="url"||n=="emptyurl"){if(n=="emptyurl"&&field_value.length==0){l=true}else{l=(field_value.search(/^(((ht|f)tp(s?))\:\/\/)?(([a-zA-Z0-9]+([@\-\.]?[a-zA-Z0-9]+)*)(\:[a-zA-Z0-9\-\.]+)?@)?(www.|ftp.|[a-zA-Z]+.)?[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,})(\:[0-9]+)?\/?/g)!=-1)}if(!l){d=(n=="url"&&field_value.length==0)?lstmsg[4]:lstmsg[13]}}if(n=="color"){var f=new ploopi_rgbcolor(field_value);if(!f.ok){l=false;d=lstmsg[10]}}if(n=="string"){l=(field_value.replace(/(^\s*)|(\s*$)/g,"").length>0);if(!l){d=lstmsg[4]}}if(n=="int"||n=="emptyint"){if(n=="emptyint"&&field_value.length==0){l=true}else{l=(field_value.search(/^(\-?[0-9]+)$/)!=-1)}if(!l){d=(n=="int"&&field_value.length==0)?lstmsg[4]:lstmsg[5]}}if(n=="float"||n=="emptyfloat"){if(n=="emptyfloat"&&field_value.length==0){l=true}else{l=(field_value.search(/^(\-?((([0-9]+(\.)?)|([0-9]*\.[0-9]+))))$/)!=-1)}if(!l){d=(n=="float"&&field_value.length==0)?lstmsg[4]:lstmsg[6]}}if(n=="date"||n=="emptydate"){if(n=="emptydate"&&field_value.length==0){l=true}else{l=(field_value.search(/^([0-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/)!=-1);if(l&&field_value.length>0){var m=field_value.split("/");for(g=0;g<=2;g++){m[g]=parseInt(m[g],10)}var k=new Date(m[2],m[1]-1,m[0]);l=(k.getDate()==m[0]&&k.getMonth()==m[1]-1&&k.getFullYear()==m[2])}}if(!l){d=(n=="date"&&field_value.length==0)?lstmsg[4]:lstmsg[7]}}if(n=="time"||n=="emptytime"){if(n=="emptytime"&&field_value.length==0){l=true}else{l=(field_value.search(/^(0[0-9]|1[0-9]|2[0-4]):[0-5][0-9](:[0-5][0-9])?$/)!=-1)}if(!l){d=(n=="time"&&field_value.length==0)?lstmsg[4]:lstmsg[8]}}if(n=="captcha"){l=false;if(arguments.length>=4&&field_value.length>0){new Ajax.Request(arguments[3],{asynchronous:false,methode:"get",parameters:"value="+field_value,onSuccess:function(p){l=(p.responseText==1)?true:false}})}if((!l)&&arguments.length>=6&&$(arguments[4])&&arguments[5].length>0){$(arguments[4]).src=arguments[5]+"&random="+Math.random()}if(!l){d=(n=="captcha"&&field_value.length==0)?lstmsg[4]:lstmsg[12]}}}else{l=false}if(!l){alert(d.replace(b,o));if(n!="checked"){a.style.background=error_bgcolor;a.focus()}}return(l)}var ploopi_ajaxloader_content='<div style="text-align:center;padding:40px 10px;"><img src="./img/ajax-loader.gif"></div>';function ploopi_ajaxloader(a){if(a&&$(a)){$(a).innerHTML=ploopi_ajaxloader_content}else{return ajaxloader}}function ploopi_gethttpobject(callback){var xmlhttp=false;
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
if(!xmlhttp&&typeof XMLHttpRequest!="undefined"){try{xmlhttp=new XMLHttpRequest()}catch(e){xmlhttp=false}}return xmlhttp}function ploopi_sendxmldata(g,b,f,d,a){if(!d){return false}if(g=="GET"){if(f=="null"){d.open("GET",b,a);d.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=ISO-8859-15")}else{d.open("GET",b+"?"+f,a);d.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=ISO-8859-15")}d.send(null)}else{if(g=="POST"){d.open("POST",b,a);d.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=ISO-8859-1");d.send(f)}}return true}function ploopi_xmlhttprequest(b,g,a,f,h){if(typeof(a)=="undefined"){a=false}if(typeof(f)=="undefined"){f=false}if(typeof(h)=="undefined"){h="GET"}var d=ploopi_gethttpobject();ploopi_sendxmldata(h,b,g,d,a);if(!a){if(f){return(d.responseXML)}else{return(d.responseText)}}}function ploopi_xmlhttprequest_tofunction(a,f,k,g,d,h){var b=ploopi_gethttpobject();if(typeof(d)=="undefined"){d=false}if(typeof(h)=="undefined"){h="GET"}if(b){b.onreadystatechange=function(){if(b.readyState==4){if(b.status==200){if(d){k(b.responseXML,g)}else{k(b.responseText,g)}}}}}return !ploopi_sendxmldata(h,a,f,b,true)}function ploopi_xmlhttprequest_todiv(a,b,f,d){if(typeof(d)=="undefined"){d="GET"}new Ajax.Request(a,{method:d,parameters:b,encoding:"iso-8859-15",onSuccess:function(g){ploopi_innerHTML(f,g.responseText)}})}function ploopi_xmlhttprequest_topopup(d,g,k,b,f,h,a){if(typeof(h)=="undefined"){h="GET"}if(typeof(a)=="undefined"){a=false}ploopi_showpopup(ploopi_ajaxloader_content,d,g,"click",k,null,null,a);ploopi_xmlhttprequest_todiv(b,f,k,h)}function ploopi_xmlhttprequest_submitform(a,f,d){var b=true;if(typeof(d)=="function"){b=d(a)}query=a.serialize();query+=(query==""?"":"&")+"ploopi_xhr=1";if(b){ploopi_xmlhttprequest_todiv(a.action,query,f,"POST")}};