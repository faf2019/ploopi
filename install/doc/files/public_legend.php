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
<? ob_start(); ?>
<div style="padding:2px;">
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_home.png"><span> Racine</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_newfolder.png"><span> Nouveau Dossier</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_newfile.png"><span> Nouveau Fichier</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_search.png"><span> Rechercher</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_download.png"><span> Télécharger</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_download_zip.png"><span> Télécharger (ZIP)</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_modify.png"><span> Modifier</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_trash.png"><span> Supprimer</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_validate.png"><span> Valider</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_folder_public.png"><span> Dossier Public</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_folder_public_locked.png"><span> Dossier Public en Lecture Seule</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_folder_shared.png"><span> Dossier Partagé</span>
</p>
<p class="ploopi_va">
	<img src="./modules/doc/img/ico_folder_shared_locked.png"><span> Dossier Partagé en Lecture Seule</span>
</p>
</div>
<?
$content = ob_get_contents();
ob_end_clean();
echo $skin->popup('Légende', $content, 'dochelp');
?>
