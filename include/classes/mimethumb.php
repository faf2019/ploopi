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

namespace ovensia\ploopi;

use ovensia\ploopi;

/**
 * Gestion des vignettes en fonction des types mime
 *
 * @package ploopi
 * @subpackage thumbmime
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 *
 * Dependance : include/functions/image.php !
 */


// NB: ploopi_resizeimage contient header("Content-Type: image/...");

class mimethumb
{
    /**
     * Passage en mode debug
     */
    private $booDebug = false;

    /**
     * longueur de la vignette
     */
    private $intWidth = 100;

    /**
     * hauteur de la vignette
     */
    private $intHeight = 100;

    /**
     * Coefficient d'agrandissement/reduction à appliquer à l'image
     */
    private $floatCoef = 0;

    /**
     * format d'export de la vignette
     */
    private $strExport = 'png';

    /**
     * extension du fichier input
     */
    private $strExtension;

    /**
     * MimeType, fileType et group du fichier à traiter (si connu)
     */
    private $strTypeMime = '';
    private $strFileType = '';
    private $strGroupType = '';

    /**
     * couleur de la bordure de la vignette (#aaff99, 'transparent', '' (defaut))
     */
    private $strBorderColor = '';

    /**
     * Chemin+nom du fichier à traiter
     */
    private $strPathFile;


    /*
     * Param pour la version CLI.php !
     */
    private $intIdModule;
    private $intIdWorkspace;

    /**
     * Constructeur de la classe
     *
     * @param int $width longueur de la vignette (defaut = 100)
     * @param int $height hauteur de la vignette (defaut = 100)
     * @param int $coef coefficient de redimentionnement de l'image (defaut = 0)
     * @param string $extensionExport format d'export ('jpg', 'jpeg', 'png'(defaut), 'gif')
     * @param str $bgcolor couleur de la bordure de la vignette ('#1f1f1f', 'transparent'(defaut))
     */
    public function __construct($width = '', $height = '', $coef = '', $extensionExport='', $bgcolor='')
    {
        $this->setWith($width);
        $this->setHeight($height);
        $this->setCoef($coef);
        if(!empty($extensionExport)) $this->setExport($extensionExport);
        if(!empty($bgcolor)) $this->setBorder($bgcolor);
    }

    /**
     * Paramétrage de la longueur
     *
     * @param int $width longueur de la vignette
     * @return true
     */
    public function setWith($width)
    {
        $this->intWidth = (is_numeric($width)) ? round($width) : 100;
        return true;
    }

    /**
     * Paramétrage de la hauteur
     *
     * @param int $height hauteur de la vignette
     * @return true
     */
    public function setHeight($height)
    {
        $this->intHeight = (is_numeric($height)) ? round($height) : 100;
        return true;
    }

    /**
     * Paramétrage du coefficient
     *
     * @param int $height hauteur de la vignette
     * @return true
     */
    public function setCoef($coef)
    {
        $this->floatCoef = (is_numeric($coef)) ? $coef : 0;
        return true;
    }

    /**
     * Paramétrage du format d'export
     *
     * @param string $extensionExport format d'export ('jpg', 'jpeg', 'png'(defaut), 'gif')
     * @param int $quality qualité d'export 0<int<100 (uniquement pour les jpg/jpeg)
     * @return true
     */
    public function setExport($extensionExport, $quality = 75)
    {
        $extensionExport = strtolower($extensionExport);
        $this->strExport = (in_array($extensionExport, array('jpg', 'jpeg', 'png', 'gif'))) ? $extensionExport : 'png';

        // Contrôle la qualité UNIQUEMENT pour le jpg/jpeg
        $quality = (is_int($quality) && $quality>0 && $quality<=100) ? $quality : 75;
        // formate le format d'export
        if($this->strExport == 'jpg' || $this->strExport == 'jpeg')
        {
            $this->strExport = array($this->strExport,$quality);
            if($this->strBorderColor == 'transparent') $this->strBorderColor = '#ffffff';
        }

        return true;
    }

