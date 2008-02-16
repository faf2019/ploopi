<?php
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
?>
<?

//search all module_type
$select =   "
        SELECT id
        FROM ploopi_module_type 
        ";
    
$result = $db->query($select);

$list_moduletype = '';
while ($fields = $db->fetchrow($result))
{
    if ($list_moduletype != '') $list_moduletype .= ',';
    $list_moduletype .= $fields['id'];
}

echo "<BR>list_moduletype : $list_moduletype";

// clean unused modules
$delete =   "
        DELETE FROM ploopi_module
        WHERE id_module_type NOT IN ($list_moduletype)
        ";
$db->query($delete);

// clean unused actions
$delete =   "
        DELETE FROM ploopi_mb_action
        WHERE id_module_type NOT IN ($list_moduletype)
        ";
$db->query($delete);

// search all param_type
$select =   "
        SELECT id
        FROM ploopi_param_type
        WHERE id_module_type IN ($list_moduletype)
        ";
echo "<BR>$select"; 
$result = $db->query($select);

$list_paramtype = '';
while ($fields = $db->fetchrow($result))
{
    if ($list_paramtype != '') $list_paramtype .= ',';
    $list_paramtype .= $fields['id'];
}

echo "<BR>list_paramtype : $list_paramtype";

// clean unused param_user
$delete =   "
        DELETE FROM ploopi_param_user
        WHERE id_param_type NOT IN ($list_paramtype)
        ";
$db->query($delete);

// clean unused param_choice
$delete =   "
        DELETE FROM ploopi_param_choice
        WHERE id_param_type NOT IN ($list_paramtype)
        ";
//echo "<BR>$delete";
$db->query($delete);

// clean unused param_default
$delete =   "
        DELETE FROM ploopi_param_default
        WHERE id_param_type NOT IN ($list_paramtype)
        ";
//echo "<BR>$delete";
$db->query($delete);


// search avalaible users
$select =   "
            SELECT  distinct id_user
            FROM    ploopi_group_user,
                    ploopi_group
            WHERE   ploopi_group.id = ploopi_group_user.id_group
            ";
    
$result = $db->query($select);

$list_user = '';
while ($fields = $db->fetchrow($result))
{
    if ($list_user != '') $list_user .= ',';
    $list_user .= $fields['id_user'];
}
echo "<BR>list_user : $list_user";

// clean unused users
$delete =   "
            DELETE FROM ploopi_user
            WHERE id NOT IN ($list_user)
            ";
echo "<BR>$delete";
$db->query($delete);

// clean unused workspace_user_role
$delete =   "
            DELETE FROM ploopi_workspace_user_role
            WHERE id_user NOT IN ($list_user)
            ";
echo "<BR>$delete";
$db->query($delete);

// clean unused param_user
$delete =   "
            DELETE FROM ploopi_param_user
            WHERE id_user NOT IN ($list_user)
            ";
            
echo "<BR>$delete";
$db->query($delete);

$select =   "
        SELECT id
        FROM ploopi_user 
        ";
    
$result = $db->query($select);

$list_user = '';
while ($fields = $db->fetchrow($result))
{
    if ($list_user != '') $list_user .= ',';
    $list_user .= $fields['id'];
}
echo "<BR>list_user : $list_user";

$delete =   "
        DELETE FROM ploopi_workspace_user
        WHERE id_user NOT IN ($list_user)
        ";
//echo "<BR>$delete";
$db->query($delete);

$delete =   "
        DELETE FROM ploopi_workspace_user_role
        WHERE id_user NOT IN ($list_user)
        ";
//echo "<BR>$delete";
$db->query($delete);

$select =   "
        SELECT id
        FROM ploopi_module
        ";
    
$result = $db->query($select);

$list_module = '';
while ($fields = $db->fetchrow($result))
{
    if ($list_module != '') $list_module .= ',';
    $list_module .= $fields['id'];
}
echo "<BR>list_module : $list_module";

$select =   "
        SELECT id
        FROM ploopi_role
        WHERE id_module IN ($list_module)
        ";
    
$result = $db->query($select);

$list_role = '';
while ($fields = $db->fetchrow($result))
{
    if ($list_role != '') $list_role .= ',';
    $list_role .= $fields['id'];
}
echo "<BR>list_role : $list_role";

$delete =   "
        DELETE FROM ploopi_role
        WHERE id_module NOT IN ($list_module)
        ";
//echo "<BR>$delete";
$db->query($delete);

if ($list_role == '') $list_role = '-1';
$delete =   "
        DELETE FROM ploopi_role_action
        WHERE id_role NOT IN ($list_role)
        ";
//echo "<BR>$delete";
$db->query($delete);
?>
