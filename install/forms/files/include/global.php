<?php
/*
    Copyright (c) 2002-2007 Netlor
    Copyright (c) 2007-2010 Ovensia
    Copyright (c) 2010 HeXad
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
 * Fonctions, constantes, variables globales
 *
 * @package forms
 * @subpackage global
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Définition des constantes
 */

/**
 * Action : Gérer les formulaires
 */
define ("_FORMS_ACTION_CREATEFORM", 1);

/**
 * Action : Ajouter une réponse dans un formulaire
 */
define ("_FORMS_ACTION_ADDREPLY",   2);

/**
 * Action : Exporter les données d'un formulaire
 */
define ("_FORMS_ACTION_EXPORT",     3);

/**
 * Action : Filtrer les données d'un formulaire
 */
define ("_FORMS_ACTION_FILTER",     4);

/**
 * Action : Supprimer des enregistrements d'un formulaire
 */
define ("_FORMS_ACTION_DELETE",     5);

/**
 * Action : Gérer l'archivage des données d'un formulaire
 */
define ("_FORMS_ACTION_BACKUP",     6);

/**
 * Action : Afficher les graphiques
 */
define ("_FORMS_ACTION_GRAPHICS",     7);

/**
 * Action : Administer les formulaire
 */
define ("_FORMS_ACTION_ADMIN",     99);



/**
 * Définition des variables globales
 */

/**
 * Types de champs (text, textarea, select, etc...)
 */
global $field_types;

/**
 * Formats de champs (string, integer, date, etc...)
 */
global $field_formats;

/**
 * Opérateurs sur les champs (=, >, <, etc...)
 */
global $field_operators;

/**
 * Types de formulaire (cms, app)
 */
global $form_types;

/**
 * Types de graphique
 */
global $forms_graphic_types;

/**
 * Types d'aggregation
 */
global $forms_graphic_line_aggregation;

/**
 * Types d'opération
 */
global $forms_graphic_operation;


$field_types = array(
    'text' => 'Texte Simple',
    'textarea' => 'Texte Avancé',
    'checkbox' => 'Case à Cocher',
    'radio' => 'Boutons Radio',
    'select' => 'Liste de Choix',
    'tablelink' => 'Lien Formulaire',
    'file' => 'Fichier',
    'autoincrement' => 'Numéro Auto',
    'color' => 'Palette de Couleur',
    'calculation' => 'Calcul'
);

$field_formats = array(
    'string' => 'Chaîne de caractères',
    'integer' => 'Nombre Entier',
    'float' => 'Nombre Réel',
    'date' => 'Date',
    'time' => 'Heure',
    'email' => 'Email',
    'url' => 'Adresse Internet'
);

$field_operators = array(
    '=' => '=',
    '>' => '>',
    '<' => '<',
    '>=' => '>=',
    '<=' => '<=',
    'between' => 'Entre',
    'like' => 'Contient',
    'begin' => 'Commence par',
    'in' => 'Dans la liste de valeurs'
);

$form_types = array(
    'cms' => 'Formulaire pour Gestion de Contenu',
    'app' => 'Application PLOOPI'
);

$forms_graphic_types = array(
    'line' => 'Courbes',
    'linec' => 'Courbes cumulées',
    'bar' => 'Histogrammes',
    'barc' => 'Histogrammes cumulés',
    'radar' => 'Radars',
    'radarc' => 'Radars cumulés',
    'pie' => 'Secteurs',
    'pie3d' => 'Secteurs 3D'
);

$forms_graphic_line_aggregation = array(
    'hour' => 'Heure',
    'day' => 'Journée',
    'week' => 'Semaine (inactif)',
    'month' => 'Mois'
);

$forms_graphic_operation = array(
    'avg' => 'Moyenne',
    'count' => 'Nombre',
    'sum' => 'Somme'
);

/**
 * Crée le nom physique de la table en fonction du nom du formulaire
 *
 * @param string $name nom du formulaire
 * @return string nom de la table
 */

function forms_createphysicalname($name)
{
    /**
     * Conversion des caractères non alphanum , conversion en minuscule, suppression des accents, suppression des espaces inutiles
     */

    $name = preg_replace("/([^[:alnum:]|_]+)/", "_", ploopi_convertaccents(strtolower(trim($name))));

    /**
     * Suppression des '_' en trop
     */

    $patterns = array('/__+/', '/_$/');
    $replacements = array('_', '');

    $name = preg_replace($patterns, $replacements, $name);

    if (strlen($name) && is_numeric($name{0})) $name  = "_{$name}";

    return(substr($name, 0, 32));
}

/**
 * Retourne une liste d'identifiants d'espaces de travail.
 * Application de la vue aux données d'un formulaire.
 *
 * @param int $moduleid identifiant du module
 * @param int $workspaceid identifiant de l'espace de travail
 * @param string $viewmode vue ('private, 'desc', 'asc', 'global')
 * @return string liste d'identifiants d'espaces séparés par une virgule
 */

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
            $workspaces = $_SESSION['ploopi']['allworkspaces'].",0";
        break;
    }

    return $workspaces;
}

