<?php
/*
    Copyright (c) 2007-2016 Ovensia
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


// ploopi\output::print_r($_SESSION['system']['user_import']);


$objUser = new ploopi\user();
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
<?php
if (empty($arrErrors))
{
    ?>
    <p class="ploopi_va" style="padding:2px;"><span>Le fichier semble conforme</span></p>
    <?php
}
else
{
    foreach($arrErrors as $strError)
    {
        ?>
        <p class="ploopi_va" style="padding:2px;"><img src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/attention.png" style="margin-right:4px;" /><span class="error"><?php echo ploopi\str::htmlentities($strError); ?></span></p>
        <?php
    }
}

if (!$booCriticalError)
{
    ?>
    <div style="padding:2px;border-bottom:1px solid #a0a0a0;background-color:#e0e0e0;"><strong>Aperçu de l'import (<a href="<?php echo ploopi\crypt::urlencode("admin.php?usrTabItem=tabUserImport&op=import"); ?>">Confirmer l'import</a>):</strong></div>
    <?php
    
    $columns = array();
    $values = array();
    
    foreach($_SESSION['system']['user_import'][0] as $strFieldName)
    {
        $columns['left'][$strFieldName] =
            array(
                'label' => ploopi\str::htmlentities($strFieldName),
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
            if (isset($_SESSION['system']['user_import'][$intI][$intJ])) $values[$intC]['values'][$strFieldName] = array('label' => ploopi\str::htmlentities($_SESSION['system']['user_import'][$intI][$intJ]));
            $intJ++;
        }   
        $intC++;
    }
    
    ploopi\skin::get()->display_array($columns, $values, 'array_user_importlist', array('sortable' => true));
}
?>