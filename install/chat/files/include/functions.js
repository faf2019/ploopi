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

var chat_last_msg_id = -1;   // id du dernier message reçu, permet de ne demander que les messages non reçus
var chat_pe_refresh = null; // timer pour le rafraichissement auto
var chat_pe_refresh_ts = 2; // tps en seconde entre chaque rafraichissement
var chat_msg_sending = false; // à vrai si envoi d'un msg en cours

/* 
 * Rafraichissement auto du chat au chargement de la page (onload)
 *
 * */

function chat_refresh_onload()
{
    chat_refresh();
    chat_pe_refresh = new PeriodicalExecuter(function(pe) { chat_refresh(); }, chat_pe_refresh_ts);
}

/* 
 * Rafraichissement auto du chat
 * On charge de manière périodique les nouvelles données depuis le serveur.
 * Les données sont au format JSON.
 * On récupère les derniers messages et la liste des utilisateurs à jour.
 * 
 * */

function chat_refresh()
{
    new Ajax.Request('index-quick.php',
        {
            method:     'post',
            parameters: {ploopi_op: 'chat_refresh', 'chat_last_msg_id': chat_last_msg_id},
            encoding:   'iso-8859-15',
            onSuccess:  function(transport, json) 
                        {
                            if(null == json) json = transport.responseText.evalJSON();
                            if (json)
                            {
                                if (json['connected'])
                                {
                                    // Suppression de la liste des utilisateurs connectés
                                    // On recherche tous les éléments en fonction du sélecteur css
                                    $$('#chat_userbox_list div.chat_userprofile').each(function(item) {
                                        $('chat_userbox_list').removeChild(item);  
                                    });
                                    
                                    // Construction de la liste des utilisateurs connectés
                                    // On parcourt l'objet "connected" qui est un tableau
                                    $A(json['connected']).each(function(item) {
                                        d = document.createElement('div');
                                        if (Prototype.Browser.IE) d.setAttribute('className', 'chat_userprofile');
                                        else d.setAttribute('class', 'chat_userprofile');
                                        d.innerHTML = item['login'];
                                
                                        $('chat_userbox_list').appendChild(d);
                                    });
                                }

                                if (json['msg'])
                                {
                                    /*$$('#chat_msgbox div.chat_msg').each(function(item) {
                                        $('chat_msgbox').removeChild(item);  
                                    });*/
                                    
                                    // Construction de la liste des messages
                                    // On parcourt l'objet "msg" qui est un tableau
                                    $A(json['msg']).each(function(item) {
                                        
                                        hh = item['timestp'].substring(8,10);
                                        mm = item['timestp'].substring(10,12);
                                        ss = item['timestp'].substring(12,14);
                                        
                                        d = document.createElement('div');
                                        if (Prototype.Browser.IE) d.setAttribute('className', 'chat_msg');
                                        else d.setAttribute('class', 'chat_msg');
                                        $('chat_msgbox').appendChild(d);
                                        
                                        d_ts = document.createElement('span');
                                        if (Prototype.Browser.IE) d_ts.setAttribute('className', 'chat_msg_ts');
                                        else d_ts.setAttribute('class', 'chat_msg_ts');
                                        d_ts.innerHTML = hh+':'+mm+':'+ss
                                        d.appendChild(d_ts);
                                        
                                        d_user = document.createElement('span');
                                        if (Prototype.Browser.IE) d_user.setAttribute('className', 'chat_msg_user');
                                        else d_user.setAttribute('class', 'chat_msg_user');
                                        d_user.innerHTML = item['login'];
                                        d.appendChild(d_user);

                                        d_content = document.createElement('span');
                                        if (Prototype.Browser.IE) d_content.setAttribute('className', 'chat_msg_content');
                                        else d_content.setAttribute('class', 'chat_msg_content');
                                        d_content.innerHTML = item['content'];
                                        d.appendChild(d_content);
                                    });

                                    $('chat_msgbox').scrollTop=$('chat_msgbox').scrollHeight; 
                                }
 
                                if (json['lastmsgid'])
                                {
                               	    chat_last_msg_id = json['lastmsgid'];
                                }
                             }
                        },
            onException: function(xhr, e)
            {
                //alert(e);
            }
        }
    );
} 

function chat_msg_send()
{
    
    if (!chat_msg_sending && $('chat_msg').value != '')
    {
    	chat_msg_sending = true;
	    chat_pe_refresh.stop();
	    
	    new Ajax.Request('index-quick.php',
	        {
	            method:     'post',
	            parameters: {ploopi_op: 'chat_msg_send', chat_msg: $('chat_msg').value},
	            encoding:   'iso-8859-15',
	            onSuccess:  function(transport) 
	            {
	            	$('chat_msg').value = '';
	            	$('chat_msg').focus
	                chat_refresh();
	                chat_pe_refresh = new PeriodicalExecuter(function(pe) { chat_refresh(); }, chat_pe_refresh_ts);
	                chat_msg_sending = false;
	            },
	            onException: function(xhr, e)
				{
	                chat_refresh();
	                chat_pe_refresh = new PeriodicalExecuter(function(pe) { chat_refresh(); }, chat_pe_refresh_ts);
	                chat_msg_sending = false;
				    //alert(e);
				}
	        }
	    );
    }
}