<?php
/*
    Copyright (c) 2007-2018 Ovensia
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
 * Légende (popup)
 *
 * @package doc
 * @subpackage public
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see skin::create_popup
 */

/**
 * On démarre un buffer spécifique pour envoyer le contenu dans un popup
 */

ob_start();
?>
<div style="padding:4px;">
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_home.png"><span style="margin-left:4px;">Racine</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_newfolder.png"><span style="margin-left:4px;">Nouveau Dossier</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_newfile.png"><span style="margin-left:4px;">Nouveau Fichier</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_search.png"><span style="margin-left:4px;">Rechercher</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_download.png"><span style="margin-left:4px;">Télécharger</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_download_zip.png"><span style="margin-left:4px;">Télécharger (ZIP)</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_modify.png"><span style="margin-left:4px;">Voir/Modifier</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_trash.png"><span style="margin-left:4px;">Supprimer</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_validate.png"><span style="margin-left:4px;">Valider</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_folder_public.png"><span style="margin-left:4px;">Dossier Public</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_folder_public_locked.png"><span style="margin-left:4px;">Dossier Public protégé</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_folder_shared.png"><span style="margin-left:4px;">Dossier Partagé</span>
    </p>
    <p class="ploopi_va" style="margin:2px;">
        <img src="./modules/doc/img/ico_folder_shared_locked.png"><span style="margin-left:4px;">Dossier Partagé protégé</span>
    </p>
    <p class="ploopi_va" style="background-color:#ffe0e0;padding:2px 0;margin:2px;">
        <span style="margin-left:4px;">Dossier ou fichier à valider</span>
    </p>
</div>
<?php
/**
 * On récupère le contenu du buffer et on supprime le buffer
 */
$content = ob_get_contents();
ob_end_clean();

/**
 * Affichage du popup
 */
echo ploopi\skin::get()->create_popup('Légende', $content, 'dochelp');
?>
