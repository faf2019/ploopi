<?php
/*
    Copyright (c) 2009 HeXad
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
 * "Constructeur" de l'instance.
 * Initialise les données du module lors de l'instanciation du module.
 *
 * @package gallery
 * @subpackage create
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion des classe gallery
 */

include_once './modules/gallery/class/class_gallery.php';
include_once './modules/gallery/class/class_gallery_tpl.php';


/**
 * On crée les 3 premières galeries de base
 */

// GALERIE 1
// Le template
$objTpl = new gallery_tpl();
$objTpl->setautosaveinfo(false);

$objTpl->fields['block'] = 'gallery1';
$objTpl->fields['description'] = 'Affichage de l\'image principale avec les vignettes en dessous';
$objTpl->fields['note'] = 'Attention, cette galerie requière OBLIGATOIREMENT :\r\nNombre de lignes = 1\r\nNombre de colonnes = 0\r\nDimension des vignettes = 92 x 69\r\nDimension des images = 500 x 375';
$objTpl->fields['addtoheadcss'] = 'gallery1.css';
$objTpl->fields['addtoheadcssie'] = 'gallery1_ie.css';
$objTpl->fields['id_module'] = $this->fields['id'];
$objTpl->fields['id_workspace'] = $this->fields['id_workspace'];
$objTpl->fields['id_user'] = 0;

$objTpl->save();

$idTpl = $objTpl->fields['id'];
unset($objTpl);

// La galerie
$objGallery = new gallery();
$objGallery->setautosaveinfo(false);

$objGallery->fields['label'] = 'Galerie 1';
$objGallery->fields['description'] = 'Premier exemple de galerie';
$objGallery->fields['template'] = $idTpl;
$objGallery->fields['nb_col'] = 0;
$objGallery->fields['nb_line'] = 1;
$objGallery->fields['thumb_width'] = 92;
$objGallery->fields['thumb_height'] = 69;
$objGallery->fields['thumb_color'] = '#000000';
$objGallery->fields['view_width'] = 500;
$objGallery->fields['view_height'] = 375;
$objGallery->fields['view_color'] = '#FFFFFF';

$objGallery->fields['create_id_user'] = 0;
$objGallery->fields['create_user'] = '';
$objGallery->fields['create_timestp'] = ploopi_createtimestamp();

$objGallery->fields['id_module'] = $this->fields['id'];
$objGallery->fields['id_workspace'] = $this->fields['id_workspace'];
$objGallery->fields['id_user'] = 0;

$objGallery->save();
unset($objGallery);

// GALERIE 2
// Le template
$objTpl = new gallery_tpl();
$objTpl->setautosaveinfo(false);

$objTpl->fields['block'] = 'gallery2';
$objTpl->fields['description'] = 'Affichage des miniatures avec image principale en lightbox';
$objTpl->fields['note'] = '';
$objTpl->fields['addtoheadcss'] = 'gallery2.css,lightbox.css';
$objTpl->fields['addtoheadcssie'] = '';
$objTpl->fields['id_module'] = $this->fields['id'];
$objTpl->fields['id_workspace'] = $this->fields['id_workspace'];
$objTpl->fields['id_user'] = 0;

$objTpl->save();

$idTpl = $objTpl->fields['id'];
unset($objTpl);

// La galerie
$objGallery = new gallery();
$objGallery->setautosaveinfo(false);

$objGallery->fields['label'] = 'Galerie 2';
$objGallery->fields['description'] = 'Second exemple de galerie';
$objGallery->fields['template'] = $idTpl;
$objGallery->fields['nb_col'] = 5;
$objGallery->fields['nb_line'] = 4;
$objGallery->fields['thumb_width'] = 92;
$objGallery->fields['thumb_height'] = 69;
$objGallery->fields['thumb_color'] = '#000000';
$objGallery->fields['view_width'] = 500;
$objGallery->fields['view_height'] = 375;
$objGallery->fields['view_color'] = '#FFFFFF';

$objGallery->fields['create_id_user'] = 0;
$objGallery->fields['create_user'] = '';
$objGallery->fields['create_timestp'] = ploopi_createtimestamp();

$objGallery->fields['id_module'] = $this->fields['id'];
$objGallery->fields['id_workspace'] = $this->fields['id_workspace'];
$objGallery->fields['id_user'] = 0;

$objGallery->save();
unset($objGallery);

// GALERIE 3
// Le template
$objTpl = new gallery_tpl();
$objTpl->setautosaveinfo(false);

$objTpl->fields['block'] = 'gallery3';
$objTpl->fields['description'] = 'Galerie avec utilisation de dewslider (plugin flash)';
$objTpl->fields['note'] = '';
$objTpl->fields['addtoheadcss'] = '';
$objTpl->fields['addtoheadcssie'] = '';
$objTpl->fields['id_module'] = $this->fields['id'];
$objTpl->fields['id_workspace'] = $this->fields['id_workspace'];
$objTpl->fields['id_user'] = 0;

$objTpl->save();

$idTpl = $objTpl->fields['id'];
unset($objTpl);

// La galerie
$objGallery = new gallery();
$objGallery->setautosaveinfo(false);

$objGallery->fields['label'] = 'Galerie 3';
$objGallery->fields['description'] = 'Troisième exemple de galerie';
$objGallery->fields['template'] = $idTpl;
$objGallery->fields['nb_col'] = 0;
$objGallery->fields['nb_line'] = 0;
$objGallery->fields['thumb_width'] = 0;
$objGallery->fields['thumb_height'] = 0;
$objGallery->fields['thumb_color'] = '#FFFFFF';
$objGallery->fields['view_width'] = 500;
$objGallery->fields['view_height'] = 333;
$objGallery->fields['view_color'] = '#BDBDBD';

$objGallery->fields['create_id_user'] = 0;
$objGallery->fields['create_user'] = '';
$objGallery->fields['create_timestp'] = ploopi_createtimestamp();

$objGallery->fields['id_module'] = $this->fields['id'];
$objGallery->fields['id_workspace'] = $this->fields['id_workspace'];
$objGallery->fields['id_user'] = 0;

$objGallery->save();
unset($objGallery);
?>
