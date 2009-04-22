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

/**
 * Préparation à l'affichage d'un formulaire
 *
 * @package forms
 * @subpackage public
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * On récupère l'idenfiant de la réponse (s'il existe).
 */

$reply_id = (isset($_REQUEST['reply_id']) && is_numeric($_REQUEST['reply_id'])) ? $_REQUEST['reply_id'] : '';

/**
 * On récupère les dates de publication du formulaire
 */

$pubdate_start = ($forms->fields['pubdate_start']) ? ploopi_timestamp2local($forms->fields['pubdate_start']) : array('date' => '');
$pubdate_end = ($forms->fields['pubdate_end']) ? ploopi_timestamp2local($forms->fields['pubdate_end']) : array('date' => '');

/**
 * Petite astuce pour générer une chaîne contenant une fonction javascript créée dynamiquement.
 * Cette fonction va permettre de valider un formulaire.
 */

ob_start();
?>
function form_validate(form)
{
    <?php
    $sql =  "
            SELECT  *
            FROM    ploopi_mod_forms_field
            WHERE   id_form = {$forms->fields['id']}
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
                    if (ploopi_validatefield('<?php echo addslashes($fields['name']); ?>', form.field_<?php echo $fields['id']; ?>, '<?php echo $format; ?>'))
                    <?php
                }
            break;

            case 'file':
                if ($fields['option_needed'])
                {
                    ?>
                    if (ploopi_validatefield('<?php echo addslashes($fields['name']); ?>', form.field_<?php echo $fields['id']; ?>, 'string'))
                    <?php
                }
            break;

            case 'select':
            case 'color':
                if ($fields['option_needed'])
                {
                    ?>
                    if (ploopi_validatefield('<?php echo addslashes($fields['name']); ?>', form.field_<?php echo $fields['id']; ?>, 'selected'))
                    <?php
                }
            break;

            case 'radio':
            case 'checkbox':
                if ($fields['option_needed'])
                {
                    ?>
                    if (ploopi_validatefield('<?php echo addslashes($fields['name']); ?>', form.elements['field_<?php echo $fields['id']; ?>[]'], 'checked'))
                    <?php
                }
            break;
        }
    }
    ?>
        return(true);

    return(false);
}

var result = form_validate(this);

<?php
/**
 * La fonction est récupérée puis nettoyée (suppression \n \r \t),
 * puis on utilise une variable javascript pour la déclarer dans la page du client.
 */

$jsfunc = preg_replace("/(\r\n|\n|\r|\t)+/", ' ', ob_get_contents());
ob_end_clean();
?>


<script type="text/javascript">
    form_validate = "<?php echo $jsfunc; ?>";
</script>

<?php
/**
 * On va chercher (s'il existe) le contenu du formulaire.
 */

$replies = array(); //réponses déjà saisies

if (!empty($reply_id))
{
    $select =   "
                SELECT  ploopi_mod_forms_reply.*,
                        ploopi_user.firstname,
                        ploopi_user.lastname,
                        ploopi_user.login
                FROM    ploopi_mod_forms_reply
                LEFT JOIN   ploopi_user ON ploopi_mod_forms_reply.id_user = ploopi_user.id
                WHERE   ploopi_mod_forms_reply.id_form = {$forms->fields['id']}
                AND     ploopi_mod_forms_reply.id = {$reply_id}
                AND     id_module = {$_SESSION['ploopi']['moduleid']}
                ";

    $db->query($select);
    if ($fields = $db->fetchrow())
    {
        $reply_id = $fields['id'];
        $replies['id_user'] = $fields['id_user'];
        if (!is_null($fields['login'])) $replies['user_login'] = $fields['login'];
        else $replies['user_login'] = _FORMS_ANONYMOUS;

        $replies['ip'] = $fields['ip'];

        $select =   "
                    SELECT  f.id, IFNULL(rf.value, f.defaultvalue) as value
                    FROM    ploopi_mod_forms_field f
                    LEFT JOIN ploopi_mod_forms_reply_field rf
                    ON      rf.id_field = f.id
                    AND     rf.id_form = f.id_form
                    AND     rf.id_reply = {$fields['id']}
                    WHERE   f.id_form = {$forms->fields['id']}
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
                WHERE   f.id_form = {$forms->fields['id']}
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
        WHERE   id_form = {$forms->fields['id']}
        ORDER BY position
        ";

$rs_fields = $db->query($sql);

$template_name = (!empty($forms->fields['model']) && file_exists("./modules/forms/templates/{$forms->fields['model']}/index.tpl")) ? $forms->fields['model'] : 'default';
$template_forms = new Template("./modules/forms/templates/{$template_name}/");
if (file_exists("./modules/forms/templates/{$template_name}/index.tpl"))
{
    echo $skin->open_simplebloc("{$forms->fields['label']} (Mode Edition)");
    $template_forms->set_filenames(array('forms_display' => "index.tpl"));

    if ($op == 'forms_reply_add' || $op == 'forms_reply_modify')
    {
        $hiddenvars = array();
        $hiddenvars[] = array('name' => 'op', 'value' => 'forms_reply_save');
        $hiddenvars[] = array('name' => 'forms_id', 'value' => $forms->fields['id']);
        $hiddenvars[] = array('name' => 'forms_reply_id', 'value' => $reply_id);

        foreach($hiddenvars as $var)
        {
            $template_forms->assign_block_vars('formhiddenvars', array(
                        'NAME' => $var['name'],
                        'VALUE' => $var['value']
                        )
                    );
        }

        $template_forms->assign_vars(array(
                    'FORM_ACTION' => 'admin.php',
                    'FORM_TARGET' => ''
                    )
                );

        $template_forms->assign_block_vars('formbuttons', array(
                    'TYPE' => 'button',
                    'VALUE' => 'Retour',
                    'OPTION' => 'onclick="javascript:document.location.href=\''.ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$forms->fields['id']}").'\';"'
                    )
                );

        $template_forms->assign_block_vars('switch_formvalidation', array());

    }
    else
    {
        switch ($op)
        {
            case 'forms_preview':
                $template_forms->assign_block_vars('formbuttons', array(
                            'TYPE' => 'button',
                            'VALUE' => 'Retour',
                            'OPTION' => 'onclick="javascript:document.location.href=\''.ploopi_urlencode("admin.php?formsTabItem=formlist").'\';"'
                            )
                        );
            break;

            case 'forms_reply_display':
                $template_forms->assign_block_vars('formbuttons', array(
                            'TYPE' => 'button',
                            'VALUE' => 'Retour',
                            'OPTION' => 'onclick="javascript:document.location.href=\''.ploopi_urlencode("admin.php?op=forms_viewreplies&forms_id={$forms->fields['id']}").'\';"'
                            )
                        );
            break;
        }
    }

    include './modules/forms/public_forms_display_render.php';
    $template_forms->pparse('forms_display');
    echo $skin->close_simplebloc();
}
else echo "ERREUR : template &laquo; {$template_name} &raquo; manquant !";
?>


