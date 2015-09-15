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
 * @author Stéphane ESCAICH
 *
 * @todo Améliorer l'interface et les fonctionnalités
 */

/**
 * Formulaire d'import de fichier
 */

$arrFields = array(
    'lastname'      =>  "Nom (obligatoire)",
    'firstname'     =>  "Prénom (obligatoire)",
    'login'         =>  "Identifiant utilisateur (obligatoire)",
    'password'      =>  "Mot de passe (obligatoire)",
    'adminlevel'    =>  "Niveau du compte. S'il n'est pas renseigné, il sera affecté au niveau le plus bas",
    'email'         =>  "Adresse de courriel",
    'phone'         =>  "Numéro de Téléphone",
    'fax'           =>  "Numéro de Fax",
    'mobile'        =>  "Numéro de Portable",
    'number'        =>  "Numéro de Poste",

    'address'       =>  "Adresse",
    'postalcode'    =>  "Code Postal",
    'city'          =>  "Ville",
    'country'       =>  "Pays",

    'building'      =>  "Bâtiment",
    'floor'         =>  "Etage",
    'office'        =>  "Bureau",

    'entity'        =>  "Organisme",
    'service'       =>  "Service",
    'function'      =>  "Fonction",
    'rank'          =>  "Grade",

    'date_expire'   => "Date d'expiration du compte (AAAAMMJJHHMMSS)",
    'service2'      => "Bureau",
    'ticketsbyemail' => "Copie des messages par Courriel (1/0)",
    'servertimezone' => "Synchronisé avec le fuseau horaire du serveur (1/0)",
    'timezone'      => "Fuseau horaire spécifique (<a href=\"http://php.net/manual/fr/timezones.php)\" target=\"_new\">http://php.net/manual/fr/timezones.php</a>)",
    'color'         => "Code couleur (#0F0F0F)",
    'civility'      => "Civilité (M/Mme/Mlle)",
    'password_force_update' => "Forcer le changement du mot de passe à la prochaine connexion (1/0)",
    'password_validity'     => "Durée de validité du mot de passe en jours (0 = illimité)",
    'disabled'      => "Compte désactivé",


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
            <label>Séparateur:</label>
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
        Le fichier source doit être au format CSV standard ( séparateur : , ou ; / délimiteur : " / caractère d'échappement : \ )<br>
        La première ligne doit être une ligne de description des colonnes.<br>
        Cela signifie qu'elle contient certains ou tous les champs de la liste ci-dessous :<br>
        <ul>
            <?
            foreach($arrFields as $strField => $strLabel)
            {
                ?>
                <li><strong><? echo $strField; ?></strong>: <? echo $strLabel; ?></li>
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

