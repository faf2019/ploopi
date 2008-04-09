<?php
$sql =  "
        SELECT      u.*
        FROM        ploopi_workspace_user_role wur
        INNER JOIN  ploopi_user u
        ON          u.id = wur.id_user
        WHERE       wur.id_role = {$roleid}
        AND         wur.id_workspace = {$_SESSION['system']['workspaceid']}
        ORDER BY    u.lastname, u.firstname
        ";

$db->query($sql);
$users = $db->getarray();

$sql =  "
        SELECT      g.*
        FROM        ploopi_workspace_group_role wgr
        INNER JOIN  ploopi_group g
        ON          g.id = wgr.id_group
        WHERE       wgr.id_role = {$roleid}
        AND         wgr.id_workspace = {$_SESSION['system']['workspaceid']}
        ORDER BY    g.label
        ";

$db->query($sql);
$groups = $db->getarray();



if (empty($groups) && empty($users))
{
    ?>
    <div style="padding:4px;font-weight:bold;">Aucun utilisateur ou groupe affecté à ce rôle, utilisez la recherche pour en ajouter</div>
    <?
    }
    else
    {
        
        $columns = array();
        $values = array();
        $c = 0;
        
        $columns['left']['type']    = array('label' => 'Type', 'width' => '120', 'options' => array('sort' => true));
        $columns['auto']['name']    = array('label' => 'Nom', 'options' => array('sort' => true));
        $columns['actions_right']['actions'] = array('label' => '&nbsp;', 'width' => '24');    
    
    
        
        foreach($groups as $group)
        {
            $values[$c]['values']['type']   = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_group.png\"><span>&nbsp;Groupe</span>");
            $values[$c]['values']['name']   = array('label' => $group['label']);
            $values[$c]['values']['actions']    = array('label' => '<a href="javascript:if (confirm(\''._SYSTEM_MSG_CONFIRMGROUPDETACH.'\')) system_roleusers_delete('.$roleid.', '.$group['id'].', \'group\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png" alt="'._SYSTEM_LABEL_DELETE.'"></a>');
            $c++;
        }
    
        foreach($users as $user)
        {
            $values[$c]['values']['type']   = array('label' => "<img src=\"{$_SESSION['ploopi']['template_path']}/img/system/ico_user.png\"><span>&nbsp;Utilisateur</span>");
            $values[$c]['values']['name']   = array('label' => "{$user['firstname']} {$user['lastname']} ({$user['login']})");
            $values[$c]['values']['actions']    = array('label' => '<a href="javascript:if (confirm(\''._SYSTEM_MSG_CONFIRMUSERDETACH.'\')) system_roleusers_delete('.$roleid.', '.$user['id'].', \'user\');"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png" alt="'._SYSTEM_LABEL_DELETE.'"></a>');
            $c++;
        }
    
        $skin->display_array($columns, $values, 'array_roles_users', array('sortable' => true, 'orderby_default' => 'type'));
    }

?>