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
define ("_FORMS_TAB_LIST", 0);
define ("_FORMS_TAB_ADD", 1);

define ("_FORMS_ACTION_CREATEFORM", 1);
define ("_FORMS_ACTION_ADDREPLY",   2);
define ("_FORMS_ACTION_EXPORT",     3);
define ("_FORMS_ACTION_FILTER",     4);
define ("_FORMS_ACTION_DELETE",     5);
define ("_FORMS_ACTION_BACKUP",     6);
define ("_FORMS_ACTION_ADMIN",     7);

global $field_types;
global $field_formats;
global $field_operators;
global $form_types;
global $form_modeles;

$field_types = array(   'text' => 'Texte Simple',
                        'textarea' => 'Texte Avancé',
                        'checkbox' => 'Case à Cocher',
                        'radio' => 'Boutons Radio',
                        'select' => 'Liste de Choix',
                        'tablelink' => 'Lien Formulaire',
                        'file' => 'Fichier',
                        'autoincrement' => 'Numéro Auto',
                        'color' => 'Palette de Couleur'
                    );

$field_formats = array( 'string' => 'Chaîne de caractères',
                        'integer' => 'Nombre Entier',
                        'float' => 'Nombre Réel',
                        'date' => 'Date',
                        'time' => 'Heure',
                        'email' => 'Email',
                        'url' => 'Adresse Internet'
                    );

$field_operators = array(   '=' => '=',
                            '>' => '>',
                            '<' => '<',
                            '>=' => '>=',
                            '<=' => '<=',
                            'like' => 'Contient',
                            'begin' => 'Commence par'
                        );

$form_types = array(    'cms' => 'Formulaire pour Gestion de Contenu',
                        'app' => 'Application PLOOPI'
                    );

$form_modeles = array(  'application' => 'Application',
                        'application2c' => 'Application 2 col.',
                        'application3c' => 'Application 3 col.',
                        'application4c' => 'Application 4 col.'
                    );

function forms_convertchars($content)
{
    $chars = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A",
                    "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
                    "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E",
                    "Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
                    "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N",
                    "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
                    "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U",
                    "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
                    "à" => "a", "á" => "a", "â" => "a", "ã" => "a",
                    "ä" => "a", "å" => "a", "æ" => "a", "ç" => "c",
                    "è" => "e", "é" => "e", "ê" => "e", "ë" => "e",
                    "ì" => "i", "í" => "i", "î" => "i", "ï" => "i",
                    "ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o",
                    "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
                    "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u",
                    "ý" => "y", "ÿ" => "y", "_" => " ", "-" => " ", "/" => " ");

    return(strtr($content, $chars));
}


function forms_createphysicalname($name)
{
    $chars = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A",
                    "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
                    "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E",
                    "Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
                    "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N",
                    "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
                    "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U",
                    "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
                    "à" => "a", "á" => "a", "â" => "a", "ã" => "a",
                    "ä" => "a", "å" => "a", "æ" => "a", "ç" => "c",
                    "è" => "e", "é" => "e", "ê" => "e", "ë" => "e",
                    "ì" => "i", "í" => "i", "î" => "i", "ï" => "i",
                    "ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o",
                    "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
                    "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u",
                    "ý" => "y", "ÿ" => "y", " " => "_", "-" => "_");

    $name = ereg_replace("([^[:alnum:]|_]+)", "", strtr(strtolower(trim($name)), $chars));
    if (strlen($name) && is_numeric($name{0})) $name  = "_$name";

    return(substr($name,0,32));
}


function forms_viewworkspaces($moduleid, $workspaceid, $viewmode)
{
    switch($viewmode)
    {
        default:
        case 'private':
            $workspaces = $workspaceid;
        break;

        case 'desc':
            $workspaces = $_SESSION['ploopi']['workspaces'][$workspaceid]['list_parents'];
            if ($workspaces!='') $workspaces.=',';
            $workspaces .= $workspaceid;
        break;

        case 'asc':
            $workspaces = $_SESSION['ploopi']['workspaces'][$workspaceid]['list_children'];
            if ($workspaces!='') $workspaces.=',';
            $workspaces .= $workspaceid;
        break;

        case 'global':
            $workspaces = $_SESSION['ploopi']['allworkspaces'].",''";
        break;
    }

    return $workspaces;
}

function forms_getfuid($id_form, $id_module, $id_object, $id_record)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    return base64_encode("{$id_form}_{$id_module}_{$id_object}_".addslashes($id_record));
}


function forms_display($id_form, $id_module, $id_object, $id_record, $rights = array(), $options = array())
{
    $fuid = forms_getfuid($id_form, $id_module, $id_object, $id_record);

    // le tableau rights peut contenir les valeurs suivantes :
    //
    // ['_FORMS_ACTION_ADDREPLY'] => 0/1
    // ['_FORMS_ACTION_EXPORT'] => 0/1
    // ['_FORMS_ACTION_DELETE'] => 0/1
    // ['_FORMS_ACTION_MODIFY'] => 0/1

    if (!isset($rights['_FORMS_ACTION_ADDREPLY'])) $rights['_FORMS_ACTION_ADDREPLY'] = 0;
    if (!isset($rights['_FORMS_ACTION_EXPORT'])) $rights['_FORMS_ACTION_EXPORT'] = 0;
    if (!isset($rights['_FORMS_ACTION_DELETE'])) $rights['_FORMS_ACTION_DELETE'] = 0;
    if (!isset($rights['_FORMS_ACTION_MODIFY'])) $rights['_FORMS_ACTION_MODIFY'] = 0;

    if (!isset($options['height'])) $options['height'] = 0;
    if (!isset($options['filter_mode'])) $options['filter_mode'] = 'default'; // or 'like'
    if (!isset($options['object_display'])) $options['object_display'] = false;
    if (!isset($options['object_label'])) $options['object_label'] = 'Objet Lié';
    if (!isset($options['object_values'])) $options['object_values'] = array();

    $_SESSION['forms'][$fuid]['id_form'] = $id_form;
    $_SESSION['forms'][$fuid]['id_object'] = $id_object;
    $_SESSION['forms'][$fuid]['id_record'] = $id_record;
    $_SESSION['forms'][$fuid]['id_module'] = $id_module;
    $_SESSION['forms'][$fuid]['rights'] = $rights;
    $_SESSION['forms'][$fuid]['options'] = $options;

    ?>
    <div id="form_<? echo $fuid; ?>"></div>
    <script type="text/javascript">
        ploopi_window_onload_stock(function () {forms_display('<? echo $fuid; ?>');});
    </script>
    <?
}
?>
