<?php
/*
    Copyright (c) 2008-2012 Ovensia
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
 * Affichage des utilisateurs
 *
 * @package system
 * @subpackage system
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Ouverture du bloc
 */

echo $skin->open_simplebloc(_SYSTEM_LABEL_DIRECTORY);
$intMaxResponse = 500;

$arrFilter = array();

// On ne veut pas les caractères % et | dans la recherche avec LIKE
$pattern = '/%|_/';

// Lecture SESSION
if (isset($_SESSION['system']['directoryform']) && !isset($_GET['system_filter_reset'])) $arrFilter = $_SESSION['system']['directoryform'];

// Lecture Params
if (isset($_POST['ploopi_lastname']) && !preg_match($pattern, $_POST['ploopi_lastname'])) $arrFilter['ploopi_lastname'] = $_POST['ploopi_lastname'];
if (isset($_POST['ploopi_firstname']) && !preg_match($pattern, $_POST['ploopi_firstname'])) $arrFilter['ploopi_firstname'] = $_POST['ploopi_firstname'];
if (isset($_POST['ploopi_login']) && !preg_match($pattern, $_POST['ploopi_login'])) $arrFilter['ploopi_login'] = $_POST['ploopi_login'];
if (isset($_POST['ploopi_group']) && !preg_match($pattern, $_POST['ploopi_group'])) $arrFilter['ploopi_group'] = $_POST['ploopi_group'];
if (isset($_POST['ploopi_workspace']) && !preg_match($pattern, $_POST['ploopi_workspace'])) $arrFilter['ploopi_workspace'] = $_POST['ploopi_workspace'];
if (isset($_POST['ploopi_email']) && !preg_match($pattern, $_POST['ploopi_email'])) $arrFilter['ploopi_email'] = $_POST['ploopi_email'];
if (isset($_POST['ploopi_last_connection_1'])) $arrFilter['ploopi_last_connection_1'] = $_POST['ploopi_last_connection_1'];
if (isset($_POST['ploopi_last_connection_2'])) $arrFilter['ploopi_last_connection_2'] = $_POST['ploopi_last_connection_2'];

// Affectation de valeurs par défaut si non défini
if (!isset($arrFilter['ploopi_lastname'])) $arrFilter['ploopi_lastname'] = '';
if (!isset($arrFilter['ploopi_firstname'])) $arrFilter['ploopi_firstname'] = '';
if (!isset($arrFilter['ploopi_login'])) $arrFilter['ploopi_login'] = '';
if (!isset($arrFilter['ploopi_group'])) $arrFilter['ploopi_group'] = '';
if (!isset($arrFilter['ploopi_workspace'])) $arrFilter['ploopi_workspace'] = '';
if (!isset($arrFilter['ploopi_email'])) $arrFilter['ploopi_email'] = '';
if (!isset($arrFilter['ploopi_last_connection_1'])) $arrFilter['ploopi_last_connection_1'] = '';
if (!isset($arrFilter['ploopi_last_connection_2'])) $arrFilter['ploopi_last_connection_2'] = '';

// Enregistrement SESSION
$_SESSION['system']['directoryform'] = $arrFilter;
?>
<form action="<?php echo ploopi_urlencode('admin.php?sysToolbarItem=directory'); ?>" method="post">
<div class="ploopi_va" style="padding:6px;">
    <label>Nom: </label>
    <input type="text" class="text" name="ploopi_lastname" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_lastname']); ?>" style="width:100px;" tabindex="100" />

    <label>Prénom: </label>
    <input type="text" class="text" name="ploopi_firstname" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_firstname']); ?>" style="width:100px;" tabindex="105" />

    <label>Login: </label>
    <input type="text" class="text" name="ploopi_login" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_login']); ?>" style="width:100px;" tabindex="110" />

    <label>Courriel: </label>
    <input type="text" class="text" name="ploopi_email" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_email']); ?>" style="width:150px;" tabindex="120" />

    <label>Groupe: </label>
    <input type="text" class="text" name="ploopi_group" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_group']); ?>" style="width:100px;" tabindex="115" />

    <label>Espace: </label>
    <input type="text" class="text" name="ploopi_workspace" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_workspace']); ?>" style="width:100px;" tabindex="115" />

    <label>Connexion entre le: </label>
    <input type="text" class="text" name="ploopi_last_connection_1" id="ploopi_last_connection_1" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_last_connection_1']); ?>" style="width:100px;" tabindex="116" />
    <? ploopi_open_calendar('ploopi_last_connection_1'); ?>
    <label>et le: </label>
    <input type="text" class="text" name="ploopi_last_connection_2" id="ploopi_last_connection_2" value="<?php echo ploopi_htmlentities($arrFilter['ploopi_last_connection_2']); ?>" style="width:100px;" tabindex="117" />
    <? ploopi_open_calendar('ploopi_last_connection_2'); ?>

    <input type="submit" class="button" value="Filtrer" tabindex="150" />
    <input type="submit" class="button" name="delete" value="Supprimer" style="color:#a60000" tabindex="155" onclick="if (!confirm('Attention vous allez supprimer définitivement les utilisateurs correspondant à ce filtre.\nContinuer ?')) return false;" />
    <input type="button" class="button" value="Réinitialiser" onclick="document.location.href='<?php echo ploopi_urlencode('admin.php?sysToolbarItem=directory&system_filter_reset'); ?>';" tabindex="160" />
