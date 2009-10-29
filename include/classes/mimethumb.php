<?
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
 * Gestion des vignettes en fonction des types mime
 *
 * @package ploopi
 * @subpackage thumbmime
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

include_once './include/functions/image.php';

// NB: ploopi_resizeimage contient header("Content-Type: image/...");

class mimethumb
{
    /**
     * longueur de la vignette
     */
    private $intWidth = 100;
    
    /**
     * hauteur de la vignette
     */
    private $intHeight = 100;
    
    /**
     * format d'export de la vignette
     */
    private $strExport = 'png';
    
    /**
     * extension du fichier input
     */
    private $strExtension;
    
    /**
     * couleur de la bordure de la vignette (#aaff99, 'transparent', '' (defaut))
     */
    private $strBorderColor = '';
    
    /**
     * Chemin+nom du fichier à traiter
     */
    private $strPathFile;
    
    
    /**
     * Passage en mode debug
     */
    private $booDebug = false;
    
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
     * @param string $extensionExport format d'export ('jpg', 'jpeg', 'png'(defaut), 'gif')
     * @param str $bgcolor couleur de la bordure de la vignette ('#1f1f1f', 'transparent'(defaut))
     */
    public function __construct($width = '', $height = '', $extensionExport='', $bgcolor='')
    {
        if(!empty($width)) $this->setWith($width);
        if(!empty($height)) $this->setHeight($height);
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
        $this->intWidth = (is_int($width)) ? $width : 100;
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
        $this->intHeight = (is_int($height)) ? $height : 100;
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
        $this->strBorderColor = (eregi('^#?[0-9a-f]{6}$',$bgcolor) || $bgcolor == 'transparent') ? $bgcolor : '';
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
        $strParam = array();
        if(empty($pathfile) || !file_exists($pathfile)) return false;
        
        $this->strPathFile = $pathfile;
        $this->strExtension = (!empty($extension)) ? $extension : ploopi_file_getextension($pathfile);
        
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
                // if(strtoupper($this->strExtension) == 'SQL') { $this->strExtension = 'txt'; }
                $booviewthumb = $this->_thumbJodconverter('ODT');
            break;
            
            case 'SXC':
            case 'XLS':
            case 'XLSX':
            case 'CSV':
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
                        // Les cases suivant n'ont pas de break. Ce sont des params pour imagick !
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
        global $db;
        $sqlMime = $db->query("SELECT filetype FROM ploopi_mimetype WHERE ext = '{$this->strExtension}'");
        if($db->numrows($sqlMime))
        {
            $fieldMime = $db->fetchrow($sqlMime);
            if(file_exists("./img/mimetypes/thumb_{$fieldMime['filetype']}.png"))
            {
                ploopi_resizeimage("./img/mimetypes/thumb_{$fieldMime['filetype']}.png", 0, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
                return true;
            }
        }
        ploopi_resizeimage('./img/mimetypes/thumb_default.png', 0, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
        return false;
    }

    /**
     * recherche de l'icone par defaut en fonction du format
     *
     * @return true + icone ou false
     */
    public function getMimeTypeIco()
    {
        global $db;
        
        @header("Content-Type: image/png");
        
        $sqlMime = $db->query("SELECT filetype FROM ploopi_mimetype WHERE ext = '{$this->strExtension}'");
        if($db->numrows($sqlMime))
        {
            $fieldMime = $db->fetchrow($sqlMime);
            if(file_exists("./img/mimetypes/ico_{$fieldMime['filetype']}.png"))
            {
                echo file_get_contents("./img/mimetypes/ico_{$fieldMime['filetype']}.png");
                return true;
            }
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
            ploopi_resizeimage($this->strPathFile, 0, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_image <br/>'.$e->getMessage();
                ploopi_die();
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
        global $db;
        
        $fileTempo='';
        
        $sqlMime = $db->query("SELECT mimetype FROM ploopi_mimetype WHERE ext = '{$this->strExtension}'");
        if($db->numrows($sqlMime))
        {
            $fieldMime = $db->fetchrow($sqlMime);
            
            if(in_array($fieldMime['mimetype'], array('text/plain', 'text/x-sql')))
            {
                $pathTemp = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp'._PLOOPI_SEP;
                if (!is_dir($pathTemp)) ploopi_makedir($pathTemp);
                
                $fileTempo = $pathTemp.md5(uniqid(rand(), true)).'.text';
                copy($this->strPathFile,$fileTempo);
                $this->strPathFile = $fileTempo.'[0]';
            }
        }
        
        // Create Imagick object
        try {
            $thumb = new Imagick($this->strPathFile);
            
            $thumb->setResolution(72,72);
            $thumb->setImageColorspace(1);
            $thumb->setBackgroundColor('#ffffff');
            
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
            
            $thumb->thumbnailImage($this->intWidth,$this->intHeight,true);
            
            $intWithTmp = $thumb->getImageWidth();
            $intHeightTmp = $thumb->getImageHeight();
            
            // Ajoute la bordure
            if(!empty($this->strBorderColor))
                $thumb->borderImage($this->strBorderColor,($this->intWidth-$intWithTmp)/2,($this->intHeight-$intHeightTmp)/2);
                
            @header("Content-Type: image/{$this->strExport}");
            echo $thumb->getImage();
            
            if(!empty($fileTempo) && file_exists($fileTempo)) unlink($fileTempo);
        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_imagick <br/>'.$e->getMessage();
                ploopi_die();
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
            if (!is_dir($zip_path)) ploopi_makedir($zip_path);
    
            $zip = new ZipArchive;
            if ($zip->open($this->strPathFile) === true && $zip->extractTo($zip_path,'Thumbnails/thumbnail.png'))
            {
                $zip->close();

                if(file_exists($zip_path.'Thumbnails/thumbnail.png'))
                {
                    // On mets une couleur de fond car les thumbs inclus sont sur fond transparent !!!
                    ploopi_resizeimage($zip_path.'Thumbnails/thumbnail.png', 0, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor, '#ffffff');
                }
                else
                {
                    if(isset($zip_path) && is_dir($zip_path)) ploopi_deletedir($zip_path);
                    return false;                    
                }
            }
            if(isset($zip_path) && is_dir($zip_path)) ploopi_deletedir($zip_path);
        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_openoffice <br/>'.$e->getMessage();
                ploopi_die();
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
            if (!is_dir($pathTemp)) ploopi_makedir($pathTemp);
            
            $fileTempo = $pathTemp.md5(uniqid(rand(), true)).'.'.$formExport;

            // Besoin pour l'appel en cli
            $doc_jobservice = '';
            if(isset($_SESSION))
            {
                $doc_jobservice = $_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_jodwebservice'];
            }
            else // en mode cli.php recup si webservice ou pas dans les param du module
            {
                include_once './include/classes/param.php';
                $objParam = new param();
                $objParam->open($this->intIdModule, $this->intIdWorkspace);
                $doc_jobservice = $objParam->getparam('doc_jodwebservice');
            }

            if(!empty($doc_jobservice) && ploopi_is_url($doc_jobservice))
            {
                if(filesize($this->strPathFile) > 512*1024) return false;
                
                include_once './include/classes/odf.php';
                
                $objJOD = new odf_converter($_SESSION['ploopi']['modules'][$_SESSION['ploopi']['moduleid']]['doc_jodwebservice']);
                $inputType = ploopi_getmimetype('file.'.$this->strExtension);
                $outputType = ploopi_getmimetype('file.'.$formExport);

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

                $fileTempoPPT = '';
                // Les pps doivent etre lu comme des ppt (en webservice le type/mime est forcé a ppt)
                if(strtoupper($this->strExtension) == 'PPS')
                {
                    $fileTempoPPT = $pathTemp.md5(uniqid(rand(), true)).'.ppt';
                    copy($this->strPathFile,$fileTempoPPT);
                    $this->strPathFile = $fileTempoPPT;
                }
                
                exec("ps -A -f | grep -E '^(.*)soffice(.*)accept\=socket\,host\=127\.0\.0\.1\,port\=8100'",$arrResult);
                if(empty($arrResult))
                {
                    // Ouverture du demon soffice pour jodconverter
                    exec('soffice -headless -accept="socket,host=127.0.0.1,port=8100;urp;" -nofirststartwizard -norestore > /dev/null');
                    sleep(2); // attend que le demon se lance (le 1er démarrage "a froid" peut etre long...)
                    exec("ps -A -f | grep -E '^(.*)soffice(.*)accept\=socket\,host\=127\.0\.0\.1\,port\=8100'",$arrResult);
                    if(empty($arrResult)) return false;
                }
                
                exec('java -jar '.realpath('./lib/jodconverter/lib/jodconverter-cli-2.2.2.jar').' '.$this->strPathFile.' '.$fileTempo);
                
                if(!empty($fileTempoPPT) && file_exists($fileTempoPPT)) unlink($fileTempoPPT);
                
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
                ploopi_die();
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
            if (!is_dir($pathTemp)) ploopi_makedir($pathTemp);
                        
            $fileTempo = $pathTemp.md5(uniqid(rand(), true)).'.png';
            $strParamExport = " -f{$this->strPathFile} -e{$fileTempo} -d72 --without-gui";
            
            exec('inkscape'.$strParamExport);
            
            ploopi_resizeimage($fileTempo, 0, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
            if(file_exists($fileTempo)) unlink($fileTempo);
           
        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_svg <br/>'.$e->getMessage();
                ploopi_die();
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
            if (!is_dir($pathTemp)) ploopi_makedir($pathTemp);
                        
            $fileTempo = $pathTemp.md5(uniqid(rand(), true)).'.png';
            
            system("ffmpeg -i {$this->strPathFile} -vcodec png -vframes 1 -an -ss 00:00:05 -y $fileTempo",$retval);
                
            if (filesize($fileTempo) == 0) 
            {
                unlink($fileTempo);
                return false;
            }
        
            if ($retval == 0)
            { 
                ploopi_resizeimage($fileTempo, 0, $this->intWidth, $this->intHeight, $this->strExport, 0, '', $this->strBorderColor);
                unlink($fileTempo);
                return true;
            }
            return false;
            
        } catch (Exception $e) {
            if($this->booDebug)
            {
                echo 'thumb_svg <br/>'.$e->getMessage();
                ploopi_die();
            }
            return false;
        }
        return true;
    }
}
?>