    /**
     * Paramétrage de la couleur de bordure (si non indiqué pas de bordure)
     *
     * @param str $bgcolor couleur de la bordure de la vignette (#aaff99, 'transparent'(defaut))
     * @return true
     */
    public function setBorder($bgcolor)
    {
        // Contrôle si le format est #ad95af ou ff55bc ou transparent
        //$this->strBorderColor = (eregi('^#?[0-9a-f]{6}$',$bgcolor) || $bgcolor == 'transparent') ? $bgcolor : '';
        $this->strBorderColor = (preg_match('/^#?[0-9a-f]{6}$/i',$bgcolor) || $bgcolor == 'transparent') ? $bgcolor : '';
        // Ajoute le # si besoin
        if(substr($this->strBorderColor,0,1) != '#' && $this->strBorderColor != 'transparent') $this->strBorderColor = '#'.$this->strBorderColor;

        // Supprime la transparence si c'est du jpg
        if(($this->strExport == 'jpg' || $this->strExport != 'jpeg') && $this->strBorderColor == 'transparent') $this->strBorderColor == '#ffffff';
    }

    /**
     * Paramétrage de idmodule et idworkspave pour la version cli de jodconverter
     *
     * @param idmodule
     * @param idworkspace
     */
    public function setIdmw($idmodule, $idworspace)
    {
        $this->intIdModule = $idmodule;
        $this->intIdWorkspace = $idworspace;
    }

    /**
     * Retourne une vignette du fichier passé en paramètre
     *
     * @param str $pathfile Chemin+nom du fichier à traiter
     * @return vignette
     */
    public function getThumbnail($pathfile)
    {
        global $db;
        $booviewthumb = false;

        $strParam = array();
        if(empty($pathfile) || !file_exists($pathfile)) return false;

        $this->strPathFile = $pathfile;
        $this->strExtension = fs::file_getextension($pathfile);

        // Recupération du type mime
        $sqlMime = $db->query("SELECT `mimetype`, `filetype`, `group` FROM ploopi_mimetype WHERE ext = '{$this->strExtension}'");
        if($db->numrows($sqlMime))
        {
            $fieldMime = $db->fetchrow($sqlMime);
            $this->strTypeMime = $fieldMime['mimetype'];
            $this->strFileType = $fieldMime['filetype'];
            $this->strGroupType = $fieldMime['group'];
        }

        if($this->strGroupType === 'text' || $this->strGroupType === 'shell' || $this->strGroupType === 'xml' || $this->strGroupType === 'unix')
        {
            $this->strExtension = 'txt'; //passe en force le type "txt" (pour feinter les csv, sql, php, etc...)
            $booviewthumb = $this->_thumbJodconverter('ODT');
        }
        else
        {
            switch(strtoupper($this->strExtension))
            {
                // Fichier openOffice
                case 'ODT':
                case 'ODS':
                case 'ODP':
                case 'ODG':
                    $booviewthumb = $this->_thumbOpenOffice();
                break;

                case 'SXW':
                case 'RTF':
                case 'DOC':
                case 'DOCX':
                case 'WPD':
                    $booviewthumb = $this->_thumbJodconverter('ODT');
                break;

                case 'SXC':
                case 'XLS':
                case 'XLSX':
                case 'TSV':
                    $booviewthumb = $this->_thumbJodconverter('ODS');
                break;

                case 'SXI':
                case 'PPT':
                case 'PPTX':
                case 'PPS':
                    $booviewthumb = $this->_thumbJodconverter('ODP');
                break;

                case 'JPG':
                case 'JPEG':
                case 'PNG':
                case 'GIF':
                    $booviewthumb = $this->_thumbImage();
                break;

                case 'SVG':
                    $booviewthumb = $this->_thumbSvg();
                break;

                case 'AVI':
                case 'FLV':
                case 'MP4':
                case '3GP':
                case 'MPEG':
                case 'MPG':
                case 'M2V':
                    $booviewthumb = $this->_thumbVideo();
                break;

                // Imagick !
                default:
                    if(class_exists('Imagick'))
                    {
                        switch(strtoupper($this->strExtension))
                        {
                            case 'PDF':
                            case 'PSD':
                                $this->strPathFile = $this->strPathFile.'[0]';
                                break;
                            case 'CIN':
                                $strParam[] = '$thumb->setImageGamma(1.7);';
                                break;
                            case 'CMYK':
                            case 'CMYKA':
                            case 'RGB':
                            case 'GREY':
                                $strParam[] = '$thumb->setImageDepth(8);';
                                $strParam[] = '$thumb->setSize(640x640);';
                                break;
                            default:
                                break;
                        }
                        $booviewthumb = $this->_thumbImagick($strParam);
                    }
                break;
            }
        }

        // Si pas d'image...
        if(!$booviewthumb) $this->getMimeTypeDefault();
        return true;
    }

