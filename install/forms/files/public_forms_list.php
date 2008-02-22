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

echo $skin->open_simplebloc(_FORMS_LIST);

$date_today = ploopi_createtimestamp();

$select =   "
            SELECT      *
            FROM        ploopi_mod_forms_form 
            WHERE       id_module = {$_SESSION['ploopi']['moduleid']}
            $sqllimitgroup
                    AND         (pubdate_start <= '$date_today' OR pubdate_start = '')
                    AND         (pubdate_end >= '$date_today' OR pubdate_end = '')
            ";


$db->query($select);


$first = true;

while ($fields = $db->fetchrow())
{
    $pubdate_start = ($fields['pubdate_start']) ? ploopi_timestamp2local($fields['pubdate_start']) : array('date' => '');
    $pubdate_end = ($fields['pubdate_end']) ? ploopi_timestamp2local($fields['pubdate_end']) : array('date' => '');

    if (!$first)
    {
        ?>
        <table cellpadding="0" cellspacing="0" bgcolor="<? echo $skin->values['colsec']; ?>" width="100%"><tr><td height="1"></td></tr></table>
        <?
    }
    if ($first) $first = false;
    ?>
    <table cellpadding="2" cellspacing="1">
        <tr><td><a style="font-size:18px;font-weight:bold;" href="<? echo "{$scriptenv}?op=forms_viewreplies&forms_id={$fields['id']}"; ?>"><? echo $fields['label']; ?></a></td></tr>
        <tr><td><? echo nl2br($fields['description']); ?></td></tr>
        <tr>
            <td>
            <strong>» <a href="<? echo "$scriptenv?op=forms_viewreplies&forms_id={$fields['id']}"; ?>"><? echo _FORMS_FILL; ?></a></strong>
            </td>
        </tr>
    </table>
    <?
}
?>


<?
echo $skin->close_simplebloc();
?>
