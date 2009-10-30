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

include_once './include/functions/string.php';

/**
 * Redimensionne une image et l'enregistre dans un fichier ou la renvoie vers le navigateur
 *
 * @param string $imagefile chemin vers le fichier image
 * @param float $coef ratio de redimensionnement de l'image destination
 * @param int $wmax largeur max de l'image destination
 * @param int $hmax hauteur max de l'image destination
 * @param string/array $format format de l'image destination (jpg, png, gif) ou array(format,qualité), qualité pour le jpg 0<qualite<100
 * @param int $nbcolor taille de la palette de l'image destination
 * @param string $filename nom du fichier image destination, si vide renvoit l'image vers le navigateur
 * @param string (6) $centerwidth couleur de fond hexadécimal RVB à utiliser pour centrer l'image dans $wmax x $hmax
 * @param string (6) $bgcolor couleur de fond hexadécimal RVB pour l'image de base (pour les png de odt qui sont transparent par ex.)
 * @return boolean true si redimensionnement ok
 *
 * @link http://fr.php.net/manual/fr/ref.image.php
 */

function ploopi_resizeimage($imagefile, $coef = 0, $wmax = 0, $hmax = 0, $format = '', $nbcolor = 0, $filename = '', $centerwidthcolor = false, $bgcolor = false)
{
    $imagefile_name = basename($imagefile);
    
    $qualite = 75;
    $originaltransparentcolor = -1;
    
    if(is_array($format))
    {
        $qualite = (is_int($format[1])) ? $format[1] : 75;
        $format = strtolower($format[0]);
    }
    else
        $format = strtolower($format);

    $extension = mime_content_type($imagefile); //FIXME Cette fonction est devenue obsolète car Fileinfo fournit la même fonctionnalité (et bien plus) d'une façon plus propre (Mais intégré en php 5.3.0 et debian au jour d'aujourd'hui est en 5.2...). 
      
    // Ouverture de l'image source
    switch($extension)
    {
        case 'image/jpg':
        case 'image/jpeg':
        {
          $extension = 'jpg';
          $imgsrc = ImageCreateFromJPEG($imagefile);
        }
        break;

        case 'image/png':
        {
          $extension = 'png';
          $imgsrc = ImageCreateFromPng($imagefile);
        }
        break;

        case 'image/gif':
        {
          $extension = 'gif';
          $imgsrc = imagecreatefromgif($imagefile);
          $originaltransparentcolor = imagecolortransparent($imgsrc);
        }
        break;
        
        default: // format en entrée non supporté
          return false;
        break;
    }
    
    // Récupération de la taille de l'image
    $w = imagesx($imgsrc);
    $h = imagesy($imgsrc);

    // Coloration du background (si ça sert à quelquechose !)
    if(!empty($bgcolor) && ($extension == 'png' || $extension == 'gif'))
    {
        $arrColor = ploopi_color_hex2rgb($bgcolor);
        
        $imgtmp = imagecreatetruecolor ($w, $h);
        
        // On colore le fond avec $bgcolor
        imagefill($imgtmp, 0, 0, imagecolorallocate($imgtmp, $arrColor[0], $arrColor[1], $arrColor[2]));
        
        // On mets l'image par dessus
        imagecopy($imgtmp,$imgsrc,0,0,0,0,$w,$h);
        imagedestroy($imgsrc);
        $imgsrc = &$imgtmp;
    }
    
    // Pas de coef de redimensionnement ? on essaye de le calculer en fonction de hmax et wmax
    if (!$coef && ($hmax || $wmax))
    {
        if ($wmax) $coef = ($w/$wmax);
        if ($hmax && $h/$hmax > $coef) $coef = ($h/$hmax);
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

    if($wmax && $wdest > $wmax) $wdest = $wmax;
    if($hmax && $hdest > $hmax) $hdest = $hmax;
    
    // Centrage de l'image demandé avec pourtour de couleur $centerwidthcolor
    if(!empty($centerwidthcolor))
    {
        $distX = ($wmax > $wdest) ? round(($wmax-$wdest)/2) : 0;
        $distY = ($hmax > $hdest) ? round(($hmax-$hdest)/2) : 0;
        
        
        $imgdest = imagecreatetruecolor ((($wmax) ? $wmax : $wdest), (($hmax) ? $hmax : $hdest));

        if($centerwidthcolor == 'transparent' && ($format == 'png' || $format == 'gif'))
        {
            
            if($format == 'gif')
            {   
                if($originaltransparentcolor > -1)
                {
                    $transparentcolor = imagecolorsforindex($imgdest, $originaltransparentcolor);
                    $transparent = imagecolorallocate($imgdest,$transparentcolor['red'],$transparentcolor['green'],$transparentcolor['blue']);
                }
                else
                {
                    $transparent = imagecolorallocate($imgdest,255,0,255);
                }
                imagefill($imgdest, 0, 0, $transparent);
                imagecolortransparent($imgdest,$transparent);
            }
            else
            {
                imagealphablending($imgdest, false);
                $transparent = imagecolorallocatealpha($imgdest, 0, 0, 0, 127);
                imagefill($imgdest, 0, 0, $transparent);
                imagesavealpha($imgdest, true);
            }
        }
        else
        {
            if($centerwidthcolor == 'transparent') $centerwidthcolor = '#ffffff'; // on demande transparent mais pas png ni gif... donc inutile !
            
            $arrColor = ploopi_color_hex2rgb($centerwidthcolor);
            $background = imagecolorallocate($imgdest, $arrColor[0], $arrColor[1], $arrColor[2]);
            imageFilledRectangle($imgdest, 0, 0, (($wmax) ? $wmax : $wdest), (($hmax) ? $hmax : $hdest), $background);
        }
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

        // Détermination du format de sortie
        if ($format != '') $extension = $format;
        if (!in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) $extension = 'png';

        if($extension == 'png')
        {
            imagealphablending($imgdest, false);
            imagesavealpha($imgdest, true);        
        }
        
        header("Content-Type: image/{$extension}");
        header("Content-Disposition: inline; filename=\"{$imagefile_name}\"");

        switch($extension)
        {
            case 'jpg':
            case 'jpeg':
              imagejpeg($imgdest, null, $qualite);
            break;

            case 'gif':
                imagegif($imgdest);
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
        
        // Détermination du format de sortie
        $extension = ($format == '') ? ploopi_file_getextension($filename) : $format;
        if (!in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) $extension = 'png';
        
        if($extension == 'png')
        {
            imagealphablending($imgdest, false);
            imagesavealpha($imgdest, true);        
        }
        
        if (is_writable($path) && (!$exists || ($exists && is_writable($filename))))
        {
            switch($extension)
            {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($imgdest, $filename, $qualite);
                break;

                case 'png':
                    imagepng($imgdest, $filename);
                break;

                case 'gif':
                    imagegif($imgdest, $filename);
                break;

                default:
                    imagedestroy($imgdest);
                    imagedestroy($imgsrc);
                    
                    return false;
                break;
            }
        }
        else
        {
            imagedestroy($imgdest);
            imagedestroy($imgsrc);
            
            return false;
        } 
    }
    
    imagedestroy($imgdest);
    imagedestroy($imgsrc);
    
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
