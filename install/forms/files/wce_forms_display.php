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

$forms = new forms();
$forms->open($forms_id);

$pubdate_start = ($forms->fields['pubdate_start']) ? ploopi_timestamp2local($forms->fields['pubdate_start']) : array('date' => '');
$pubdate_end = ($forms->fields['pubdate_end']) ? ploopi_timestamp2local($forms->fields['pubdate_end']) : array('date' => '');


ob_start();
?>
function form_validate(form)
{
    <?
    $sql =  "
            SELECT  *
            FROM    ploopi_mod_forms_field
            WHERE   id_form = {$forms_id}
            ORDER BY position
            ";

    $db->query($sql);

    while ($fields = $db->fetchrow())
    {

        switch ($fields['type'])
        {
            case 'text':
                if (isset($field_formats[$fields['format']])) // chp de type 'text' et ayant un format existant
                {
                    switch($fields['format'])
                    {
                        case 'string':
                            if ($fields['option_needed']) $format = 'string';
                            else $format = 'emptystring';
                        break;

                        case 'date':
                            if ($fields['option_needed']) $format = 'date';
                            else $format = 'emptydate';
                        break;

                        case 'time':
                            if ($fields['option_needed']) $format = 'time';
                            else $format = 'emptytime';
                        break;

                        case 'integer':
                            if ($fields['option_needed']) $format = 'int';
                            else $format = 'emptyint';
                        break;

                        case 'float':
                            if ($fields['option_needed']) $format = 'float';
                            else $format = 'emptyfloat';
                        break;

                        case 'email':
                            if ($fields['option_needed']) $format = 'email';
                            else $format = 'emptyemail';
                        break;

                        default:
                            $format = '';
                        break;
                    }
                    ?>
                    if (ploopi_validatefield('<? echo addslashes($fields['name']); ?>', form.field_<? echo $fields['id']; ?>, '<? echo $format; ?>'))
                    <?
                }
            break;

            case 'select':
            case 'color':
                if ($fields['option_needed'])
                {
                    ?>
                    if (ploopi_validatefield('<? echo addslashes($fields['name']); ?>', form.field_<? echo $fields['id']; ?>, 'selected'))
                    <?
                }
            break;

            case 'radio':
            case 'checkbox':
                if ($fields['option_needed'])
                {
                    ?>
                    if (ploopi_validatefield('<? echo addslashes($fields['name']); ?>', form.elements['field_<? echo $fields['id']; ?>[]'], 'checked'))
                    <?
                }
            break;
        }
    }
    ?>
        return(true);

    return(false);
}

var result = form_validate(this);

<?
$jsfunc = preg_replace("/(\r\n|\n|\r|\t)+/", ' ', ob_get_contents());
ob_end_clean();
?>


<script type="text/javascript">
    form_validate = "<? echo $jsfunc; ?>";
</script>


<?
$replies = array(); //réponses déjà saisies

//if ($forms->fields['option_onlyone'] || isset($reply_id))
if (isset($reply_id))
{

    if (isset($reply_id)) $where = " AND id = {$reply_id}";
    else $where = '';

    $select =   "
                SELECT  *
                FROM    ploopi_mod_forms_reply
                WHERE   id_form = $forms_id
                $where
                AND     id_user = {$_SESSION['ploopi']['userid']}
                AND     id_workspace = {$_SESSION['ploopi']['workspaceid']}
                AND     id_module = {$_SESSION['ploopi']['moduleid']}
                ";

    $db->query($select);
    if ($fields = $db->fetchrow())
    {
        $reply_id = $fields['id'];
        ?>
        <input type="hidden" name="reply_id" value="<? echo $fields['id']; ?>">
        <?
        $select =   "
                    SELECT  f.id, IFNULL(rf.value, f.defaultvalue) as value
                    FROM    ploopi_mod_forms_field f
                    LEFT JOIN ploopi_mod_forms_reply_field rf
                    ON      rf.id_field = f.id
                    AND     rf.id_form = f.id_form
                    AND     rf.id_reply = {$fields['id']}
                    WHERE   f.id_form = $forms_id
                    ";
        $db->query($select);
        while ($fields = $db->fetchrow())
        {
            $replies[$fields['id']] = explode('||',$fields['value']);
        }
    }
}
else
{
    $select =   "
                SELECT  f.id, f.defaultvalue as value
                FROM    ploopi_mod_forms_field f
                WHERE   f.id_form = $forms_id
                ";
    $db->query($select);
    while ($fields = $db->fetchrow())
    {
        $replies[$fields['id']] = explode('||',$fields['value']);
    }
}

foreach($replies as $key =>$values)
{
    switch($values[0])
    {
        case '=date()':
            $localdate = ploopi_timestamp2local(ploopi_createtimestamp());
            $values[0] = $localdate['date'];
        break;

        case '=time()':
            $localdate = ploopi_timestamp2local(ploopi_createtimestamp());
            $values[0] = $localdate['time'];
        break;
    }
    $replies[$key] = $values;
}

$sql =  "
        SELECT  *
        FROM    ploopi_mod_forms_field
        WHERE   id_form = {$forms_id}
        ORDER BY position
        ";

$rs_fields = $db->query($sql);


//include './modules/forms/cms_forms_model_application.php';
$template_forms = new Template("./templates/frontoffice/$template_name");
if (file_exists("./templates/frontoffice/{$template_name}/forms.tpl"))
{
    global $template_body;

    $template_forms->set_filenames(array('forms_display' => "forms.tpl"));
    include './modules/forms/wce_forms_render.php';
    $template_forms->pparse('forms_display');
}
else echo "ERREUR : template forms.tpl manquant !";
?>


