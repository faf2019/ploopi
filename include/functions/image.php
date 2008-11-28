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

/**
 * Fonctions de manipulation d'images.
 * Redimensionnement, changement de format.
 *
 * @package ploopi
 * @subpackage image
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */


/**
 * Redimensionne une image et l'enregistre dans un fichier ou la renvoie vers le navigateur
 *
 * @param string $imagefile chemin vers le fichier image
 * @param float $coef ratio de redimensionnement de l'image destination
 * @param int $wmax largeur max de l'image destination
 * @param int $hmax hauteur max de l'image destination
 * @param string $format format de l'image destination (jpg, png, gif)
 * @param int $nbcolor taille de la palette de l'image destination
 * @param string $filename nom du fichier image destination, si vide renvoit l'image vers le navigateur
 * @param string (6) couleur de fond hexadécimal RVB pour redimension avec marge
 * @return boolean true si redimensionnement ok
 *
 * @link http://fr.php.net/manual/fr/ref.image.php
 */

function ploopi_resizeimage($imagefile, $coef = 0, $wmax = 0, $hmax = 0, $format = '', $nbcolor = 0, $filename = '',$addBorder = false)
{
    //$c = new ploopi_cache($imagefile, 8640000, _PLOOPI_PATHDATA._PLOOPI_SEP.'cache'._PLOOPI_SEP);
    //if (!$c->start())
    //{

    $imagefile_name = basename($imagefile);
    $extension = ploopi_file_getextension($imagefile_name);

    switch($extension)
    {
        case 'jpg':
        case 'jpeg':
          $imgsrc = ImageCreateFromJPEG($imagefile);
        break;

        case 'png':
          $imgsrc = ImageCreateFromPng($imagefile);
        break;

        case 'gif':
          $imgsrc = imagecreatefromgif($imagefile);
        break;

        default:
          return(false);
        break;
    }

    $w = imagesx($imgsrc);
    $h = imagesy($imgsrc);

    if (!$coef) // no coef defined
    {
        if ($wmax) $coef = $w/$wmax;
        if ($hmax && $h/$hmax > $coef) $coef = $h/$hmax;
    }
    
    $wdest = round($w/$coef);
    $hdest = round($h/$coef);

    if(!empty($addBorder) && $wmax && $hmax)
    {
      $red = $green = $blue = '255';

      if(preg_match('`^#([A-Fa-f0-9]{2})([A-Fa-f0-9]{2})([A-Fa-f0-9]{2})$`',$addBorder,$color))
      {
        $red = hexdec($color[1]);
        $green = hexdec($color[2]);
        $blue = hexdec($color[3]);
      }

      $imgdest = imagecreatetruecolor ($wmax, $hmax);

      $background = imagecolorallocate($imgdest,$red,$green,$blue);

      imageFilledRectangle($imgdest, 0, 0, $wmax, $hmax, $background);

      $distX = ($wmax > $wdest) ? (($wmax-$wdest)/2) : 0;
      $distY = ($hmax > $hdest) ? (($hmax-$hdest)/2) : 0;
      imagecopyresampled($imgdest, $imgsrc, $distX, $distY, 0, 0, $wdest, $hdest, $w, $h);
    }
    else
    {
      $imgdest = imagecreatetruecolor ($wdest, $hdest);
      imagecopyresampled($imgdest, $imgsrc, 0, 0, 0, 0, $wdest, $hdest, $w, $h);
    }

    if ($nbcolor) imagetruecolortopalette($imgdest, true, $nbcolor);

    if($format != '')
    {
        $extension = $format;
        $imagefile = substr($imagefile,0,strlen($imagefile) - strlen(ploopi_file_getextension($imagefile)) + 1);
    }

    if($filename == '')
    {
        header("Content-Type: image/{$extension}");
        header("Content-Disposition: inline; filename=\"{$imagefile_name}\"");

        switch($extension)
        {
            case 'jpg':
            case 'jpeg':
              imagejpeg($imgdest);
            break;

            case 'png':
                imagepng($imgdest);
            break;

            case 'gif':
                imagepng($imgdest);
            break;

            default:
                return(false);
            break;
        }
    }
    else
    {
        $path = dirname($filename);
        $exists = file_exists($filename);
        if (is_writable($path) && (!$exists || ($exists && is_writable($filename))))
        {
            switch($extension)
            {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($imgdest, $filename);
                break;

                case 'png':
                    imagepng($imgdest, $filename);
                break;

                case 'gif':
                    imagepng($imgdest, $filename);
                break;

                default:
                    return(false);
                break;
            }
        }
        else
          return(false);
    }

    //$c->end();
    //}
    return(true);
}


?>