</div>
</form>


<?php
include_once './include/classes/query.php';

// On charge un maximum de données dans des tableaux pour simplifier les recherches par la suite
// et pour résoudre des cas complexes (retrouver un utilisateur dans un espace attaché à un sous-groupe d'utilisateur dans un sous-espace de travail)

// ETAPE 1 : CHARGEMENT

// Chargement de tous les sous-espaces de travail de l'espace courant
$arrWorkspaces = array();
// Espaces selon le filtre de recherche
$arrFilteredWorkspaces = array();

$objQuery = new ploopi_query_select();
$objQuery->add_select('w.*');
$objQuery->add_from('ploopi_workspace w');
// ICI, on n'applique pas le filtre sur le nom pour pouvoir lire l'arbre complet (au cas ou l'espace ou le groupe que l'on cherche soit dans un sous-espace)

if ($_SESSION['system']['level'] == 'work') // Visualisation depuis un espace de travail, filtrage sur les sous-espaces
{
    $objWorkspace = new workspace();
    if ($objWorkspace->open($workspaceid))
    {
        $objQuery->add_where('(w.id = %d OR w.parents = %s OR w.parents LIKE %s)', array(
            $workspaceid,
            "{$objWorkspace->fields['parents']};{$objWorkspace->fields['id']}",
            "{$objWorkspace->fields['parents']};{$objWorkspace->fields['id']};%")
        );
    }
}

$objRs = $objQuery->execute();
while ($row = $objRs->fetchrow())
{
    $arrWorkspaces[$row['id']] = array(
        'desc' => $row, // Description de l'espace (nom, ...)
        'children' => array(), // Liste des espaces fils directs
        'family' => array(), // Liste des espaces fils indirects
        'groups_children' => array(), // Liste des groupes fils directs
        'groups_family' => array(), // Liste des groupes fils indirects
        'groups_included' => array() // Liste des groupes fils inclus (y compris en provenance des sous-espaces)
    );
}

// Chargement des ID de tous les sous-espaces de travail de l'espace courant en fonction du filtre de recherche par espace
if ($arrFilter['ploopi_workspace'] != '')
{
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('w.*');
    $objQuery->add_from('ploopi_workspace w');
    $objQuery->add_where('label LIKE %s', "%{$arrFilter['ploopi_workspace']}%");

    $objRs = $objQuery->execute();
    while ($row = $objRs->fetchrow()) $arrFilteredWorkspaces[$row['id']] = $row['id'];

    if (empty($arrFilteredWorkspaces)) $arrFilteredWorkspaces[-1] = -1;
}
else foreach($arrWorkspaces as $intIdWorkspace => $row) $arrFilteredWorkspaces[$intIdWorkspace] = $intIdWorkspace;

// Chargement de tous les groupes
$arrGroups = array();
// Groupes selon le filtre de recherche
$arrFilteredGroups = array();

$objQuery = new ploopi_query_select();
$objQuery->add_select('*');
$objQuery->add_from('ploopi_group');

$objRs = $objQuery->execute();
while ($row = $objRs->fetchrow()) $arrGroups[$row['id']] = array('desc' => $row, 'children' => array(), 'family' => array());

// Chargement de tous les groupes en fonction du filtre de recherche
if ($arrFilter['ploopi_group'] != '')
{
    // Chargement des groupes autorisés
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('id');
    $objQuery->add_from('ploopi_group');
    $objQuery->add_where('label LIKE %s', "%{$arrFilter['ploopi_group']}%");

    $objRs = $objQuery->execute();
    while ($row = $objRs->fetchrow()) $arrFilteredGroups[$row['id']] = $row['id'];
}
else foreach($arrGroups as $intIdGroup => $row) $arrFilteredGroups[$intIdGroup] = $intIdGroup;


// ETAPE 2 : TRAITEMENTS

// On construit pour chaque espace, la liste des sous-espaces (tous niveaux)
foreach($arrWorkspaces as $intId => $row)
{
    // Lien fils
    if (isset($arrWorkspaces[$row['desc']['id_workspace']])) $arrWorkspaces[$row['desc']['id_workspace']]['children'][$intId] = $intId;

    // Lien famille (fils, petits-fils)
    $arrParents = explode(';', $row['desc']['parents']);
    foreach($arrParents as $intIdParent)
    {
        if ($intIdParent > 1 && isset($arrWorkspaces[$intIdParent]))
        {
            $arrWorkspaces[$intIdParent]['family'][$intId] = $intId;
        }
    }
}