/**
 * Génère l'identifiant unique du bloc formulaire appelé par un enregistrement d'un objet
 *
 * @param int $id_form identifiant du formulaire
 * @param int $id_module identifiant du module appelant
 * @param int $id_object identifiant de l'objet appelant
 * @param string $id_record identifiant de l'enregistrement appelant
 * @return string identifiant du bloc en md5
 *
 * @see md5
 */

function forms_getfuid($id_form, $id_module, $id_object, $id_record)
{
    if ($id_module == -1) $id_module = $_SESSION['ploopi']['moduleid'];

    return md5("{$id_form}_{$id_module}_{$id_object}_".addslashes($id_record));
}


/**
 * Affiche un formulaire depuis un autre module
 *
 * @param int $id_form identifiant du formulaire
 * @param int $id_module identifiant du module appelant
 * @param int $id_object identifiant de l'objet appelant
 * @param string $id_record identifiant de l'enregistrement appelant
 * @param array $rights tableau des droits  (_FORMS_ACTION_ADDREPLY, _FORMS_ACTION_EXPORT, _FORMS_ACTION_DELETE, _FORMS_ACTION_MODIFY)
 * @param array $options tableau des options (height:int, filter_mode:string, object_display:boolean, object_label:string, object_values:array)
 *
 * @see forms_getfuid
 */

function forms_display($id_form, $id_module, $id_object, $id_record, $rights = array(), $options = array())
{
    $forms_fuid = forms_getfuid($id_form, $id_module, $id_object, $id_record);

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

    $_SESSION['forms'][$forms_fuid]['id_form'] = $id_form;
    $_SESSION['forms'][$forms_fuid]['id_object'] = $id_object;
    $_SESSION['forms'][$forms_fuid]['id_record'] = $id_record;
    $_SESSION['forms'][$forms_fuid]['id_module'] = $id_module;
    $_SESSION['forms'][$forms_fuid]['rights'] = $rights;
    $_SESSION['forms'][$forms_fuid]['options'] = $options;

    ?>
    <div id="form_<?php echo $forms_fuid; ?>"></div>
    <script type="text/javascript">
        ploopi_window_onload_stock(function () {forms_display('<?php echo $forms_fuid; ?>');});
    </script>
    <?php
}

/**
 * Retourne les données d'un formulaire dans un tableau (appel depuis un enregistrement d'un objet externe)
 *
 * @param int $id_form identifiant du formulaire
 * @param int $id_module identifiant du module appelant
 * @param int $id_object identifiant de l'objet appelant
 * @param string $id_record identifiant de l'enregistrement appelant
 * @param array $options tableau des options (filter_mode:string, object_display:boolean, object_label:string, object_values:array)
 *
 * @return array tableau contenant le titre et les données
 */

function forms_getdata($id_form, $id_module, $id_object, $id_record, $options = array())
{
    include_once './modules/forms/classes/formsForm.php';

    global $db;

    $forms_fuid = forms_getfuid($id_form, $id_module, $id_object, $id_record);

    $_SESSION['forms'][$forms_fuid]['id_form'] = $id_form;
    $_SESSION['forms'][$forms_fuid]['id_object'] = $id_object;
    $_SESSION['forms'][$forms_fuid]['id_record'] = $id_record;
    $_SESSION['forms'][$forms_fuid]['id_module'] = $id_module;

    if (!isset($options['filter_mode'])) $options['filter_mode'] = 'default'; // or 'like'
    if (!isset($options['object_display'])) $options['object_display'] = false;
    if (!isset($options['object_label'])) $options['object_label'] = 'Objet Lié';
    if (!isset($options['object_values'])) $options['object_values'] = array();

    $_SESSION['forms'][$forms_fuid]['options'] = $options;

    include './modules/forms/op_preparedata.php';

    return(array($data_title, $data));
}

function forms_gradient($HexFrom, $HexTo, $ColorSteps)
{
    if (substr($HexFrom, 0, 1) == '#') $HexFrom = substr($HexFrom, 1, strlen($HexFrom) - 1);
    if (substr($HexTo, 0, 1) == '#') $HexTo = substr($HexTo, 1, strlen($HexTo) - 1);


    $FromRGB['r'] = hexdec(substr($HexFrom, 0, 2));
    $FromRGB['g'] = hexdec(substr($HexFrom, 2, 2));
    $FromRGB['b'] = hexdec(substr($HexFrom, 4, 2));

    $ToRGB['r'] = hexdec(substr($HexTo, 0, 2));
    $ToRGB['g'] = hexdec(substr($HexTo, 2, 2));
    $ToRGB['b'] = hexdec(substr($HexTo, 4, 2));

    $StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps - 1);
    $StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps - 1);
    $StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps - 1);

    $GradientColors = array();

    for($i = 0; $i <= $ColorSteps; $i++)
    {
            $RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
            $RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
            $RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));

            $HexRGB['r'] = sprintf('%02x', ($RGB['r']));
            $HexRGB['g'] = sprintf('%02x', ($RGB['g']));
            $HexRGB['b'] = sprintf('%02x', ($RGB['b']));

            $GradientColors[] = implode(NULL, $HexRGB);
    }

    foreach($GradientColors as &$Color) $Color = "#{$Color}";

    return $GradientColors;
}

function id_captcha($id_form)
{
    return md5('form_captcha_'.$id_form);
}
?>