    /**
     * recherche de la vignette par defaut en fonction du format + ploopi_resizeimage
     *
     * @return true + vignette ou false
     */
    public function getMimeTypeDefault()
    {
        if(!empty($this->strFileType) && file_exists("./img/mimetypes/thumb_{$this->strFileType}.png"))
        {
            image::resize("./img/mimetypes/thumb_{$this->strFileType}.png", $this->floatCoef, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
            return true;
        }
        image::resize('./img/mimetypes/thumb_default.png', $this->floatCoef, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
        return false;
    }

    /**
     * recherche de l'icone par defaut en fonction du format
     *
     * @return true + icone ou false
     */
    public function getMimeTypeIco()
    {
        @header("Content-Type: image/png");

        if(!empty($this->strFileType) && file_exists("./img/mimetypes/thumb_{$this->strFileType}.png"))
        {
            echo file_get_contents("./img/mimetypes/ico_{$this->strFileType}.png");
            return true;
        }
        echo file_get_contents('./img/mimetypes/ico_default.png');
        return false;
    }

    /**
     * simple resize via ploopi_resizeimage d'une image (jpeg / jpg / gig / png)
     *
     * @return true + vignette ou false
     */
    private function _thumbImage()
    {
        try {
            image::resize($this->strPathFile, $this->floatCoef, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_image <br/>'.$e->getMessage();
                system::kill();
            }
            return false;
        }
        return true;
    }

    /**
     * Convertion de format + génération de vignette via imagick
     *
     * @return true + vignette ou false
     */
    private function _thumbImagick($arrParam = '')
    {
        // Pour les fichier plain-text il faut avoir une extension 'text' sinon Imagick ne le reconnait pas...
        $fileTempo='';

        if($this->strGroupType === 'text')
        {
            $pathTemp = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp'._PLOOPI_SEP;
            if (!is_dir($pathTemp)) fs::makedir($pathTemp);

            $fileTempo = $pathTemp.md5(uniqid(rand(), true)).'.text';

            if(_PLOOPI_SERVER_OSTYPE == 'unix')
                symlink($this->strPathFile,$fileTempo); // Sous nux on crée juste un lien symbolique (+ rapide)
            else
                copy($this->strPathFile,$fileTempo); // Sous win les liens symbolique n'existe que pour vista,2003 ou > ...

            $this->strPathFile = $fileTempo.'[0]';
        }

        // Create Imagick object
        try {

            $thumb = new Imagick($this->strPathFile);
            $color = new ImagickPixel( "white" );

            $thumb->setResolution(72,72);
            $thumb->setImageColorspace(1);
            $thumb->setBackgroundColor($color);

            if(!empty($arrParam))
            {
                foreach ($arrParam as $strParam)
                    eval($strParam);
            }


            if(is_array($this->strExport)) // si array c'est que array(jpg, qualité)
            {
                $thumb->setCompression(Imagick::COMPRESSION_JPEG);
                $thumb->setCompressionQuality($this->strExport[1]);
                $thumb->setFormat('jpg');
            }
            else
            {
                $thumb->setFormat($this->strExport);
            }

            // Détermination de la taille de l'image destination en fonction du coef de redimensionnement
            if (!$this->floatCoef && (!$this->intWidth || !$this->intHeight))
            {
                if ($this->intWidth) $this->setCoef($thumb->getImageWidth()/$this->intWidth);
                if ($this->intHeight && $thumb->getImageHeight()/$this->intHeight > $this->floatCoef) $this->setCoef($thumb->getImageHeight()/$this->intHeight);

                if (!$this->intWidth)    $this->setWith(round($thumb->getImageWidth()/$this->floatCoef));
                if (!$this->intHeight)   $this->setHeight(round($thumb->getImageHeight()/$this->floatCoef));
            }

            if ($this->floatCoef && !$this->intWidth && !$this->intHeight)
            {
                $this->setWith(round($thumb->getImageWidth()/$this->floatCoef));
                $this->setHeight(round($thumb->getImageHeight()/$this->floatCoef));
            }

            $thumb->thumbnailImage($this->intWidth,$this->intHeight,true);

            $intWithTmp = $thumb->getImageWidth();
            $intHeightTmp = $thumb->getImageHeight();

            // Ajoute la bordure
            if(!empty($this->strBorderColor))
            {
                $BorderColor = new ImagickPixel( $this->strBorderColor );
                $thumb->borderImage($BorderColor, ($this->intWidth-$intWithTmp)/2,($this->intHeight-$intHeightTmp)/2);
            }

            @header("Content-Type: image/{$this->strExport}");
            echo $thumb->getImage();

            if(!empty($fileTempo) && file_exists($fileTempo)) unlink($fileTempo);
        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_imagick <br/>'.$e->getMessage();
                system::kill();
            }
            if(!empty($fileTempo) && file_exists($fileTempo)) unlink($fileTempo);
            return false;
        }
        if(!empty($fileTempo) && file_exists($fileTempo)) unlink($fileTempo);
        return true;
    }

    /**
     * Ouverture des fichiers openoffice (zip) + extraction du thumbnail + ploopi_resizeimage
     *
     * @return true + vignette ou false
     */
    private function _thumbOpenOffice()
    {
        try {
            // Pour OpenOffice, les thumbs sont déjà dans le fichier qui est un zip
            $tmpfoldername = md5(uniqid(rand(), true));
            $zip_path = _PLOOPI_PATHDATA._PLOOPI_SEP.'zip'._PLOOPI_SEP.$tmpfoldername._PLOOPI_SEP;
            if (!is_dir($zip_path)) fs::makedir($zip_path);

            $zip = new ZipArchive;
            if ($zip->open($this->strPathFile) === true && $zip->extractTo($zip_path,'Thumbnails/thumbnail.png'))
            {
                $zip->close();

                if(file_exists($zip_path.'Thumbnails/thumbnail.png'))
                {
                    // On mets une couleur de fond car les thumbs inclus sont sur fond transparent !!!
                    image::resize($zip_path.'Thumbnails/thumbnail.png', $this->floatCoef, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor, '#ffffff');
                }
                else
                {
                    if(isset($zip_path) && is_dir($zip_path)) fs::deletedir($zip_path);
                    return false;
                }
            }
            if(isset($zip_path) && is_dir($zip_path)) fs::deletedir($zip_path);
        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_openoffice <br/>'.$e->getMessage();
                system::kill();
            }
            return false;
        }
        return true;
    }