// On construit pour chaque groupe, la liste des sous-groupes (tous niveaux)
foreach($arrGroups as $intId => $row)
{
    // Lien fils
    if (isset($arrGroups[$row['desc']['id_group']])) $arrGroups[$row['desc']['id_group']]['children'][$intId] = $intId;

    // Lien famille (fils, petits-fils)
    $arrParents = explode(';', $row['desc']['parents']);
    foreach($arrParents as $intIdParent)
    {
        if ($intIdParent > 1 && isset($arrGroups[$intIdParent]))
        {
            $arrGroups[$intIdParent]['family'][$intId] = $intId;
        }
    }
}

// On construit pour chaque espace, la liste des sous-groupes directs et indirect
$objQuery = new ploopi_query_select();
$objQuery->add_select('wg.*');
$objQuery->add_from('ploopi_workspace_group wg');
if (!empty($arrFilteredWorkspaces) && $arrFilter['ploopi_workspace'] != '') $objQuery->add_where('wg.id_workspace IN (%e)', array($arrFilteredWorkspaces));

if ($_SESSION['system']['level'] == 'work') // Visualisation depuis un espace de travail, filtrage sur les sous-espaces
{
    $objWorkspace = new workspace();
    if ($objWorkspace->open($workspaceid))
    {
        $objQuery->add_from('ploopi_workspace w');
        $objQuery->add_where('wg.id_workspace = w.id');
        $objQuery->add_where('(w.id = %d OR w.parents = %s OR w.parents LIKE %s)', array(
            $workspaceid,
            "{$objWorkspace->fields['parents']};{$objWorkspace->fields['id']}",
            "{$objWorkspace->fields['parents']};{$objWorkspace->fields['id']};%")
        );
    }
}

$objRs = $objQuery->execute();
while ($row = $objRs->fetchrow())
{
    //if ($row['id_workspace'] && isset($arrFilteredWorkspaces[$row['id_workspace']]) && isset($arrGroups[$row['id_group']]))
    if ($row['id_workspace'] && isset($arrGroups[$row['id_group']]))
    {
        $arrWorkspaces[$row['id_workspace']]['groups_children'][$row['id_group']] = $row['id_group'];

        $arrWorkspaces[$row['id_workspace']]['groups_family'][$row['id_group']] = $row['id_group'];

        foreach($arrGroups[$row['id_group']]['family'] as $intId) $arrWorkspaces[$row['id_workspace']]['groups_family'][$intId] = $intId;

    }
}

// On construit pour chaque espace, la liste des sous-groupes inclus (partie 2, héritage des sous-espaces)
// PAS OPTIMISE ! IL FAUDRAIT PARTIR DES BRANCHES ET REMONTER, ça reste acceptable.
foreach($arrWorkspaces as $intId => $row)
{
    $arrWorkspaces[$intId]['groups_included'] = $arrWorkspaces[$intId]['groups_family'];

    $arrParents = explode(';', $row['desc']['parents']);
    foreach($arrParents as $intIdParent)
    {
        if ($intIdParent > 1 && isset($arrWorkspaces[$intIdParent]))
        {
            foreach($row['groups_family'] as $intIdGroup)
            {
                $arrWorkspaces[$intIdParent]['groups_included'][$intIdGroup] = $intIdGroup;
            }
        }
    }
}

// On cherche :
// * Les utilisateurs inclus dans l'espace sélectionné, c'est à dire :
//   Tous les utilisateurs de l'espace courant et des sous-espaces, en incluant les rattachements de groupes et sous-groupes (et les utilisateurs rattachés en direct)
// * Il faut donc utiliser la propriété "groups_included" de l'espace de travail qui contient tous les sous-groupes (directs, indirects, sous-espaces)
// * Il faut également vérifier l'appartenance aux groupes/espaces filtrés (par le nom)


// Sélection des utilisateurs
$objQuery = new ploopi_query_select();
$objQuery->add_from('ploopi_user u');

// Filtrages basiques (utilisateur)
if ($arrFilter['ploopi_lastname'] != '') $objQuery->add_where('u.lastname LIKE %s', "%{$arrFilter['ploopi_lastname']}%");
if ($arrFilter['ploopi_firstname'] != '') $objQuery->add_where('u.firstname LIKE %s', "%{$arrFilter['ploopi_firstname']}%");
if ($arrFilter['ploopi_login'] != '') $objQuery->add_where('u.login LIKE %s', "%{$arrFilter['ploopi_login']}%");
if ($arrFilter['ploopi_email'] != '') $objQuery->add_where('u.email LIKE %s', "%{$arrFilter['ploopi_email']}%");
if ($arrFilter['ploopi_last_connection_1'] != '') $objQuery->add_where('u.last_connection >= %s', ploopi_local2timestamp($arrFilter['ploopi_last_connection_1'], '00:00:00'));
if ($arrFilter['ploopi_last_connection_2'] != '') $objQuery->add_where('u.last_connection <= %s', ploopi_local2timestamp($arrFilter['ploopi_last_connection_2'], '23:59:59'));

