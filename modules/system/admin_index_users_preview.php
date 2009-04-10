<?php
/*
    Copyright (c) 2007-2009 Ovensia
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
 * Gestion de l'import d'utilisateurs par fichier csv.
 * Attention probablement non fonctionnel.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane ESCAICH
 */


// ploopi_print_r($_SESSION['system']['user_import']);


$objUser = new user();
$objUser->init_description();

$arrErrors = array();

$arrNeededFields = array(
    'login'     => 0,
    'password'  => 0,
    'lastname'  => 0,
    'firstname' => 0
);

$arrValidFields = array_keys($objUser->fields);

$booCriticalError = false;

if (empty($_SESSION['system']['user_import']) || empty($_SESSION['system']['user_import'][0]))
{
    $arrErrors[] = 'Fichier invalide';
    $booCriticalError = true;
}

// Analyse de la ligne de titre (ligne 0)
foreach($_SESSION['system']['user_import'][0] as $strFieldName)
{
    if (!in_array($strFieldName, $arrValidFields) || $strFieldName == 'id')
    {
        $arrErrors[] = "Champ '{$strFieldName}' invalide";
    }
    
    // Est-ce un champ obligatoire
    if (isset($arrNeededFields[$strFieldName])) $arrNeededFields[$strFieldName] = 1;
}

foreach($arrNeededFields as $strFieldName => $booFound)
{
    if (!$booFound) 
    {
        $arrErrors[] = "Champ '{$strFieldName}' non trouvé";
        $booCriticalError = true;
    }
}

for ($intI = 1; $intI < count($_SESSION['system']['user_import']); $intI++)
{
    if (count($_SESSION['system']['user_import'][$intI]) != count($_SESSION['system']['user_import'][0])) $arrErrors[] = "Taille de l'enregistrement n° {$intI} invalide";
}

if ($booCriticalError) $arrErrors[] = "Erreur critique, impossible d'importer le fichier";

?>
<div style="padding:2px;border-bottom:1px solid #a0a0a0;background-color:#e0e0e0;"><strong>Import d'un fichier CSV contenant des utilisateurs :</strong></div>
<?
if (empty($arrErrors))
{
    ?>
    <p class="ploopi_va" style="padding:2px;"><span>Le fichier semble conforme</span></p>
    <?
}
else
{
    foreach($arrErrors as $strError)
    {
        ?>
        <p class="ploopi_va" style="padding:2px;"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png" style="margin-right:4px;" /><span class="error"><? echo $strError ?></span></p>
        <?
    }
}

if (!$booCriticalError)
{
    ?>
    <div style="padding:2px;border-bottom:1px solid #a0a0a0;background-color:#e0e0e0;"><strong>Aperçu de l'import (<a href="<? echo ploopi_urlencode("admin.php?usrTabItem=tabUserImport&op=import"); ?>">Confirmer l'import</a>):</strong></div>
    <?
    
    $columns = array();
    $values = array();
    
    foreach($_SESSION['system']['user_import'][0] as $strFieldName)
    {
        $columns['left'][$strFieldName] =
            array(
                'label' => $strFieldName,
                'width' => 90,
                'options' => array('sort' => true)
            );
    }
    
    $intC = 0;
    
    for ($intI = 1; $intI < count($_SESSION['system']['user_import']); $intI++)
    {
        $intJ = 0;
        foreach($_SESSION['system']['user_import'][0] as $strFieldName)
        {
            if (isset($_SESSION['system']['user_import'][$intI][$intJ])) $values[$intC]['values'][$strFieldName] = array('label' => $_SESSION['system']['user_import'][$intI][$intJ]);
            $intJ++;
        }   
        $intC++;
    }
    
    $skin->display_array($columns, $values, 'array_user_importlist', array('sortable' => true));
}