    /**
     * Transformation de document via jodconverter + génération d'une vignette via imagick
     * Taille maxi du fichier 512ko avec jod webservice
     * Limité à UNIX et taille maxi du fichier 2Mo en jod local
     *
     * @param string $formExport format d'export pour jodconverter
     * @return true + vignette ou false
     */
    private function _thumbJodconverter($formExport)
    {
        try {
            $pathTemp = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp'._PLOOPI_SEP;
            if (!is_dir($pathTemp)) fs::makedir($pathTemp);

            $fileTempo = $pathTemp.md5(uniqid(rand(), true)).'.'.$formExport;

            // Besoin pour l'appel en cli
            $system_jobservice = '';

            if(function_exists('param::get'))
            {
                $system_jobservice = param::get('system_jodwebservice', _PLOOPI_MODULE_SYSTEM);
            }
            else // en mode cli.php recup si webservice ou pas dans les param du module
            {
                include_once './include/classes/param.php';
                $objParam = new param();
                $objParam->open(_PLOOPI_MODULE_SYSTEM);
                $system_jobservice = $objParam->getparam('system_jodwebservice');
            }

            if(!empty($system_jobservice) && str::is_url($system_jobservice))
            {
                if(filesize($this->strPathFile) > 1024*1024) return false;

                include_once './include/classes/odf.php';

                $objJOD = new odf_converter($system_jobservice);

                $inputType = fs::getmimetype('file.'.$this->strExtension);
                $outputType = fs::getmimetype('file.'.$formExport);

                $content = $objJOD->convert(file_get_contents($this->strPathFile), $inputType, $outputType);

                if(!empty($content) && substr($content, 0, 12) !== '<html><head>')
                {
                    $handle = fopen($fileTempo,'w');
                    fwrite($handle, $content);
                    fclose($handle);
                }
                else
                    return false;
            }
            else
            {
                if(_PLOOPI_SERVER_OSTYPE != 'unix') return false;
                if(filesize($this->strPathFile) > 2*1024*1024) return false;
                // On verif que le démon est lancé
                exec("ps -f -A | grep -E '^(.*)soffice(.*)accept\=socket\,host\=127\.0\.0\.1\,port\=8100'",$arrResult);
                if(empty($arrResult)) return false; // Pas d'instance du serveur openoffice ! On sort

                // Les fichier type text/plain css et cie doivent etre vu comme des fichier .text
                $fileTempoTXT = '';
                if($this->strGroupType === 'text')
                {
                    $fileTempoTXT = $pathTemp.md5(uniqid(rand(), true)).'.txt';
                    symlink($this->strPathFile,$fileTempoTXT); // Sous nux on crée juste un lien symbolique (+ rapide)
                    $this->strPathFile = $fileTempoTXT;
                }

                // Les pps doivent etre lu comme des ppt (en webservice le type/mime est forcé a ppt)
                $fileTempoPPT = '';
                if(strtoupper($this->strExtension) == 'PPS')
                {
                    $fileTempoPPT = $pathTemp.md5(uniqid(rand(), true)).'.ppt';
                    symlink($this->strPathFile,$fileTempoPPT); // Sous nux on crée juste un lien symbolique (+ rapide)
                    $this->strPathFile = $fileTempoPPT;
                }

                exec('java -jar '.realpath('./lib/jodconverter/lib/jodconverter-cli-2.2.2.jar').' '.$this->strPathFile.' '.$fileTempo.' > /dev/null');

                if(!empty($fileTempoTXT) && is_link($fileTempoTXT)) unlink($fileTempoTXT);
                if(!empty($fileTempoPPT) && is_link($fileTempoPPT)) unlink($fileTempoPPT);
                if(!file_exists($fileTempo)) return false;
            }
            $this->strPathFile = $fileTempo;
            $booResult = $this->_thumbOpenOffice();

            if(file_exists($fileTempo)) unlink($fileTempo);

            if(!$booResult) return false;

        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_jodconverter <br/>'.$e->getMessage();
                system::kill();
            }
            if(!empty($fileTempoPPT) && file_exists($fileTempoPPT)) unlink($fileTempoPPT);
            return false;
        }
        return true;
    }

    /**
     * Conversion des fichier svg via inkscape + génération de la vignette via ploopi_resizeimage
     *
     * @return true + vignette ou false
     */
    private function _thumbSvg()
    {
        if(_PLOOPI_SERVER_OSTYPE != 'unix') return false;

        try {
            $pathTemp = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp'._PLOOPI_SEP;
            if (!is_dir($pathTemp)) fs::makedir($pathTemp);

            $fileTempo = $pathTemp.md5(uniqid(rand(), true)).'.png';
            $strParamExport = " --without-gui -f{$this->strPathFile} -e{$fileTempo} -h400";

            shell_exec("inkscape {$strParamExport}");

            image::resize($fileTempo, $this->floatCoef, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
            if(file_exists($fileTempo)) unlink($fileTempo);

        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_svg <br/>'.$e->getMessage();
                system::kill();
            }
            return false;

        }
        return true;
    }

    /**
     * crée des vignettes vidéo
     *
     * @return true + vignette ou false
     */
    private function _thumbVideo()
    {
        try {
            $pathTemp = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp'._PLOOPI_SEP;
            if (!is_dir($pathTemp)) fs::makedir($pathTemp);

            $fileTempo = $pathTemp.md5(uniqid(rand(), true)).'.png';

            exec("ffmpeg -i {$this->strPathFile} -vcodec png -f image2 -vframes 1 -an -ss 00:00:05 -y $fileTempo",$arrReturn);

            if(!file_exists($fileTempo)) return false;

            if (filesize($fileTempo) == 0)
            {
                unlink($fileTempo);
                return false;
            }

            image::resize($fileTempo, $this->floatCoef, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
            unlink($fileTempo);
            return true;

        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_svg <br/>'.$e->getMessage();
                system::kill();
            }
            return false;
        }
        return true;
    }
}
