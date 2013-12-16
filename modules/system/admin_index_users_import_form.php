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
 * Interface pour l'import d'utilisateurs par fichier csv.
 * Attention probablement non fonctionnel.
 *
 * @package system
 * @subpackage admin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author St�phane ESCAICH
 *
 * @todo Am�liorer l'interface et les fonctionnalit�s
 */

/**
 * Formulaire d'import de fichier
 */

$arrFields = array(
    'lastname'      =>  "Nom (obligatoire)",
    'firstname'     =>  "Pr�nom (obligatoire)",
    'login'         =>  "Identifiant utilisateur (obligatoire)",
    'password'      =>  "Mot de passe (obligatoire)",
    'adminlevel'    =>  "Niveau du compte. S'il n'est pas renseign�, il sera affect� au niveau le plus bas",
    'email'         =>  "Adresse m�l",
    'phone'         =>  "Num�ro de T�l�phone",
    'fax'           =>  "Num�ro de Fax",
    'mobile'        =>  "Num�ro de Portable",
    'number'        =>  "Num�ro de Poste",

    'address'       =>  "Adresse",
    'postalcode'    =>  "Code Postal",
    'city'          =>  "Ville",
    'country'       =>  "Pays",

    'building'      =>  "B�timent",
    'floor'         =>  "Etage",
    'office'        =>  "Bureau",

    'service'       =>  "Service",
    'function'      =>  "Fonction",
    'rank'          =>  "Grade",

    'comments'      =>  "Commentaires"
);
?>
<form action="<?php echo ploopi_urlencode('admin.php?ploopi_op=system_user_import'); ?>" method="post" enctype="multipart/form-data">

<div style="width:500px;margin:auto;">
    <div class="ploopi_form">
        <p>
            <label><?php echo _SYSTEM_LABEL_IMPORTSRC; ?>:</label>
            <input type="file" class="text" name="system_user_file" tabindex="100" />
        </p>
        <p>
            <label>S�parateur:</label>
            <select class="select" name="system_user_sep" tabindex="102" style="width:40px;">
                <option value="<? echo ploopi_htmlentities(',') ?>"><? echo ploopi_htmlentities(',') ?></option>
                <option value="<? echo ploopi_htmlentities(';') ?>"><? echo ploopi_htmlentities(';') ?></option>
            </select>
        </p>
    </div>
    <div style="text-align:right;padding:4px;">
        <input type="submit" class="flatbutton" value="<?php echo _PLOOPI_SEND; ?>" tabindex="110" />
    </div>

    <div style="padding:4px;">
        Le fichier source doit �tre au format CSV standard ( s�parateur : , ou ; / d�limiteur : " / caract�re d'�chappement : \ )<br>
        La premi�re ligne doit �tre une ligne de description des colonnes.<br>
        Cela signifie qu'elle contient certains ou tous les champs de la liste ci-dessous :<br>
        <ul>
            <?
            foreach($arrFields as $strField => $strLabel)
            {
                ?>
                <li><strong><? echo ploopi_htmlentities($strField); ?></strong>: <? echo ploopi_htmlentities($strLabel); ?></li>
                <?
            }
            ?>
        </ul>
        Liste des valeurs possibles pour le champ "adminlevel" :
        <ul>
            <li>10 : Utilisateur</li>
            <li>15 : Gestionnaire de groupe</li>
            <li>20 : Administrateur de groupe</li>
            <li>99 : Administrateur</li>
        </ul>
    </div>

</div>
</form>