// Filtrage sur les espaces/groupes "visibles"
$objQuery->add_leftjoin('ploopi_workspace_user wu ON wu.id_user = u.id');
$objQuery->add_leftjoin('ploopi_group_user gu ON gu.id_user = u.id');

// Détermination des espaces/groupes à utiliser dans le filtre
$arrQueryGroups = array();
$arrQueryWorkspaces = array();

if ($_SESSION['system']['level'] == 'work')
{
    if (!empty($arrWorkspaces[$workspaceid]['family'])) $arrQueryWorkspaces = $arrWorkspaces[$workspaceid]['family'];

    if (!empty($arrWorkspaces[$workspaceid])) $arrQueryWorkspaces[$workspaceid] = $workspaceid;

    if (!empty($arrWorkspaces[$workspaceid]['groups_included'])) $arrQueryGroups = $arrWorkspaces[$workspaceid]['groups_included'];

    // Intersection entre les groupes possibles et les groupes filtrés
    if (!empty($arrQueryGroups) && !empty($arrFilteredGroups)) $arrQueryGroups = array_intersect($arrQueryGroups, $arrFilteredGroups);

    // Intersection entre les espaces possibles et les espaces filtrés
    if (!empty($arrQueryWorkspaces) && !empty($arrFilteredWorkspaces)) $arrQueryWorkspaces = array_intersect($arrQueryWorkspaces, $arrFilteredWorkspaces);
}
else
{
    //$arrQueryGroups = $arrFilteredGroups;
    $arrQueryWorkspaces = $arrFilteredWorkspaces;

    if ($arrFilter['ploopi_workspace'] != '')
    {
        $arrQueryGroups = array();
        foreach($arrFilteredWorkspaces as $intIdWorkspace)
        {
            $arrQueryGroups = array_merge($arrQueryGroups, $arrWorkspaces[$intIdWorkspace]['groups_family']);
        }
    }
    else $arrQueryGroups = $arrFilteredGroups;
}

//ploopi_print_r($arrWorkspaces[current($arrFilteredWorkspaces)]);

if (empty($arrQueryWorkspaces)) $arrQueryWorkspaces[] = -1;
if (empty($arrQueryGroups)) $arrQueryGroups[] = -1;

// Pas de filtre sur espace et groupe
if ($arrFilter['ploopi_workspace'] == '' && $arrFilter['ploopi_group'] == '')
{
    if ($_SESSION['system']['level'] == 'work')
    {
        $objQuery->add_where('(wu.id_workspace IN (%e) OR gu.id_group IN (%e))', array($arrQueryWorkspaces, $arrQueryGroups));
    }
}
else
{
    if ($arrFilter['ploopi_workspace'] != '' && $arrFilter['ploopi_group'] != '')
    {
        $objQuery->add_where('(wu.id_workspace IN (%e) AND gu.id_group IN (%e))', array($arrQueryWorkspaces, $arrQueryGroups));
    }
    else // Au moins un filtre sur espace ou groupe (les espaces sont filtrés en amont)
    {
        if ($arrFilter['ploopi_workspace'] != '')
        {
            $objQuery->add_where('(wu.id_workspace IN (%e) OR gu.id_group IN (%e))', array($arrQueryWorkspaces, $arrQueryGroups));
        }
        else // ($arrFilter['ploopi_group'] != '')
        {
            $objQuery->add_where('gu.id_group IN (%e)', array($arrQueryGroups));
        }
    }
}

$objQueryCount = clone $objQuery;

// On distingue la requête "Count" de la requête "Data"
$objQuery->add_select('DISTINCT u.id, u.*');
$objQuery->add_orderby('u.lastname, u.firstname');
$objQueryCount->add_select('COUNT(DISTINCT u.id) as c');

// Sauvegarde de la dernière requête SQL pour export
ploopi_setsessionvar('directory_sql', $objQuery->get_sql());

// Lecture du nombre de réponses
$intNbRep = current($objQueryCount->execute()->fetchrow());

if (isset($_REQUEST['delete'])) {

    // Exécution de la requête principale permettant de lister les utilisateurs selon le filtre
    $objRs = $objQuery->execute();
    while ($row = $objRs->fetchrow()) {
        $objUser = new user();
        if ($objUser->open($row['id']))
        {
            if ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_generate_htpasswd']) system_generate_htpasswd($objUser->fields['login'], '', true);
            ploopi_create_user_action_log(_SYSTEM_ACTION_DELETEUSER, "{$objUser->fields['login']} - {$objUser->fields['lastname']} {$objUser->fields['firstname']} (id:{$objUser->fields['id']})");
            $objUser->delete();
        }
    }

    ploopi_redirect('admin.php?sysToolbarItem=directory');
}

