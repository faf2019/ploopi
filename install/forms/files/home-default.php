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
ploopi_init_module('forms');
$workspaces = ploopi_viewworkspaces($home_moduleid);
$sqllimitgroup = " AND ploopi_mod_forms_form.id_workspace IN ($workspaces)";

$date_today = ploopi_createtimestamp();

$forms_select =     "
            SELECT      *
            FROM        ploopi_mod_forms_form 
            WHERE       id_module = {$home_moduleid}
            $sqllimitgroup
            AND         (pubdate_start <= '$date_today' OR pubdate_start = '')
            AND         (pubdate_end >= '$date_today' OR pubdate_end = '')
            ";


$forms_result = $db->query($forms_select);

$first = true;

while ($forms_fields = $db->fetchrow($forms_result))
{
    $pubdate_start = ($forms_fields['pubdate_start']) ? ploopi_timestamp2local($forms_fields['pubdate_start']) : array('date' => '');
    $pubdate_end = ($forms_fields['pubdate_end']) ? ploopi_timestamp2local($forms_fields['pubdate_end']) : array('date' => '');

    if (!$first)
    {
        ?>
        <table cellpadding="0" cellspacing="0" bgcolor="<? echo $skin->values['colsec']; ?>" width="100%"><tr><td height="1"></td></tr></table>
        <?
    }
    if ($first) $first = false;
    ?>
    <table cellpadding="2" cellspacing="1">
        <tr><td><a style="font-size:18px;font-weight:bold;" href="<? echo "$scriptenv?ploopi_moduleid=$menu_moduleid&ploopi_action=public&op=viewlist&forms_id={$forms_fields['id']}"; ?>"><? echo $forms_fields['label']; ?></a></td></tr>
        <tr><td><? echo nl2br($forms_fields['description']); ?></h2></td></tr>
        <tr>
            <td>
            <strong>» <a href="<? echo "$scriptenv?ploopi_moduleid=$menu_moduleid&ploopi_action=public&op=viewlist&forms_id={$forms_fields['id']}"; ?>"><? echo _FORMS_FILL; ?></a></strong>
            </td>
        </tr>
    </table>
    <?
}
?>