/*
if($_FILES['srcfile']['name'] != '')
{
    $created = array();
    $errors = array();
    $fields = array();
    $handle = fopen ($_FILES['srcfile']['tmp_name'], "r");
    while ($line = fgets($handle, 4096))
    {
        $content = explode(';',$line);

        // Ligne de description de la structure du fichier
        if(!count($fields)) $fields = array_flip($content);

        // Ligne d'utilisateur à aujouter
        else
        {
            // On vérifie que la première ligne du fichier est bien
            // la ligne de description de la structure du fichier
            if(array_key_exists('login',$fields) && array_key_exists('password',$fields))
            {
                // On supprime les espaces en trop
                foreach($content as $key => $value)
                {
                    $content[$key] = trim($value);
                }

                // On vérifie que le login et le mot de passe ne sont pas vides
                if($content[$fields['login']] != '' && $content[$fields['password']] != '')
                {
                    (isset($md5passwd)) ? $password = $content[$fields['password']] : $password = md5($content[$fields['password']]);

                    // On vérifie que le login n'existe pas
                    $sql = "SELECT id FROM ploopi_user WHERE login = '{$content[$fields['login']]}'";
                    $db->query($sql);

                    // Si le login n'existe pas
                    if(!$db->numrows())
                    {
                        $user = new user();
                        $user->fields['id_type']        = -1;
                        $user->fields['id_ldap']        = -1;
                        $user->fields['date_creation']  = ploopi_createtimestamp();
                        $user->fields['login']          = trim($content[$fields['login']]);
                        $user->fields['password']       = trim($password);

                        if(isset($fields['lastname']))  $user->fields['lastname']   = trim($content[$fields['lastname']]);
                        if(isset($fields['firstname'])) $user->fields['firstname']  = trim($content[$fields['firstname']]);
                        if(isset($fields['email']))     $user->fields['email']      = trim($content[$fields['email']]);
                        if(isset($fields['phone']))     $user->fields['phone']      = trim($content[$fields['phone']]);
                        if(isset($fields['fax']))       $user->fields['fax']        = trim($content[$fields['fax']]);
                        if(isset($fields['comments']))  $user->fields['comments']   = trim($content[$fields['comments']]);
                        if(isset($fields['address']))   $user->fields['address']    = trim($content[$fields['address']]);

                        $user->save();

                        (isset($fields['adminlevel']) && $content[$fields['adminlevel']] != '') ? $adminlevel = $content[$fields['adminlevel']] : $adminlevel = _PLOOPI_ID_LEVEL_USER;

                        $workspace_user = new workspace_user();
                        $workspace_user->fields['id_user']      = $user->fields['id'];
                        $workspace_user->fields['id_workspace']     = $workspaceid;
                        $workspace_user->fields['adminlevel']   = $adminlevel;
                        $workspace_user->save();

                        $created[$content[$fields['login']]] = $content[$fields['password']];
                    }
                    else
                    {
                        $errors[$content[$fields['login']]] = "Le login existe déjà.";
                    }
                }
                else
                {
                    $errors[$content[$fields['login']]] = "Le login ou le mot de passe n'est pas valide.";
                }
            }
            else
            {
                $errors['Attention'] = "La première ligne du fichier doit être une ligne de description valide.";
            }
        }
    }
    fclose ($handle);
}

// Affichage du récapitulatif et des erreurs
?>

<table cellpadding="2" cellspacing="1" width="100%">
    <tr>
        <td width="50%" valign="top">
            <?php
            if(count($created))
            {
                echo $skin->open_simplebloc('Récapitulatif');
                    foreach($created as $login => $passwd)
                    {
                        echo "<table><tr><td><b>$login</b> : $passwd</td></tr></table>";
                    }
                echo $skin->close_simplebloc();
            }
            ?>
        </td>
        <td width="50%" valign="top">
            <?php
            if(count($errors))
            {
                echo $skin->open_simplebloc('Erreurs');
                    foreach($errors as $login => $msg)
                    {
                        echo "<table><tr><td><b>$login</b> : $msg</td></tr></table>";
                    }
                echo $skin->close_simplebloc();
            }
            ?>
        </td>
    </tr>
</table>
*/