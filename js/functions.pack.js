function ploopi_annotation(A){ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=annotation&id_annotation="+A,"ploopiannotation_"+A)}var tag_timer;var tag_search;var tag_results=new Array();var tag_last_array=new Array();var tag_new_array=new Array();var tag_lastedit="";var tag_modified=-1;function ploopi_annotation_tag_init(A){$("ploopi_annotationtags_"+A).onkeyup=ploopi_annotation_tag_keyup;$("ploopi_annotationtags_"+A).onkeypress=ploopi_annotation_tag_keypress}function ploopi_annotation_tag_search(B,A){clearTimeout(tag_timer);tag_search=A;tag_timer=setTimeout("ploopi_annotation_tag_searchtimeout('"+B+"')",100)}function ploopi_annotation_tag_searchtimeout(A){list_tags=tag_search.split(" ");if(list_tags.length>0){ploopi_xmlhttprequest_tofunction("index-quick.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=annotation_searchtags&tag="+list_tags[list_tags.length-1],ploopi_annotation_tag_display,A)}}function ploopi_annotation_tag_display(A,B){if(A!=""){tag_results=new Array();splited_result=A.split("|");tagstoprint="";for(i=0;i<splited_result.length;i++){detail=splited_result[i].split(";");if(tagstoprint!=""){tagstoprint+=" "}if(i==0){tagstoprint+="<b>"}tagstoprint+="<a href=\"javascript:ploopi_annotation_tag_complete('"+B+"',"+i+')">'+detail[0]+"</a> ("+detail[1]+")";if(i==0){tagstoprint+="</b>"}tag_results[i]=detail[0]}$("tagsfound_"+B).innerHTML=tagstoprint}else{$("tagsfound_"+B).innerHTML="";tag_results=new Array()}}function ploopi_annotation_tag_prevent(A){if(window.event){window.event.returnValue=false}else{A.preventDefault()}}function ploopi_annotation_tag_keypress(A){A=A||window.event;src=(A.srcElement)?A.srcElement:A.target;switch(A.keyCode){case 38:case 40:prevent(A);break;case 9:ploopi_annotation_tag_prevent(A);break;case 13:ploopi_annotation_tag_prevent(A);break;default:tag_lastedit=$(src.id).value;break}}function ploopi_annotation_tag_keyup(A){A=A||window.event;src=(A.srcElement)?A.srcElement:A.target;idrecord=src.id.split("_")[2];switch(A.keyCode){case 38:case 40:prevent(A);break;case 9:ploopi_annotation_tag_complete(idrecord);ploopi_annotation_tag_prevent(A);break;case 13:ploopi_annotation_tag_complete(idrecord);ploopi_annotation_tag_prevent(A);break;case 35:case 36:case 39:case 37:break;default:tag_last_array=new Array();tag_new_array=new Array();tag_last_array=tag_lastedit.split(" ");tag_new_array=$(src.id).value.split(" ");tag_modified=-1;for(i=0;i<tag_new_array.length;i++){if(tag_new_array[i]!=tag_last_array[i]){if(tag_modified==-1){tag_modified=i}else{tag_modified=-2}}}if(tag_modified>=0){ploopi_annotation_tag_search(idrecord,tag_new_array[tag_modified])}break}}function ploopi_annotation_tag_complete(B,A){if(!(A>=0)){A=0}if(tag_results[A]){tag_new_array[tag_modified]=tag_results[A];taglist="";for(i=0;i<tag_new_array.length;i++){if(taglist!=""){taglist+=" "}taglist+=tag_new_array[i]}$("ploopi_annotationtags_"+B).value=taglist.replace(/(^\s*)|(\s*$)/g,"")+" ";$("tagsfound_"+B).innerHTML=""}tag_results=new Array()}function ploopi_annotation_delete(A,B){if(confirm("�tes vous certain de vouloir supprimer cette annotation ?")){ploopi_xmlhttprequest("index-quick.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=annotation_delete&ploopi_annotation_id="+B)}ploopi_annotation(A)}function ploopi_annotation_validate(A){if(ploopi_validatefield("Titre",A.ploopi_annotationtags,"string")){return true}return false}function ploopi_calendar_open(A,B){ploopi_showpopup(ploopi_xmlhttprequest("index-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=calendar_open&selected_date="+$(A).value+"&inputfield_id="+A),192,B,false,"ploopi_popup_calendar")}function $S(A){A=$(A);if(A){return(A.style)}}function abPos(B){var B=(typeof (B)=="object"?B:$(B)),A={X:0,Y:0};while(B!=null){A.X+=B.offsetLeft;A.Y+=B.offsetTop;B=B.offsetParent}return(A)}function agent(A){return(Math.max(navigator.userAgent.toLowerCase().indexOf(A),0))}function toggle(A){$S(A).display=($S(A).display=="none"?"block":"none")}function within(B,A,C){return((B>=A&&B<=C)?true:false)}function XY(B,A){var C=agent("msie")?[event.clientX+document.body.scrollLeft,event.clientY+document.body.scrollTop]:[B.pageX,B.pageY];return(C[zero(A)])}function zero(A){A=parseInt(A);return(!isNaN(A)?A:0)}var maxValue={H:360,S:100,V:100},HSV={H:360,S:100,V:100};var slideHSV={H:360,S:100,V:100},zINDEX=15,stop=1;function HSVslide(K,B,J){function M(R){N=XY(R,1)-P.Y;O=XY(R)-P.X}function L(S,R,T){return(Math.min(S,Math.max(0,Math.ceil((parseInt(T)/R)*S))))}function G(S,R){if(within(S,0,R)){return(S)}else{if(S>R){return(R)}else{if(S<0){return("-"+H)}}}}function D(U){if(!stop){if(K!="drag"){M(U)}if(K=="SVslide"){C.left=G(O-H,162)+"px";C.top=G(N-H,162)+"px";slideHSV.S=L(100,162,C.left);slideHSV.V=100-L(100,162,C.top);HSVupdate()}else{if(K=="Hslide"){var R=G(N-H,163),T="HSV",V={};C.top=(R)+"px";slideHSV.H=L(360,163,R);for(var S in T){S=T.substr(S,1);V[S]=(S=="H")?maxValue[S]-L(maxValue[S],163,R):HSV[S]}HSVupdate(V);$S("SV").backgroundColor="#"+color.HSV_HEX({H:HSV.H,S:100,V:100})}else{if(K=="drag"){C.left=XY(U)+I-A+"px";C.top=XY(U,1)+E-Q+"px"}}}}}if(stop){stop="";var C=$S(K!="drag"?K:B);if(K=="drag"){var I=parseInt(C.left),E=parseInt(C.top),A=XY(J),Q=XY(J,1);$S(B).zIndex=zINDEX++}else{var P=abPos($(B)),O,N,H=(K=="Hslide")?2:4;P.X+=10;P.Y+=22;if(K=="SVslide"){slideHSV.H=HSV.H}}document.onmousemove=D;document.onmouseup=function(){stop=1;document.onmousemove="";document.onmouseup=""};D(J)}}function HSVupdate(A){A=color.HSV_HEX(HSV=A?A:slideHSV);$S("plugCUR").background="#"+A;$("colorpicker_inputcolor").value="#"+A;return(A)}function loadSV(){var B="";for(var A=165;A>=0;A--){B+='<div style="background: #'+color.HSV_HEX({H:Math.round((360/165)*A),S:100,V:100})+';"><br /></div>'}$("Hmodel").innerHTML=B}color={};color.cords=function(B){var C=B/2,A=(hsv.H/360)*(Math.PI*2),D=(hsv.S+(100-hsv.V))/100*(C/2);$S("mCur").left=Math.round(Math.abs(Math.round(Math.sin(A)*D)+C+3))+"px";$S("mCur").top=Math.round(Math.abs(Math.round(Math.cos(A)*D)-C-21))+"px"};color.HEX=function(A){A=Math.round(Math.min(Math.max(0,A),255));return("0123456789ABCDEF".charAt((A-A%16)/16)+"0123456789ABCDEF".charAt(A%16))};color.RGB_HEX=function(B){var A=color.HEX;return(A(B.R)+A(B.G)+A(B.B))};color.HEX_RGB=function(A){return({R:parseInt(A.substring(1,3),16),G:parseInt(A.substring(3,5),16),B:parseInt(A.substring(5,7),16),A:1})};color.HSV_RGB=function(I){var M,O,J,E,D,L=I.S/100,K=I.V/100,N=I.H/360;if(L>0){if(N>=1){N=0}N=6*N;F=N-Math.floor(N);J=Math.round(255*K*(1-L));E=Math.round(255*K*(1-(L*F)));D=Math.round(255*K*(1-(L*(1-F))));K=Math.round(255*K);switch(Math.floor(N)){case 0:M=K;O=D;E=J;break;case 1:M=E;O=K;E=J;break;case 2:M=J;O=K;E=D;break;case 3:M=J;O=E;E=K;break;case 4:M=D;O=J;E=K;break;case 5:M=K;O=J;E=E;break}return({R:M?M:0,G:O?O:0,B:E?E:0,A:1})}else{return({R:(K=Math.round(K*255)),G:K,B:K,A:1})}};color.RGB_HSV=function(D){var G=Math.max(D.R,D.G,D.B),E=G-Math.min(D.R,D.G,D.B),C,B,A;if(G!=0){B=Math.round(E/G*100);if(D.R==G){C=(D.G-D.B)/E}else{if(D.G==G){C=2+(D.B-D.R)/E}else{if(D.B==G){C=4+(D.R-D.G)/E}}}var C=Math.min(Math.round(C*60),360);if(C<0){C+=360}}return({H:C?C:0,S:B?B:0,V:Math.round((G/255)*100)})};color.HSV_HEX=function(A){return(color.RGB_HEX(color.HSV_RGB(A)))};color.HEX_HSV=function(A){return(color.RGB_HSV(color.HEX_RGB(A)))};function ploopi_colorpicker_open(A,C){if($(A).value==""){$(A).value="#ffffff"}ploopi_showpopup(ploopi_xmlhttprequest("admin-light.php","ploopi_op=colorpicker_open&inputfield_id="+A+"&colorpicker_value="+escape($(A).value)),220,C,false,"popup_colorpicker");loadSV();var B=color.HEX_HSV($(A).value);HSVupdate(B);$S("SV").backgroundColor="#"+color.HSV_HEX({H:B.H,S:100,V:100});$S("SVslide").left=(Math.ceil((B.S*165)/100)-4)+"px";$S("SVslide").top=(165-Math.ceil((B.V*165)/100)-4)+"px";$S("Hslide").top=(165-Math.ceil((B.H*165)/360)-2)+"px"}function ploopi_documents_openfolder(C,B,A){ploopi_showpopup("",300,A,"click","ploopi_documents_openfolder_popup");ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=documents_openfolder&currentfolder="+C+"&documentsfolder_id="+B,"","ploopi_documents_openfolder_popup")}function ploopi_documents_openfile(C,B,A){ploopi_showpopup("",380,A,"click","ploopi_documents_openfile_popup");ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=documents_openfile&currentfolder="+C+"&documentsfile_id="+B,"","ploopi_documents_openfile_popup")}function ploopi_documents_deletefile(B,C,A){ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=documents_deletefile&currentfolder="+B+"&documentsfile_id="+A,"","ploopidocuments_"+C)}function ploopi_documents_deletefolder(B,C,A){ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=documents_deletefolder&currentfolder="+B+"&documentsfolder_id="+A,"","ploopidocuments_"+C)}function ploopi_documents_browser(G,D,E,C,A){if(typeof (D)=="undefined"){D=""}if(typeof (A)=="undefined"){A=false}if(typeof (C)=="undefined"){C=""}if(typeof (E)=="undefined"){E=""}var B=(C!="")?"&orderby="+C:"";if(A){ploopi_ajaxloader("ploopidocuments_"+G);ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=documents_browser&mode="+E+"&currentfolder="+D+B,"","ploopidocuments_"+G)}else{ploopi_innerHTML("ploopidocuments_"+G,ploopi_xmlhttprequest("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=documents_browser&mode="+E+"&currentfolder="+D+B))}}function ploopi_documents_validate(A){if(A.documentsfile_name){if(!ploopi_validatefield("Fichier",A.documentsfile_name,"string")){return false}}else{if(!ploopi_validatefield("Fichier",A.documentsfile_file,"string")){return false}}if(ploopi_validatefield("Libell�",A.documentsfile_label,"string")){return true}return false}function ploopi_documents_popup(D,C,G,A,B){var E=ploopi_base64_encode(G+"_"+D+"_"+ploopi_addslashes(C)+"_popup");ploopi_showpopup(""+ploopi_xmlhttprequest("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=documents_selectfile&id_object="+D+"&id_record="+C+"&documents_id="+E+"&destfield="+A)+"",600,B,"click","ploopi_documents_popup")}var ploopi_window_onload_functions=new Array();var ploopi_window_onunload_functions=new Array();function ploopi_window_onload_stock(A){ploopi_window_onload_functions[ploopi_window_onload_functions.length]=A}function ploopi_window_onload_launch(){window.onload=function(){for(var A=0;A<ploopi_window_onload_functions.length;A++){ploopi_window_onload_functions[A]()}}}function ploopi_window_onunload_stock(A){ploopi_window_onunload_functions[ploopi_window_onunload_functions.length]=A}function ploopi_window_onunload_launch(){window.onunload=function(){for(var A=0;A<ploopi_window_onunload_functions.length;A++){ploopi_window_onunload_functions[A]()}}}function ploopi_dispatch_onchange(A){if(Prototype.Browser.IE){$(A).fireEvent("onChange")}else{var B=document.createEvent("HTMLEvents");B.initEvent("change",false,false);$(A).dispatchEvent(B)}}ploopi_window_onload_launch();ploopi_window_onunload_launch();var ploopi_nbpopup=0;function ploopi_showpopup(popup_content,w,e,centered,id,pposx,pposy){var ploopi_popup;var active_effect=false;if(!id){id="ploopi_popup"}if(!$(id)){bodys=document.getElementsByTagName("body");ploopi_nbpopup++;ploopi_popup=document.createElement("div");ploopi_popup.setAttribute("class","ploopi_popup");ploopi_popup.setAttribute("className","ploopi_popup");ploopi_popup.setAttribute("id",id);ploopi_popup.setAttribute("style","z-index:"+(1000+ploopi_nbpopup)+";");ploopi_popup.style.display="none";bodys[0].appendChild(ploopi_popup);active_effect=true}else{ploopi_popup=$(id)}w=parseInt(w);if(!w){w=200}posx=0;posy=0;pposx=parseInt(pposx);pposy=parseInt(pposy);if(pposx){posx=pposx}if(pposy){posy=pposy}if(e){if(e.pageX||e.pageY){posx=e.pageX;posy=e.pageY}else{if(e.clientX||e.clientY){posx=e.clientX+document.body.scrollLeft;posy=e.clientY+document.body.scrollTop}}}else{switch(centered){case false:break;default:case true:var p_width=parseInt(document.body.offsetWidth);var p_left=parseInt(document.body.scrollLeft);var posx=(p_width/2)-(w/2)+p_left;break}}with(ploopi_popup.style){if(typeof (popup_content)!="undefined"){ploopi_innerHTML(id,popup_content)}tmpleft=parseInt(posx)+20;tmptop=parseInt(posy);if(w>0){width=w+"px"}else{w=parseInt(ploopi_popup.offsetWidth)}if(20+w+parseInt(tmpleft)>parseInt(document.body.offsetWidth)){tmpleft=parseInt(tmpleft)-w-40}left=tmpleft+"px";top=tmptop+"px"}if(active_effect){new Effect.Appear(id,{duration:0.4,from:0,to:1})}}function ploopi_movepopup(id,e,pposx,pposy,popup_content){var ploopi_popup;if(!id){id="ploopi_popup"}ploopi_popup=$(id);posx=0;posy=0;pposx=parseInt(pposx);pposy=parseInt(pposy);if(pposx){posx=pposx}if(pposy){posy=pposy}if(e){if(e.pageX||e.pageY){posx=e.pageX;posy=e.pageY}else{if(e.clientX||e.clientY){posx=e.clientX+document.body.scrollLeft;posy=e.clientY+document.body.scrollTop}}}with(ploopi_popup.style){ploopi_innerHTML(id,popup_content);tmpleft=parseInt(posx)+20;tmptop=parseInt(posy);w=parseInt(ploopi_popup.offsetWidth);if(20+w+parseInt(tmpleft)>parseInt(document.body.offsetWidth)){tmpleft=parseInt(tmpleft)-w-40}left=tmpleft+"px";top=tmptop+"px"}}function ploopi_hidepopup(A){if(!A){A="ploopi_popup"}if($(A)){new Effect.Fade(A,{duration:0.3,afterFinish:function(){var B=document.getElementsByTagName("body");B[0].removeChild($(A))}})}}function ploopi_hideallpopups(){var C=document.getElementsByClassName("ploopi_popup");var B=document.getElementsByTagName("body");var A=C.length;for(var D=0;D<A;D++){B[0].removeChild(C[D])}}ploopi_skin_array_renderupdate_done=new Array();function ploopi_skin_array_renderupdate(A){greater=$("ploopi_explorer_values_inner_"+A).offsetHeight>$("ploopi_explorer_values_outer_"+A).offsetHeight;if(greater){if(typeof (ploopi_skin_array_renderupdate_done[A])=="undefined"){$("ploopi_explorer_title_"+A).innerHTML="<div style='float:right;width:16px;'>&nbsp;</div>"+$("ploopi_explorer_title_"+A).innerHTML;columns=$("ploopi_explorer_main_"+A).getElementsByClassName("ploopi_explorer_column");for(j=0;j<columns.length;j++){if(columns[j].style.right!=""){diff=(Prototype.Browser.IE)?22:16;columns[j].style.right=(parseInt(columns[j].style.right)+diff)+"px"}}ploopi_skin_array_renderupdate_done[A]=true}}if(Prototype.Browser.IE){columns=$("ploopi_explorer_main_"+A).getElementsByClassName("ploopi_explorer_column");for(j=0;j<columns.length;j++){columns[j].style.height=$("ploopi_explorer_main_"+A).offsetHeight+"px"}}}function ploopi_skin_treeview_shownode(C,B,A){if(typeof (A)=="undefined"){A="admin-light.php"}elt=$("t"+C);dest=$("n"+C);if(elt.src.indexOf("plus")!=-1){elt.src=elt.src.replace("plus","minus")}else{if(elt.src.indexOf("minus")!=-1){elt.src=elt.src.replace("minus","plus")}}if($(dest)){if($(dest).style.display=="none"){$(dest).style.display="block";if($(dest).innerHTML.length<20){ploopi_ajaxloader(dest);ploopi_xmlhttprequest_todiv(A,B,dest)}}else{$(dest).style.display="none"}}}function ploopi_skin_array_refresh(A,B){ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=ploopi_skin_array_refresh&array_id="+A+"&array_orderby="+B,"ploopi_explorer_main_"+A)}var keyStr="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";function ploopi_base64_encode(C){var A="";var K,I,G;var J,H,E,D;var B=0;do{K=C.charCodeAt(B++);I=C.charCodeAt(B++);G=C.charCodeAt(B++);J=K>>2;H=((K&3)<<4)|(I>>4);E=((I&15)<<2)|(G>>6);D=G&63;if(isNaN(I)){E=D=64}else{if(isNaN(G)){D=64}}A=A+keyStr.charAt(J)+keyStr.charAt(H)+keyStr.charAt(E)+keyStr.charAt(D)}while(B<C.length);return A}function ploopi_base64_decode(C){var A="";var K,I,G;var J,H,E,D;var B=0;C=C.replace(/[^A-Za-z0-9\+\/\=]/g,"");do{J=keyStr.indexOf(C.charAt(B++));H=keyStr.indexOf(C.charAt(B++));E=keyStr.indexOf(C.charAt(B++));D=keyStr.indexOf(C.charAt(B++));K=(J<<2)|(H>>4);I=((H&15)<<4)|(E>>2);G=((E&3)<<6)|D;A=A+String.fromCharCode(K);if(E!=64){A=A+String.fromCharCode(I)}if(D!=64){A=A+String.fromCharCode(G)}}while(B<C.length);return A}function ploopi_addslashes(A){A=String(A);A=A.replace(/\\/g,"\\\\");A=A.replace(/\'/g,"\\'");A=A.replace(/\"/g,'\\"');return(A)}function ploopi_subscription(B,A){if(typeof (A)=="undefined"){A=""}ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=subscription&ploopi_subscription_id="+B+"&next="+A,"ploopi_subscription_"+B)}function ploopi_subscription_checkaction(A,B){var C=(B==-1)?$("ploopi_subscription_unsubscribe"):$("ploopi_subscription_action_"+B);C.checked=!C.checked;if(B==-1&&C.checked){ploopi_checkall($("ploopi_form_subscription_"+A),"ploopi_subscription_action_",false,true)}if(B>-1&&$("ploopi_subscription_unsubscribe")&&$("ploopi_subscription_unsubscribe").checked){$("ploopi_subscription_unsubscribe").checked=false}if(B==0&&C.checked){ploopi_checkall($("ploopi_form_subscription_"+A),"ploopi_subscription_action_",true,true)}if(B>0&&!C.checked&&$("ploopi_subscription_action_0").checked){$("ploopi_subscription_action_0").checked=false}}function ploopi_tickets_new(E,H,D,B,A,C){var G="";if(B){G+="&ploopi_tickets_object_label="+B}if(H){G+="&ploopi_tickets_id_object="+H}if(D){G+="&ploopi_tickets_id_record="+D}if(C){G+="&ploopi_tickets_reload="+C}if(A){G+="&ploopi_tickets_id_user="+A}ploopi_showpopup("",550,E,"click","system_popupticket");ploopi_ajaxloader("system_popupticket");ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=tickets_new"+G,"system_popupticket")}function ploopi_tickets_refresh(G,E,C,B){var A=G;var D=false;if(typeof (C)=="undefined"){C=""}if(typeof (B)=="undefined"){B=""}new PeriodicalExecuter(function(H){new Ajax.Request("index-quick.php?ploopi_op=tickets_getnum",{method:"get",encoding:"iso-8859-15",onSuccess:function(L){var J=L.responseText.split(",");if(J.length==2){var I=parseInt(J[0],10);var K=parseInt(J[1],10);$("tpl_ploopi_tickets_new").innerHTML=C+I+B;if(K>A&&!D){ploopi_tickets_alert();D=true}A=K}}})},E)}function ploopi_tickets_alert(){ploopi_showpopup("",350,null,true,"popup_tickets_new_alert",0,200);ploopi_ajaxloader("popup_tickets_new_alert");ploopi_xmlhttprequest_todiv("admin-light.php","ploopi_env="+_PLOOPI_ENV+"&ploopi_op=tickets_alert","popup_tickets_new_alert")}function ploopi_openwin(D,B,E,C){var H=(screen.height-(E+60))/2;var G=(screen.width-B)/2;if(!C){C="ploopiwin"}var A=window.open(D,C,"top="+H+",left="+G+",width="+B+", height="+E+", status=no, menubar=no, toolbar=no, scrollbars=yes, resizable=yes, screenY=20, screenX=20");A.focus();return(A)}function ploopi_confirmform(B,A){if(confirm(A)){B.submit()}}function ploopi_confirmlink(B,A){if(confirm(A)){location.href=B}}function ploopi_switchdisplay(A){e=$(A);if(e){e.style.display=(e.style.display=="none")?"block":"none"}}function ploopi_checkbox_click(B,A){src=(B.srcElement)?B.srcElement:B.target;if(typeof (src.id)=="undefined"||src.id!=A){if(Prototype.Browser.IE){switch($(A).type){case"radio":$(A).checked=true;break;default:$(A).checked=!$(A).checked;break}$(A).fireEvent("onchange")}else{var B=document.createEvent("MouseEvents");B.initEvent("click",false,false);$(A).dispatchEvent(B)}}}function ploopi_checkall(G,B,H,C){var A=G.elements.length;var E=new RegExp(B,"g");if(!C){C=false}for(var D=0;D<A;D++){var I=G.elements[D];if(C){if(I.id.match(E)){I.checked=H}}else{if(I.name.match(E)){I.checked=H}}}}function ploopi_getelem(elem,obj){if(typeof (obj)!="object"){obj=document}return(obj.getElementById)?obj.getElementById(elem):eval("document.all['"+ploopi_addslashes(elem)+"']")}function ploopi_innerHTML(B,A){if($(B)){$(B).innerHTML=A;$(B).innerHTML.evalScripts()}}function ploopi_rgbcolor(H){this.ok=false;if(H.charAt(0)=="#"){H=H.substr(1,6)}H=H.replace(/ /g,"");H=H.toLowerCase();var A={aliceblue:"f0f8ff",antiquewhite:"faebd7",aqua:"00ffff",aquamarine:"7fffd4",azure:"f0ffff",beige:"f5f5dc",bisque:"ffe4c4",black:"000000",noir:"000000",blanchedalmond:"ffebcd",blue:"0000ff",bleu:"0000ff",blueviolet:"8a2be2",brown:"a52a2a",burlywood:"deb887",cadetblue:"5f9ea0",chartreuse:"7fff00",chocolate:"d2691e",coral:"ff7f50",cornflowerblue:"6495ed",cornsilk:"fff8dc",crimson:"dc143c",cyan:"00ffff",darkblue:"00008b",darkcyan:"008b8b",darkgoldenrod:"b8860b",darkgray:"a9a9a9",darkgreen:"006400",darkkhaki:"bdb76b",darkmagenta:"8b008b",darkolivegreen:"556b2f",darkorange:"ff8c00",darkorchid:"9932cc",darkred:"8b0000",darksalmon:"e9967a",darkseagreen:"8fbc8f",darkslateblue:"483d8b",darkslategray:"2f4f4f",darkturquoise:"00ced1",darkviolet:"9400d3",deeppink:"ff1493",deepskyblue:"00bfff",dimgray:"696969",dodgerblue:"1e90ff",feldspar:"d19275",firebrick:"b22222",floralwhite:"fffaf0",forestgreen:"228b22",fuchsia:"ff00ff",gainsboro:"dcdcdc",ghostwhite:"f8f8ff",gold:"ffd700",goldenrod:"daa520",gray:"808080",gris:"808080",green:"008000",vert:"008000",greenyellow:"adff2f",honeydew:"f0fff0",hotpink:"ff69b4",indianred:"cd5c5c",indigo:"4b0082",ivory:"fffff0",khaki:"f0e68c",lavender:"e6e6fa",lavenderblush:"fff0f5",lawngreen:"7cfc00",lemonchiffon:"fffacd",lightblue:"add8e6",lightcoral:"f08080",lightcyan:"e0ffff",lightgoldenrodyellow:"fafad2",lightgrey:"d3d3d3",lightgreen:"90ee90",lightpink:"ffb6c1",lightsalmon:"ffa07a",lightseagreen:"20b2aa",lightskyblue:"87cefa",lightslateblue:"8470ff",lightslategray:"778899",lightsteelblue:"b0c4de",lightyellow:"ffffe0",lime:"00ff00",limegreen:"32cd32",linen:"faf0e6",magenta:"ff00ff",maroon:"800000",mediumaquamarine:"66cdaa",mediumblue:"0000cd",mediumorchid:"ba55d3",mediumpurple:"9370d8",mediumseagreen:"3cb371",mediumslateblue:"7b68ee",mediumspringgreen:"00fa9a",mediumturquoise:"48d1cc",mediumvioletred:"c71585",midnightblue:"191970",mintcream:"f5fffa",mistyrose:"ffe4e1",moccasin:"ffe4b5",navajowhite:"ffdead",navy:"000080",oldlace:"fdf5e6",olive:"808000",olivedrab:"6b8e23",orange:"ffa500",orangered:"ff4500",orchid:"da70d6",palegoldenrod:"eee8aa",palegreen:"98fb98",paleturquoise:"afeeee",palevioletred:"d87093",papayawhip:"ffefd5",peachpuff:"ffdab9",peru:"cd853f",pink:"ffc0cb",rose:"ffc0cb",plum:"dda0dd",powderblue:"b0e0e6",purple:"800080",red:"ff0000",rouge:"ff0000",rosybrown:"bc8f8f",royalblue:"4169e1",saddlebrown:"8b4513",salmon:"fa8072",sandybrown:"f4a460",seagreen:"2e8b57",seashell:"fff5ee",sienna:"a0522d",silver:"c0c0c0",skyblue:"87ceeb",slateblue:"6a5acd",slategray:"708090",snow:"fffafa",springgreen:"00ff7f",steelblue:"4682b4",tan:"d2b48c",teal:"008080",thistle:"d8bfd8",tomato:"ff6347",turquoise:"40e0d0",violet:"ee82ee",violetred:"d02090",wheat:"f5deb3",white:"ffffff",blanc:"ffffff",whitesmoke:"f5f5f5",yellow:"ffff00",jaune:"ffff00",yellowgreen:"9acd32"};for(var C in A){if(H==C){H=A[C]}}var I=[{re:/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,example:["rgb(123, 234, 45)","rgb(255,234,245)"],process:function(J){return[parseInt(J[1]),parseInt(J[2]),parseInt(J[3])]}},{re:/^(\w{2})(\w{2})(\w{2})$/,example:["#00ff00","336699"],process:function(J){return[parseInt(J[1],16),parseInt(J[2],16),parseInt(J[3],16)]}},{re:/^(\w{1})(\w{1})(\w{1})$/,example:["#fb0","f0f"],process:function(J){return[parseInt(J[1]+J[1],16),parseInt(J[2]+J[2],16),parseInt(J[3]+J[3],16)]}}];for(var B=0;B<I.length;B++){var E=I[B].re;var D=I[B].process;var G=E.exec(H);if(G){channels=D(G);this.r=channels[0];this.g=channels[1];this.b=channels[2];this.ok=true}}this.r=(this.r<0||isNaN(this.r))?0:((this.r>255)?255:this.r);this.g=(this.g<0||isNaN(this.g))?0:((this.g>255)?255:this.g);this.b=(this.b<0||isNaN(this.b))?0:((this.b>255)?255:this.b);this.toRGB=function(){return"rgb("+this.r+", "+this.g+", "+this.b+")"};this.toHex=function(){var L=this.r.toString(16);var K=this.g.toString(16);var J=this.b.toString(16);if(L.length==1){L="0"+L}if(K.length==1){K="0"+K}if(J.length==1){J="0"+J}return"#"+L+K+J};this.getHelpXML=function(){var L=new Array();for(var N=0;N<I.length;N++){var K=I[N].example;for(var M=0;M<K.length;M++){L[L.length]=K[M]}}for(var S in A){L[L.length]=S}var O=document.createElement("ul");O.setAttribute("id","rgbcolor-examples");for(var N=0;N<L.length;N++){try{var P=document.createElement("li");var R=new ploopi_rgbcolor(L[N]);var T=document.createElement("div");T.style.cssText="margin: 3px; border: 1px solid black; background:"+R.toHex()+"; color:"+R.toHex();T.appendChild(document.createTextNode("test"));var J=document.createTextNode(" "+L[N]+" -> "+R.toRGB()+" -> "+R.toHex());P.appendChild(T);P.appendChild(J);O.appendChild(P)}catch(Q){}}return O}}function ploopi_validatefield(field_label,field_object,field_type){var ok=true;var i;var nbpoint=0;var msg=new String();var reg=new RegExp("<FIELD_LABEL>","gi");if(field_object){field_value=field_object.value;if(field_type=="selected"){msg=lstmsg[9];ok=(field_object.selectedIndex>0&&field_object.value!="")}if(field_type=="checked"){msg=lstmsg[9];ok=false;for(c=0;c<field_object.length;c++){if(field_object[c].checked){ok=true}}}if(field_type=="phone"||field_type=="emptyphone"){ok=field_value.match(/^\+?(\([0-9 ]+\))?[0-9 ]+$/);if(field_type=="emptyphone"){ok=ok||field_value.length==0}if(!ok){msg=(field_type=="phone"&&field_value.length==0)?lstmsg[4]:lstmsg[11]}}if(field_type=="email"||field_type=="emptyemail"){ok=field_value.match(/^[a-z0-9._-]+@[a-z0-9.-]{2,}[.][a-z]{2,4}$/);if(field_type=="emptyemail"){ok=ok||field_value.length==0}if(!ok){msg=(field_type=="email"&&field_value.length==0)?lstmsg[4]:lstmsg[0]}}if(field_type=="color"){var color=new ploopi_rgbcolor(field_value);if(!color.ok){ok=false;msg=lstmsg[10]}}if(field_type=="string"){ok=(field_value.replace(/(^\s*)|(\s*$)/g,"").length>0);if(!ok){msg=lstmsg[4]}}if(field_type=="int"||field_type=="emptyint"){ok=field_value.match(/^(\-?[0-9]+)$/);if(field_type=="emptyint"){ok=ok||field_value.length==0}if(!ok){msg=(field_type=="int"&&field_value.length==0)?lstmsg[4]:lstmsg[5]}}if(field_type=="float"||field_type=="emptyfloat"){ok=field_value.match(/^(\-?((([0-9]+(\.)?)|([0-9]*\.[0-9]+))))$/);if(field_type=="emptyfloat"){ok=ok||field_value.length==0}if(!ok){msg=(field_type=="float"&&field_value.length==0)?lstmsg[4]:lstmsg[6]}}if(field_type=="date"||field_type=="emptydate"){ok=field_value.match(/^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$/);if(ok&&field_value.length>0){var date_split=field_value.split("/");var datetotest=new Date(eval(date_split[2]),eval(date_split[1])-1,eval(date_split[0]));var year=datetotest.getYear();if((Math.abs(year)+"").length<4){year=year+1900}ok=((datetotest.getDate()==eval(date_split[0]))&&(datetotest.getMonth()==eval(date_split[1])-1)&&(year==eval(date_split[2])))}if(field_type=="emptydate"){ok=ok||field_value.length==0}if(!ok){msg=(field_type=="date"&&field_value.length==0)?lstmsg[4]:lstmsg[7]}}if(field_type=="time"||field_type=="emptytime"){ok=field_value.match(/^[0-9]{2}[:]{1}[0-9]{2}([:]{1}[0-9]{2})?$/);if(field_type=="emptytime"){ok=ok||field_value.length==0}if(!ok){msg=(field_type=="time"&&field_value.length==0)?lstmsg[4]:lstmsg[8]}}}else{ok=false}if(!ok){alert(msg.replace(reg,field_label));if(field_type!="checked"){field_object.style.background=error_bgcolor;field_object.focus()}}return(ok)}var ploopi_ajaxloader_content='<div style="text-align:center;padding:40px 10px;"><img src="./img/ajax-loader.gif"></div>';function ploopi_ajaxloader(A){if(A&&$(A)){$(A).innerHTML=ploopi_ajaxloader_content}else{return ajaxloader}}function ploopi_gethttpobject(callback){var xmlhttp=false;
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
if(!xmlhttp&&typeof XMLHttpRequest!="undefined"){try{xmlhttp=new XMLHttpRequest()}catch(e){xmlhttp=false}}return xmlhttp}function ploopi_sendxmldata(E,B,D,C,A){if(!C){return false}if(E=="GET"){if(D=="null"){C.open("GET",B,A);C.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=ISO-8859-15")}else{C.open("GET",B+"?"+D,A);C.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=ISO-8859-15")}C.send(null)}else{if(E=="POST"){C.open("POST",B,A);C.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=ISO-8859-15");C.send(D)}}return true}function ploopi_xmlhttprequest(B,E,A,D,G){if(typeof (A)=="undefined"){A=false}if(typeof (D)=="undefined"){D=false}if(typeof (G)=="undefined"){G="GET"}var C=ploopi_gethttpobject();ploopi_sendxmldata(G,B,E,C,A);if(!A){if(D){return(C.responseXML)}else{return(C.responseText)}}}function ploopi_xmlhttprequest_tofunction(A,D,H,E,C,G){var B=ploopi_gethttpobject();if(typeof (C)=="undefined"){C=false}if(typeof (G)=="undefined"){G="GET"}if(B){B.onreadystatechange=function(){if(B.readyState==4){if(B.status==200){if(C){H(B.responseXML,E)}else{H(B.responseText,E)}}}}}return !ploopi_sendxmldata(G,A,D,B,true)}function ploopi_xmlhttprequest_todiv(A,C,E,D){var B=ploopi_gethttpobject();if(typeof (D)=="undefined"){D="GET"}if(B){B.onreadystatechange=function(){if(B.readyState==4){if(B.status==200){ploopi_innerHTML(E,B.responseText)}}}}return !ploopi_sendxmldata(D,A,C,B,true)};