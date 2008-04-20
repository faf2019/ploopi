<?php
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

// on verifie qu'on est bien dans le module CHAT
if (ploopi_ismoduleallowed('chat'))
{
    switch($ploopi_op)
    {
        case 'chat_refresh':
            include_once './include/functions/date.php';
            ploopi_init_module('chat', false, false, false);
            
            $arrData = array('connected' => array(), 'msg' => array(), 'lastmsgid' => 0);
            
            // mise à jour de la liste des utilisateurs connectés
            chat_connected_update();
            
            // on récupère la liste des utilisateurs connectés
            $sql =  "
                    SELECT      cc.*, 
                                u.login,
                                u.firstname,
                                u.lastname
                    
                    FROM        ploopi_mod_chat_connected cc
                    
                    INNER JOIN  ploopi_user u
                    ON          u.id = cc.id_user
                    
                    WHERE       cc.id_module = {$_SESSION['ploopi']['moduleid']}
                    
                    ORDER BY    cc.connection_timestp ASC
                    ";
            
            $db->query($sql);
            
            $arrData['connected'] = $db->getarray();
            
            if (isset($_POST['chat_last_msg_id']) && is_numeric($_POST['chat_last_msg_id']) && $_POST['chat_last_msg_id'] > 0)
            {
                // on récupère la liste des nouveaux messages
                $sql =  "
                        SELECT      cm.*, 
                                    u.login,
                                    u.firstname,
                                    u.lastname
                        
                        FROM        ploopi_mod_chat_msg cm
                        
                        INNER JOIN  ploopi_user u
                        ON          u.id = cm.id_user
                        
                        WHERE       cm.id_module = {$_SESSION['ploopi']['moduleid']}
                        AND         cm.id > {$_POST['chat_last_msg_id']}
                        
                        ORDER BY    cm.timestp ASC
                        ";
                
                $rs = $db->query($sql);
                
                $arrData['msg'] = $db->getarray($rs);
                
                // on recule d'une position sur le recordset pour récupérer le dernier id de message
                if ($db->numrows()) $db->dataseek($rs, $db->numrows()-1);
                
                $arrData['lastmsgid'] = ($row = $db->fetchrow()) ? $row['id'] : 0;
            }
            else // on récupère juste l'id du dernier message (l'utilisateur vient de se connecter)
            {
                $sql =  "
                        SELECT      max(cm.id) as lastmsgid
                        FROM        ploopi_mod_chat_msg cm
                        WHERE       cm.id_module = {$_SESSION['ploopi']['moduleid']}
                        ";
                
                $db->query($sql);
                $arrData['lastmsgid'] = ($row = $db->fetchrow()) ? $row['lastmsgid'] : 0;
            }
            
            header("Content-Type: text/x-json; charset=utf-8"); 
            echo json_encode($arrData);
            
             
           ploopi_die();
        break;
        
        case 'chat_msg_send':
            include_once './include/functions/date.php';
            ploopi_init_module('chat', false, false, false);
                    
            include_once './modules/chat/classes/class_chat_msg.php';
            
            if (!empty($_POST['chat_msg']))
            {
                // enregistrement du message
                $chat_msg = new chat_msg();
                $chat_msg->save($_POST['chat_msg']);
            }
                    
            ploopi_die();
        break;
    }
    
}
?>