?>

<div style="padding:4px;background-color:#e0e0e0;border-bottom:1px solid #ccc;">
    <?
    if ($_SESSION['system']['level'] == 'system')
    {
        ?>
        <span>Vous pouvez retrouver ici l'ensemble des utilisateurs du sytème avec leur profil complet.<br />Vous ne pouvez cependant pas les gérer. Pour cela vous devez accéder à l'<a href="<?php echo ploopi_urlencode('admin.php?system_level=work'); ?>">interface d'administration des espaces de travail</a>.</span>
        <?
    }
    else
    {
        ?>
        <span>Vous pouvez retrouver ici l'ensemble des utilisateurs de l'espace de travail et des sous-espaces.</span>
        <?
    }
    ?>
    <?php
    if ($intNbRep > $intMaxResponse)
    {
        ?>
        <br /><strong class="error">Il y a <?php echo $intNbRep ?> réponses (<?php echo $intMaxResponse; ?> max), vous devriez préciser votre recherche ou exporter les données.</strong>
        <?
    }
    else
    {
        ?>
        <br /><strong><?php echo $intNbRep; ?> utilisateur(s) trouvé(s).</strong>
        <?
    }
    ?>

</div>


<div class="ploopi_tabs">
    <a href="<? echo ploopi_urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc=vcf"); ?>"><img src="./img/export/vcf.png"><span>vCard <sup>VCF</sup></span></a>
    <a href="<? echo ploopi_urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc=xml"); ?>"><img src="./img/export/xml.png"><span>Brut <sup>XML</sup></span></a>
    <a href="<? echo ploopi_urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc=csv"); ?>"><img src="./img/export/csv.png"><span>Brut <sup>CSV</sup></span></a>
    <?
    if (ploopi_getparam('system_jodwebservice') != '') {
        ?>
        <a href="<? echo ploopi_urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc=pdf"); ?>"><img src="./img/export/pdf.png"><span>Adobe &trade; <sup>PDF</sup></span></a>
        <a href="<? echo ploopi_urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc=ods"); ?>"><img src="./img/export/ods.png"><span>OpenOffice &trade; <sup>ODS</sup></span></a>
        <?
    }
    ?>
    <a href="<? echo ploopi_urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc=xls"); ?>"><img src="./img/export/xls.png"><span>MS Excel &trade; <sup>XLS</sup></span></a>
    <a href="<? echo ploopi_urlencode("admin-light.php?ploopi_op=system_directory_export&system_directory_typedoc=xlsx"); ?>"><img src="./img/export/xls.png"><span>MS Excel &trade; <sup>XLSX</sup></span></a>
</div>

