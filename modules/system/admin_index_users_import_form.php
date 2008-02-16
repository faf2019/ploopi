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
<form action="<? echo $scriptenv; ?>" method="Post" enctype="multipart/form-data">
<input type="Hidden" name="op" value="import">

<table cellpadding="2" cellspacing="1" align="center" width="100%">
    <tr>
        <td align="right"><? echo _SYSTEM_LABEL_IMPORTSRC; ?>*:&nbsp;</td>
        <td align="left"><input class="text" type="File" name="srcfile"></td>
    </tr>
    <tr>
        <td align="right">Mots de passe MD5** ?</td>
        <td align="left"><input class="Checkbox" type="Checkbox" name="md5passwd"></td>
    </tr>
    <tr>
        <td align="center" colspan="2"><input class="flatbutton" type="Submit" value="Importer"></td>
    </tr>
    <tr>
        <td colspan="2">
            <br>
            (*) La source d'import doit être un fichier texte, dont les champs sont séparés par des points-virgule.<br>
            La première ligne doit être une ligne de description.<br>
            Cela signifie qu'elle contient certains ou tous les champs de la liste ci-dessous.<br>
            Elle sert à décrire la structure du fichier.<br>
            Elle doit au moins contenir les champs "login" et "password".<br><br>
            Liste des champs :<br>
            - adminlevel : Niveau du compte. S'il n'est pas renseigné, il sera affecté au niveau le plus bas.<br>
            - lastname : Nom de famille.<br>
            - firstname : Prénom.<br>
            - login : Login. Il doit être unique. Il est nécessaire à la création d'un compte.<br>
            - password : Mot de passe. Il est nécessaire à la création d'un compte.<br>
            - email : Email.<br>
            - phone : Numéro de téléphone.<br>
            - fax : Numéro de fax.<br>
            - address : Adresse.<br>
            - comments : Commentaires.<br><br>
            Liste des valeurs possibles pour le champ "adminlevel" :<br>
            - 10 : Utilisateur<br>
            - 15 : Gestionnaire de groupe<br>
            - 20 : Administrateur de groupe<br>
            - 99 : Administrateur<br><br>

            (**)Si la case "Mots de passe MD5" est cochée, les mots de passe seront sauvegardés tels quels.<br>
            Dans le cas contraire, ils seront encryptés en MD5.
        </td>
    </tr>
</form>
</table>
