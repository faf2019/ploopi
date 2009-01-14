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

function ploopi_resizeimage($imagefile, $coef = 0, $wmax = 0, $hmax = 0, $format = '', $nbcolor = 0, $filename = '', $addBorder = false)
{
    $imagefile_name = basename($imagefile);
    $extension = ploopi_file_getextension($imagefile_name);
    
    // si l'extension du fichier n'est pas "parlante", on tente de récupérer le format dans les infos du fichier
    if (!in_array($extension, array('jpg', 'jpeg', 'png', 'gif')))
    {
        $arrImageInfo = getimagesize($imagefile);
        if (isset($arrImageInfo['mime']))
        {
            if (strstr($arrImageInfo['mime'], 'gif') !== false) $extension = 'gif';
            elseif (strstr($arrImageInfo['mime'], 'png') !== false) $extension = 'png';
            elseif (strstr($arrImageInfo['mime'], 'jpg') !== false) $extension = 'jpg';
            elseif (strstr($arrImageInfo['mime'], 'jpeg') !== false) $extension = 'jpeg';
        }
    }

    // Ouverture de l'image source
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

        default: // format en entrée non supporté
          return false;
        break;
    }

    // Récupération de la taille de l'image
    $w = imagesx($imgsrc);
    $h = imagesy($imgsrc);

    // Pas de coef de redimensionnement ? on essaye de le calculer en fonction de hmax et wmax
    if (!$coef && ($hmax || $wmax))
    {
        if ($wmax) $coef = $w/$wmax;
        if ($hmax && $h/$hmax > $coef) $coef = $h/$hmax;
    }
    
    // Détermination de la taille de l'image destination en fonction du coef de redimensionnement
    if (!$coef) 
    {
        $coef = 1;
        $wdest = $w;
        $hdest = $h;
    } 
    else 
    {   
        $wdest = round($w/$coef);
        $hdest = round($h/$coef);
    }

    // Ajout éventuel d'un fond de couleur  
    if(!empty($addBorder) && $wmax && $hmax)
    {
        $imgdest = imagecreatetruecolor ($wmax, $hmax);

        $arrColor = ploopi_color_hex2rgb($addBorder);
        $background = imagecolorallocate($imgdest, $arrColor[0], $arrColor[1], $arrColor[2]);

        imageFilledRectangle($imgdest, 0, 0, $wmax, $hmax, $background);

        $distX = ($wmax > $wdest) ? (($wmax-$wdest)/2) : 0;
        $distY = ($hmax > $hdest) ? (($hmax-$hdest)/2) : 0;
        imagecopyresampled($imgdest, $imgsrc, $distX, $distY, 0, 0, $wdest, $hdest, $w, $h);
    }
    else
    {
        if ($wdest != $w || $hdest != $h)
        {
            $imgdest = imagecreatetruecolor ($wdest, $hdest);
            imagecopyresampled($imgdest, $imgsrc, 0, 0, 0, 0, $wdest, $hdest, $w, $h);
        }   
        else $imgdest = &$imgsrc;
    }

    if ($nbcolor) imagetruecolortopalette($imgdest, true, $nbcolor);

    if($filename == '')
    {
        ploopi_ob_clean();
        
        if ($format != '') $extension = $format;
        
        header("Content-Type: image/{$extension}");
        header("Content-Disposition: inline; filename=\"{$imagefile_name}\"");

        switch($extension)
        {
            case 'jpg':
            case 'jpeg':
              imagejpeg($imgdest);
            break;

            case 'gif':
                imagepng($imgdest);
            break;

            default:
            case 'png':
                imagepng($imgdest);
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
                    return false;
                break;
            }
        }
        else return false;
    }

    return true;
}


/**
 * Découpe un texte pour qu'il tienne dans une image d'une largeur déterminée
 *
 * @param string $text texte à écrire sur l'image
 * @param int $width largeur de l'image
 * @param int $fontsize taille de la police de caractère
 * @param string $font chemin vers le fichier de la police de caractère
 * @return array tableau décrivant la structure du texte affiché
 */

function ploopi_image_wordwrap($text, $width, $fontsize, $font)
{
    $arrTextLines = split ("\n", $text);
    $arrLines = array();
    $intLineHeight = 0;
    $intTextWidth = 0;
    
    foreach($arrTextLines as $text)
    {
        $arrWords = split (' ', $text);
        $strLine  = '';
        
        foreach ($arrWords as $strWord)
        {
            $arrBox  = imagettfbbox($fontsize, 0, $font, $strLine.$strWord);
            $intSize = $arrBox[4] - $arrBox[0];
            if ($intSize > $width)
            {
                $strLine = trim ($strLine);
                $arrDimensions  = imagettfbbox ($fontsize, 0, $font, $strLine);
                $w = $arrDimensions[4] - $arrDimensions[0];
                if ($w > $intTextWidth) $intTextWidth = $w;
                $arrLines[] = array('text' => trim($strLine), 'width' => $w);
                $strLine = '';
            }
            $strLine .= $strWord.' ';
        }
        
        $strLine = trim($strLine);
        $arrDimensions  = imagettfbbox ($fontsize, 0, $font, $strLine);
        $w = $arrDimensions[4] - $arrDimensions[0];
        if ($w > $intTextWidth) $intTextWidth = $w;
        $arrLines[] = array('text' => trim($strLine), 'width' => $w);
    }
    
    $arrDimensions  = imagettfbbox ($fontsize, 0, $font, 'AJLMYabdfghjklpqry019`@$^&*(,');
    $intLineHeight = $arrDimensions[1] - $arrDimensions[5];
    
    return array ('lineheight' => $intLineHeight, 'textwidth' => $intTextWidth, 'lines' => $arrLines);
}
?>