<?php
if ($intNbRep <= $intMaxResponse && $intNbRep > 0)
{
    // Définition des colonnes du tableau (interface)
    $arrResult = array(
        'columns' => array(),
        'rows' => array()
    );

    $arrResult['columns']['left']['nom'] = array(
        'label' => 'Nom/prénom',
        'width' => '200',
        'options' => array('sort' => true)
    );

    $arrResult['columns']['left']['login'] = array(
        'label' => 'Login',
        'width' => '150',
        'options' => array('sort' => true)
    );

    $arrResult['columns']['left']['email'] = array(
        'label' => 'Courriel',
        'width' => '65'
    );

    $arrResult['columns']['left']['groups'] = array(
        'label' => 'Groupes',
        'width' => '200',
        'options' => array('sort' => true)
    );

    $arrResult['columns']['auto']['workspaces'] = array(
        'label' => 'Espaces de travail / Rôles',
        'options' => array('sort' => true)
    );

    $arrResult['columns']['right']['last_connection'] = array(
        'label' => 'Dernière connexion',
        'width' => '150',
        'options' => array('sort' => true)
    );

    if ($_SESSION['system']['level'] == 'system')
    {
        $arrResult['columns']['actions_right']['actions'] = array(
            'label' => '&nbsp;',
            'width' => 24
        );
    }

    // Exécution de la requête principale permettant de lister les utilisateurs selon le filtre
    $arrUser = $objQuery->execute()->getarray(true);


    // Requête permettant de connaître les groupes attachés aux utilisateurs
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('gu.*');
    $objQuery->add_from('ploopi_group_user gu');
    $objQuery->add_where('gu.id_user IN (%e)', array(array_keys($arrUser)));
    $objRs = $objQuery->execute();

    $arrGroupsId = array(); // Contient les Id des groupes concernés par les utilisateurs affichés
    while ($row = $objRs->fetchrow())
    {
        $arrUser[$row['id_user']]['groups'][$row['id_group']] = $arrGroups[$row['id_group']]['desc']['label'];
        $arrGroupsId[$row['id_group']] = $row['id_group'];
    }

    // Requête permettant de connaître les "adminlevel" des groupes rattachés aux espaces de la recherche
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('wg.*');
    $objQuery->add_from('ploopi_workspace_group wg');
    $objQuery->add_where('wg.id_workspace IN (%e)', array(array_keys($arrWorkspaces)));
    $objRs = $objQuery->execute();

    $arrAdminLevel = array();

    while ($row = $objRs->fetchrow()) $arrAdminLevel[$row['id_workspace']][$row['id_group']] = $row['adminlevel'];

    // Récupération des espaces des utilisateurs trouvés (via les rattachements de groupes)
    foreach($arrUser as $intIdUser => $rowUser)
    {
        if (!empty($rowUser['groups']))
        {
            foreach($arrWorkspaces as $intIdWorkspace => $rowWorkspace)
            {
                if (empty($arrUser[$intIdUser]['workspaces'][$intIdWorkspace]))
                {
                    // Permet de déterminer sur l'utilisateur est attaché à cet espace
                    $arrInt = array_intersect(array_keys($rowUser['groups']), $rowWorkspace['groups_family']);

                    $intAdminLevel = _PLOOPI_ID_LEVEL_VISITOR;

                    if (!empty($arrInt)) // L'utilisateur est attaché à cet espace
                    {
                        // On cherche son adminlevel
                        foreach($arrInt as $intIdGroupInt)
                        {
                            if (isset($arrAdminLevel[$intIdWorkspace][$intIdGroupInt]))
                            {
                                $intAdminLevel = max($arrAdminLevel[$intIdWorkspace][$intIdGroupInt], $intAdminLevel);
                            }
                        }

                        if (!$intAdminLevel)
                        {
                            foreach($arrInt as $intIdGroup)
                            {
                                $arrGroupsParent = explode(';', $arrGroups[$intIdGroup]['desc']['parents']);

                                foreach($arrGroupsParent as $intIdGroupInt)
                                {
                                    if (isset($arrAdminLevel[$intIdWorkspace][$intIdGroupInt]))
                                    {
                                        $intAdminLevel = max($arrAdminLevel[$intIdWorkspace][$intIdGroupInt], $intAdminLevel);
                                    }
                                }
                            }

                        }

                        $arrUser[$intIdUser]['workspaces'][$intIdWorkspace] = array(
                            'label' => $rowWorkspace['desc']['label'],
                            'adminlevel' => $intAdminLevel
                        );
                    }
                }
            }
        }
    }

    // Récupération des espaces des utilisateurs trouvés (via les rattachements directs)
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('wu.*');
    $objQuery->add_from('ploopi_workspace_user wu');
    $objQuery->add_where('wu.id_user IN (%e)', array(array_keys($arrUser)));
    $objQuery->add_where('wu.id_workspace IN (%e)', array(array_keys($arrWorkspaces)));
    $objRs = $objQuery->execute();

    while ($row = $objRs->fetchrow())
    {
        if (empty($arrUser[$row['id_user']]['workspaces'][$row['id_workspace']]))
            $arrUser[$row['id_user']]['workspaces'][$row['id_workspace']] = array(
                'label' => $arrWorkspaces[$row['id_workspace']]['desc']['label'],
                'adminlevel' => $row['adminlevel']
            );
        else $arrUser[$row['id_user']]['workspaces'][$row['id_workspace']]['adminlevel'] = max($arrUser[$row['id_user']]['workspaces'][$row['id_workspace']]['adminlevel'], $row['adminlevel']);
    }


    // tableau contenant les rôles pour les utilisateurs/groupes trouvés
    $arrRoles = array('groups' => array(), 'users' => array());

    if (!empty($arrGroupsId))
    {
        // recherche des rôles "groupe"
        $objQuery = new ploopi_query_select();
        $objQuery->add_select('wgr.id_group, wgr.id_workspace');
        $objQuery->add_select('r.id, r.id_module, r.label as role_label');
        $objQuery->add_select('m.label as module_label');
        $objQuery->add_from('ploopi_role r');
        $objQuery->add_from('ploopi_workspace_group_role wgr');
        $objQuery->add_from('ploopi_module m');
        $objQuery->add_where('wgr.id_role = r.id');
        $objQuery->add_where('r.id_module = m.id');
        $objQuery->add_where('wgr.id_group IN (%e)', array($arrGroupsId));
        $objRs = $objQuery->execute();

        while ($row = $objRs->fetchrow()) $arrRoles['groups'][$row['id_workspace']][$row['id_group']][$row['id']] = $row;
    }

    // recherche des rôles "utilisateur"
    $objQuery = new ploopi_query_select();
    $objQuery->add_select('wur.id_user, wur.id_workspace');
    $objQuery->add_select('r.id, r.id_module, r.label as role_label');
    $objQuery->add_select('m.label as module_label');
    $objQuery->add_from('ploopi_role r');
    $objQuery->add_from('ploopi_workspace_user_role wur');
    $objQuery->add_from('ploopi_module m');
    $objQuery->add_where('wur.id_role = r.id');
    $objQuery->add_where('r.id_module = m.id');
    $objQuery->add_where(' wur.id_user IN (%e)', array(array_keys($arrUser)));
    $objRs = $objQuery->execute();

    while ($row = $objRs->fetchrow()) $arrRoles['users'][$row['id_workspace']][$row['id_user']][$row['id']] = $row;

    foreach ($arrUser as $intUserId => $row)
    {
        // tri des groupes par nom
        if (!empty($row['groups']))
        {
            asort($row['groups']);
            $strSortLabelGroups = implode(',', $row['groups']);
        }
        else $strSortLabelGroups = '';

        // tri des espaces par nom
        if (!empty($row['workspaces'])) asort($row['workspaces']);

        $arrSortLabelWorkspaces = array();

        // conversion du tableau d'espaces en un tableau de liens vers les espaces
        if (!empty($row['workspaces']))
        {
            foreach($row['workspaces'] as $intIdWorkspace => $rowWorkspace)
            {
                $arrSortLabelWorkspaces[] = $rowWorkspace['label'];

                // tableau qui va contenir les rôles dont dispose l'utilisateur dans l'espace courant
                $arrUserWspRoles = array();
                if (isset($arrRoles['groups'][$intIdWorkspace]))
                {
                    foreach($arrRoles['groups'][$intIdWorkspace] as $intIdGrp => $arrDetail)
                    {
                        // L'utilisateur appartient au groupe (donc il a les rôles)
                        if (!empty($row['groups']) && in_array($intIdGrp, array_keys($row['groups'])))
                        {
                            foreach($arrDetail as $intIdRole => $arrR)
                                $arrUserWspRoles[$intIdRole] = $_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN ?
                                    sprintf(
                                        "<a title=\"Accéder à ce rôle\" href=\"%s\">%s</a><span>&nbsp;(</span><a title=\"Accéder à ce module\" href=\"%s\">%s</a><span>)</span>",
                                        ploopi_urlencode("admin.php?system_level=work&workspaceid={$intIdWorkspace}&wspToolbarItem=tabRoles&op=modify_role&roleid={$intIdRole}"),
                                        ploopi_htmlentities($arrR['role_label']),
                                        ploopi_urlencode("admin.php?system_level=work&workspaceid={$intIdWorkspace}&wspToolbarItem=tabModules&op=modify&moduleid={$arrR['id_module']}"),
                                        ploopi_htmlentities($arrR['module_label'])
                                    ) : sprintf("%s (%s)", ploopi_htmlentities($arrR['role_label']), ploopi_htmlentities($arrR['module_label']));
                        }
                    }
                }

                if (isset($arrRoles['users'][$intIdWorkspace][$intUserId]))
                {
                    foreach($arrRoles['users'][$intIdWorkspace][$intUserId] as $intIdRole => $arrR)
                        $arrUserWspRoles[$intIdRole] = $_SESSION['ploopi']['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN ?
                            sprintf(
                                    "<a title=\"Accéder à ce rôle\" href=\"%s\">%s</a><span>&nbsp;(</span><a title=\"Accéder à ce module\" href=\"%s\">%s</a><span>)</span>",
                                    ploopi_urlencode("admin.php?system_level=work&workspaceid={$intIdWorkspace}&wspToolbarItem=tabRoles&op=modify_role&roleid={$intIdRole}"),
                                    ploopi_htmlentities($arrR['role_label']),
                                    ploopi_urlencode("admin.php?system_level=work&workspaceid={$intIdWorkspace}&wspToolbarItem=tabModules&op=modify&moduleid={$arrR['id_module']}"),
                                    ploopi_htmlentities($arrR['module_label'])
                                ) : sprintf("%s (%s)", ploopi_htmlentities($arrR['role_label']), ploopi_htmlentities($arrR['module_label']));
                }

                // Chaine contenant, pour un utilisateur et un espace, la liste des rôles
                $strUserWspRoles = empty($arrUserWspRoles) ? '' : '<span>&nbsp;:&nbsp;</span>'.implode('<span>,&nbsp;</span>', $arrUserWspRoles);

                if ($rowWorkspace['adminlevel'] >= _PLOOPI_ID_LEVEL_SYSTEMADMIN) $icon = 'level_systemadmin';
                elseif($rowWorkspace['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPADMIN) $icon = 'level_groupadmin';
                elseif($rowWorkspace['adminlevel'] >= _PLOOPI_ID_LEVEL_GROUPMANAGER) $icon = 'level_groupmanager';
                else $icon = 'level_user';

                $row['workspaces'][$intIdWorkspace] = sprintf(
                    "<img style=\"float:left;\" src=\"%s\" /><span style=\"display:block;margin-left:20px;\"><a title=\"Accéder à cet espace\" href=\"%s\">%s</a>%s</span>",
                    "{$_SESSION['ploopi']['template_path']}/img/system/adminlevels/{$icon}.png",
                    ploopi_urlencode("admin.php?system_level=work&workspaceid={$intIdWorkspace}"),
                    ploopi_htmlentities($rowWorkspace['label']),
                    $strUserWspRoles
                );
            }
        }

        // conversion du tableau de libellé de groupes en un tableau de liens vers les groupes
        if (!empty($row['groups']))
        {
            foreach($row['groups'] as $intId => $strLabel)
                $row['groups'][$intId] = sprintf(
                    "<a title=\"Accéder à ce groupe\" href=\"%s\">%s</a>",
                    ploopi_urlencode("admin.php?system_level=work&groupid={$intId}"),
                    ploopi_htmlentities($strLabel)
                );
        }

        $strUserLabel = ploopi_htmlentities(sprintf("%s %s", $row['lastname'], $row['firstname']));
        $strUserLogin = ploopi_htmlentities($row['login']);

        // si l'utilisateur est attaché à un groupe, on met un lien vers la fiche de l'utilisateur pour pouvoir la modifier
        if (!empty($row['groups']))
        {
            reset($row['groups']);
            $strUserLabel = sprintf(
                "<a title=\"Accéder à cet utilisateur\" href=\"%s\">%s</a>",
                ploopi_urlencode("admin.php?system_level=work&groupid=".key($row['groups'])."&wspToolbarItem=tabUsers&op=modify_user&user_id={$intUserId}"),
                $strUserLabel
            );

            $strUserLogin = sprintf(
                "<a title=\"Accéder à cet utilisateur\" href=\"%s\">%s</a>",
                ploopi_urlencode("admin.php?system_level=work&groupid=".key($row['groups'])."&wspToolbarItem=tabUsers&op=modify_user&user_id={$intUserId}"),
                $strUserLogin
            );

        }

        $arrResult['rows'][] = array(
            'values' => array(
                'nom' => array('label' => $strUserLabel, 'sort_label' => sprintf("%s %s", $row['lastname'], $row['firstname'])),
                'login' => array('label' => $strUserLogin, 'sort_label' => $row['login']),
                'email' => array('label' => empty($row['email']) ? '' : '<a title="'.$row['email'].'" href="mailto:'.$row['email'].'"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/email.gif" /></a>', 'sort_label' => $row['email']),
                'groups' => array(
                    'label' => (empty($row['groups'])) ? '<em>Pas de groupe dans cet espace</em>'  : implode('<br />', $row['groups']),
                    'sort_label' => $strSortLabelGroups
                ),
                'workspaces' => array(
                    'label' => (empty($row['workspaces'])) ? '<em>Pas d\'espace</em>' : implode('', $row['workspaces']),
                    'sort_label' => implode(',', $arrSortLabelWorkspaces)
                ),
                'last_connection' => array(
                    'label' => implode(' ', ploopi_timestamp2local($row['last_connection'])),
                    'sort_label' => $row['last_connection'],
                    'sort_flag' => SORT_NUMERIC
                ),
                'actions' => array('label' => '<a href="javascript:ploopi_confirmlink(\''.ploopi_urlencode("admin.php?ploopi_op=system_delete_user&system_user_id={$intUserId}").'\',\''._SYSTEM_MSG_CONFIRMUSERDELETE.'\')"><img src="'.$_SESSION['ploopi']['template_path'].'/img/system/btn_delete.png" title="'._SYSTEM_LABEL_DELETE.'"></a>')
            )
        );
    }

    $skin->display_array(
        $arrResult['columns'],
        $arrResult['rows'],
        'system_directory',
        array(
            'sortable' => true,
            'orderby_default' => 'login',
            'limit' => 25
        )
    );
}
echo $skin->close_simplebloc();

?>

<p class="ploopi_va" style="padding:4px;">
    <span style="margin-right:5px;">Légende:</span>
    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_user.png" />
    <span style="margin-right:5px;"><?php echo ploopi_htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_USER]); ?></span>
    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupmanager.png" />
    <span style="margin-right:5px;"><?php echo ploopi_htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPMANAGER]); ?></span>
    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_groupadmin.png" />
    <span style="margin-right:5px;"><?php echo ploopi_htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_GROUPADMIN]); ?></span>
    <img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/adminlevels/level_systemadmin.png" />
    <span style="margin-right:5px;"><?php echo ploopi_htmlentities($ploopi_system_levels[_PLOOPI_ID_LEVEL_SYSTEMADMIN]); ?></span>
</p>
