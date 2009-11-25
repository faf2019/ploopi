<?
/*
  Copyright (c) 2006-2007 Sylvain BRISON (génération image)
  Copyright (c) 2009 Drew Phillips (gestion du son)
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
 * affichage de captcha
 *
 * @package ploopi
 * @subpackage captcha
 * @copyright Sylvain BRISON, Drew Phillips, HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * NB : CES CLASSES SONT TRES FORTEMENT INSPIREES DU TRAVAIL EFFECTUE SUR :
 *  
 *      * Cryptographp v1.4 (partie captcha)
 *      (c) 2006-2007 Sylvain BRISON
 *      www.cryptographp.com
 *      cryptographp@alphpa.com
 *      Licence CeCILL V2
 *       
 *      * Securimage 2.0 BETA (November 15, 2009)
 *      (c) 2009 Drew Phillips
 *      www.phpcaptcha.org
 *      drew@drew-phillips.com
 *      Licence GNU General Public License (GPL) v.2.1
 */

class captcha
{
    // -------------------------------------
    // Configuration de la session
    // -------------------------------------

    private $captchasession; // nom de la dimension supplémentaire pour le tableau en session
    
    // -------------------------------------
    // Configuration du fond du cryptogramme
    // -------------------------------------
    
    private $captchawidth   = 160;  // Largeur du captcha (en pixels)
    private $captchaheight  = 50;   // Hauteur du captcha (en pixels)
    
    private $bgR  = 255;            // Couleur du fond au format RGB: Red (0->255)
    private $bgG  = 255;            // Couleur du fond au format RGB: Green (0->255)
    private $bgB  = 255;            // Couleur du fond au format RGB: Blue (0->255)
    
    private $bgclear = true;        // Fond transparent (true/false)
    
    private $bgimg = '';            // Le fond du cryptogramme peut-être une image  
                                    // PNG, GIF ou JPG. Indiquer le fichier image
                                    // Exemple: $fondimage = 'photo.gif';
                                    // L'image sera redimensionnée si nécessaire
                                    // pour tenir dans le cryptogramme.
                                    // Si vous indiquez un répertoire plutôt qu'un 
                                    // fichier l'image sera prise au hasard parmi 
                                    // celles disponibles dans le répertoire

    private $bgframe = true;        // Ajoute un cadre de l'image (true/false)


    // ----------------------------
    // Configuration des caractères
    // ----------------------------
    
    // Couleur de base des caractères
    
    private $charR = 0;     // Couleur des caractères au format RGB: Red (0->255)
    private $charG = 0;     // Couleur des caractères au format RGB: Green (0->255)
    private $charB = 0;     // Couleur des caractères au format RGB: Blue (0->255)
    
    private $charcolorrnd = true;       // Choix aléatoire de la couleur.
    private $charcolorrndlevel = 2;     // Niveau de clarté des caractères si choix aléatoire (0->4)
                                        // 0: Aucune sélection
                                        // 1: Couleurs très sombres (surtout pour les fonds clairs)
                                        // 2: Couleurs sombres
                                        // 3: Couleurs claires
                                        // 4: Couleurs très claires (surtout pour fonds sombres)
    
    private $charclear = 10;        // Intensité de la transparence des caractères (0->127)
                                    // 0=opaques; 127=invisibles
                                    // interessant si vous utilisez une image $bgimg
                                    // Uniquement si PHP >=3.2.1
    
    // Polices de caractères
    
    private $tfont = array(         // Ajoutez autant de lignes que vous voulez
        'ComickBook_Simple.ttf',    // Les polices seront aléatoirement utilisées.
        'Alanden_.ttf',             // Vous devez copier les fichiers correspondants
        'SCRAWL.TTF'                // dans ./img/fonts/  
    );                                                                  
    
    
    // Caracteres autorisés
    // Attention, certaines polices ne distinguent pas (ou difficilement) les majuscules 
    // et les minuscules. Certains caractères sont faciles à confondre, il est donc
    // conseillé de bien choisir les caractères utilisés.
    
    private $captchaeasy = true;                // Création de cryptogrammes "faciles à lire" (true/false)
                                                // composés alternativement de consonnes et de voyelles.
    
    private $charel = 'abcdefhklmnprtwxyzABCDEFGHKLMNPRTWXYZ234569';  // Caractères autorisés
    
    private $charelc = 'BCDFGHKLMNPRTVWXZ';     // Consonnes utilisées si $crypteasy = true
    private $charelv = 'AEIOUY';                // Voyelles utilisées si $crypteasy = true
    
    private $difuplow = false;      // Différencie les Maj/Min lors de la saisie du code (true, false)
    
    private $charnbmin = 4;         // Nb minimum de caracteres dans le cryptogramme
    private $charnbmax = 5;         // Nb maximum de caracteres dans le cryptogramme
    
    private $charspace = 20;        // Espace entre les caracteres (en pixels)
    private $charsizemin = 14;      // Taille minimum des caractères
    private $charsizemax = 16;      // Taille maximum des caractères
    
    private $charanglemax  = 25;    // Angle maximum de rotation des caracteres (0-360)
    private $charup   = true;       // Déplacement vertical aléatoire des caractères (true/false)
    
    // Effets supplémentaires
    
    private $captchagaussianblur = false;   // Transforme l'image finale en brouillant: méthode Gauss (true/false)
                                            // uniquement si PHP >= 5.0.0
    private $captchagrayscal = false;       // Transforme l'image finale en dégradé de gris (true/false)
    
    // ----------------------
    // Configuration du bruit
    // ----------------------
    
    private $noisepxmin = 10;       // Bruit: Nb minimum de pixels aléatoires
    private $noisepxmax = 10;       // Bruit: Nb maximum de pixels aléatoires
    
    private $noiselinemin = 1;      // Bruit: Nb minimum de lignes aléatoires
    private $noiselinemax = 1;      // Bruit: Nb maximum de lignes aléatoires
    
    private $nbcirclemin = 1;       // Bruit: Nb minimum de cercles aléatoires 
    private $nbcirclemax = 1;       // Bruit: Nb maximim de cercles aléatoires
    
    private $noisecolorchar  = 3;   // Bruit: Couleur d'ecriture des pixels, lignes, cercles: 
                                    // 1: Couleur d'écriture des caractères
                                    // 2: Couleur du fond
                                    // 3: Couleur aléatoire
                           
    private $brushsize = 1;         // Taille d'ecriture du princeau (en pixels) 
                                    // de 1 à 25 (les valeurs plus importantes peuvent provoquer un 
                                    // Internal Server Error sur certaines versions de PHP/GD)
                                    // Ne fonctionne pas sur les anciennes configurations PHP/GD
    
    private $noiseup = false;       // Le bruit est-il par dessus l'ecriture (true) ou en dessous (false) 
    
    // --------------------------------
    // Configuration système & sécurité
    // --------------------------------
        
    private $captchaformat = 'png'; // Format du fichier image généré "GIF", "PNG" ou "JPG"
                                    // Si vous souhaitez un fond transparent, utilisez "PNG" (et non "GIF")
    
    private $captchausetimer = 0;       // Temps (en seconde) avant d'avoir le droit de regénérer un cryptogramme
    
    private $usertimererror = 3;         // Action à réaliser si le temps minimum n'est pas respecté:
                                        // 1: Ne rien faire, ne pas renvoyer d'image.
                                        // 2: L'image renvoyée est "images/erreur2.png" (vous pouvez la modifier)
                                        // 3: Le script se met en pause le temps correspondant (attention au timeout
                                        //    par défaut qui coupe les scripts PHP au bout de 30 secondes)
                                        //    voir la variable "max_execution_time" de votre configuration PHP
    
    private $captchausemax = 1000;      // Nb maximum de fois que l'utilisateur peut générer le cryptogramme
                                        // Si dépassement, l'image renvoyée est "images/erreur1.png"
                                        // PS: Par défaut, la durée d'une session PHP est de 180 mn, sauf si 
                                        // l'hebergeur ou le développeur du site en ont décidé autrement... 
                                        // Cette limite est effective pour toute la durée de la session. 
                          
    private $captchaoneuse = false;     // Si vous souhaitez que la page de verification ne valide qu'une seule 
                                        // fois la saisie en cas de rechargement de la page indiquer "true".
                                        // Sinon, le rechargement de la page confirmera toujours la saisie.                          

    // --------------------------------
    // Variable internes NE PA MODIFIER
    // --------------------------------
    
    private $img, $ink, $xvariation, $charnb, $tword, $bg, $brush;
        
    
    /**
     * Contructeur de la classe.
     * 
     * @param array voir la description des attributs (optionnel)
     * @param string nom de la session => $_SESSION['ploopi']['captcha'][$param] (optionnel)
     * @return captcha
     */
    function __construct()
    {
        // passage des variables via l'éventuel array passé au constructeur
        if(func_num_args())
        {
            $arg = func_get_arg(0);
            if(is_array($arg)) foreach($arg as $var => $value) if(isset($this->{$var})) $this->{$var} = $value;
        }

        // Possibilité de modifier la session d'enregistrement
        if(func_num_args()==2)
        {
            $arg = func_get_arg(1);
            if(is_string($arg))
            {
                if(!isset($_SESSION['ploopi']['captcha'][$arg])) $_SESSION['ploopi']['captcha'][$arg] = '';
                $this->captchasession = &$_SESSION['ploopi']['captcha'][$arg];
            }
        }
        else
        {
            if(!isset($_SESSION['ploopi']['captcha'])) $_SESSION['ploopi']['captcha'] = '';
            $this->captchasession = &$_SESSION['ploopi']['captcha'];
        }
    }
    
    /**
     * Contrôle de sécurité :
     * - Nb de captcha autorisé
     * - delais entre 2 générations respecté 
     * 
     * @return empty or image
     */
    private function secure()
    {
        // Vérifie si l'utilisateur a le droit de (re)générer un cryptogramme
        if (isset($this->captchasession['captchacptuse']) && $this->captchasession['captchacptuse'] >= $this->captchausemax)
        {
           ploopi_ob_clean();
           header("Content-type: image/png");
           readfile('./img/captcha/erreur1.png'); 
           exit;
        }
        
        if(isset($this->captchasession['captchatime']))
        {
            // Autorisation de refresh toutes les $this->captchausetimer seconde
            $delai = time() - $this->captchasession['captchatime'];
            if ($delai < $this->captchausetimer) 
            { 
                switch ($this->usertimererror)
                {
                    case 2:     // Image message d'erreur 
                        ploopi_ob_clean();
                        header("Content-type: image/png");
                        readfile('./img/captcha/erreur2.png'); 
                        exit;
                    case 3:     // Fait une pause
                        sleep ($this->captchausetimer - $delai);
                        break; 
                    case 1:     // Quitte le script sans rien faire      
                    default:
                        exit;  
                }
            }
        }
    }

    /**
     * Génération du code captcha
     * 
     * @return empty
     */
    private function createCode()
    {
        // on controle que tout est ok
        $this->secure();
        
        $this->captchasession['code'] = $this->captchasession['codesound'] = '';
        
        $pair = rand(0,1);
        $this->charnb = rand($this->charnbmin,$this->charnbmax);
        
        for ($i=1;$i<= $this->charnb; $i++)
        {              
            if ($this->captchaeasy) 
                $this->captchasession['code'] .= (!$pair) ? $this->charelc{rand(0,strlen($this->charelc)-1)} : $this->charelv{rand(0,strlen($this->charelv)-1)};
            else 
                $this->captchasession['code'] .= $this->charel{rand(0,strlen($this->charel)-1)};
            
            $pair=!$pair;
        }
        $this->captchasession['codesound'] = strtoupper($this->captchasession['code']);
    }
    
    /**
     * Méthode générant l'image du texte lettre par lettre.
     * Permet ensuite de calculer le centrage de l'image des lettres
     * 
     * @return empty
     */
    private function ecriture()
    {
        $this->ink = imagecolorallocatealpha($this->img, $this->charR, $this->charG, $this->charB, $this->charclear);
        
        $x = $this->xvariation;
        
        for ($i=1; $i <= $this->charnb; $i++) 
        {       
            if ($this->charcolorrnd) // Choisit des couleurs au hasard
            {   
                $ok = false;
                do {
                    $rndR = rand(0,255); $rndG = rand(0,255); $rndB = rand(0,255);
                    $rndcolor = $rndR+$rndG+$rndB;
                    switch ($this->charcolorrndlevel) 
                    {
                        case 1  : if ($rndcolor<200) $ok=true; break; // tres sombre
                        case 2  : if ($rndcolor<400) $ok=true; break; // sombre
                        case 3  : if ($rndcolor>500) $ok=true; break; // claires
                        case 4  : if ($rndcolor>650) $ok=true; break; // très claires
                        default : $ok=true;               
                    }
                } while (!$ok);
                  
                $rndink = imagecolorallocatealpha($this->img, $rndR, $rndG, $rndB, $this->charclear);
            }  
        
            $lafont="./img/fonts/".$this->tword[$i]['font'];
            imagettftext($this->img, $this->tword[$i]['size'], $this->tword[$i]['angle'], $x, $this->tword[$i]['y'], $this->charcolorrnd ? $rndink : $this->ink, $lafont, $this->tword[$i]['element']);
        
            $x += $this->charspace;
        } 
    }

    /**
     * Méthode permettant de déterminer la couleur du bruit et la forme du pinceau
     * 
     * @return empty
     */
    function noisecolor()
    {
        $this->brushsize = 2;
        switch ($this->noisecolorchar) 
        {
             case 1  : 
                $noisecol = $this->ink; 
                break;
             case 2  : 
                 $noisecol = $this->bg; 
                 break;
             case 3  : 
             default : 
                 $noisecol = imagecolorallocate($this->img, rand(0,255), rand(0,255), rand(0,255));
                 break;               
        }
        
        $brushsize = ($this->brushsize > 1) ? rand(1,$this->brushsize) : $this->brushsize;
        
        if ($brushsize > 1 && function_exists('imagesetbrush')) 
        {
            $this->brush = imagecreatetruecolor($this->brushsize, $this->brushsize);
            imagefill($this->brush, 0, 0, $noisecol);
            imagesetbrush($this->img, $this->brush);
            $noisecol = IMG_COLOR_BRUSHED;
        }
        
        return $noisecol;
    }


    /**
     * Ajout de bruits: point, lignes et cercles aléatoires
     * 
     * @return empty
     */
    function bruit()
    {
        $nbpx = rand($this->noisepxmin, $this->noisepxmax);
        $nbline = rand($this->noiselinemin, $this->noiselinemax);
        $nbcircle = rand($this->nbcirclemin, $this->nbcirclemax);
        
        for ($i=1; $i < $nbpx; $i++) imagesetpixel($this->img, rand(0,$this->captchawidth-1), rand(0,$this->captchaheight-1), $this->noisecolor());
        for ($i=1; $i <= $nbline; $i++) imageline($this->img, rand(0,$this->captchawidth-1), rand(0,$this->captchaheight-1), rand(0,$this->captchawidth-1), rand(0,$this->captchaheight-1), $this->noisecolor());
        for ($i=1; $i <= $nbcircle; $i++) imagearc($this->img, rand(0,$this->captchawidth-1), rand(0,$this->captchaheight-1), $rayon = rand(5,$this->captchawidth/3), $rayon, 0, 360, $this->noisecolor());
    } 

    /**
     * Génère l'image CAPTCHA
     * 
     * @return image
     */
    public function createCaptcha()
    {
        
        // Création du code captcha
        $this->createCode();
        
        // Création du cryptogramme temporaire
        $imgtmp = imagecreatetruecolor($this->captchawidth, $this->captchaheight);
        $blank  = imagecolorallocate($imgtmp, 255, 255, 255);
        $black   = imagecolorallocate($imgtmp, 0, 0, 0);
        imagefill($imgtmp, 0, 0, $blank);

        // on fabrique une image avec les lettres pour calculer le recadrage et eviter les lettres qui dépassent du cadre
        $i = 1;
        $x = 10;
        $this->tword = '';
        foreach(str_split($this->captchasession['code'],1) as $letter) 
        {              
            $this->tword[$i]['element'] = $letter;
            $this->tword[$i]['font'] =  $this->tfont[array_rand($this->tfont,1)];
            $this->tword[$i]['angle'] = (rand(1,2) == 1) ? rand(0,$this->charanglemax) : rand(360-$this->charanglemax,360);
            $this->tword[$i]['size'] = rand($this->charsizemin,$this->charsizemax);
            $this->tword[$i]['y'] = ($this->charup ? ($this->captchaheight/2) + rand(0,($this->captchaheight/5)) : ($this->captchaheight/1.5));
             
            $lafont="./img/fonts/".$this->tword[$i]['font'];
            imagettftext($imgtmp, $this->tword[$i]['size'], $this->tword[$i]['angle'], $x, $this->tword[$i]['y'], $black, $lafont, $this->tword[$i]['element']);
        
            $x += $this->charspace;
            $i++;
         } 
         
         // Calcul du racadrage horizontal du cryptogramme temporaire
        $x = 0;
        $xbegin = 0;
        while (($x < $this->captchawidth) && (!$xbegin)) 
        {
            $y=0;
            while (($y < $this->captchaheight) && (!$xbegin)) 
            {
                if (imagecolorat($imgtmp, $x, $y) != $blank) $xbegin = $x;
                $y++;
            }
            $x++;
        } 

        $x = $this->captchawidth - 1;
        $xend = 0;
        while (($x > 0) && (!$xend)) 
        {
            $y=0;
            while (($y < $this->captchaheight) && (!$xend)) 
            {
                if (imagecolorat($imgtmp, $x, $y) != $blank) $xend = $x;
                $y++;
            }
            $x--;
        } 
             
        $this->xvariation = round(($this->captchawidth/2)-(($xend - $xbegin)/2));
        imagedestroy ($imgtmp);
         
        
        // Création du cryptogramme définitif
        // Création du fond
        $this->img = imagecreatetruecolor($this->captchawidth, $this->captchaheight); 

        if ($this->bgimg && is_dir($this->bgimg)) 
        {
            $dh  = opendir($this->bgimg);
            
            while (false !== ($filename = readdir($dh))) 
                if(eregi(".[gif|jpg|png]$", $filename)) $files[] = $filename;
                
            closedir($dh);
            $this->bgimg = $this->bgimg.'/'.$files[array_rand($files,1)];
        }
        
        if ($this->bgimg) 
        {
            list($getwidth, $getheight, $gettype, $getattr) = getimagesize($this->bgimg);
            switch ($gettype) 
            {
                case "1": $imgread = imagecreatefromgif($bgimg); break;
                case "2": $imgread = imagecreatefromjpeg($bgimg); break;
                case "3": $imgread = imagecreatefrompng($bgimg); break;
            }
            imagecopyresized ($this->img, $imgread, 0, 0, 0, 0, $this->captchawidth, $this->captchaheight, $getwidth, $getheight);
            imagedestroy ($imgread);
        }
        else 
        {
            $bg = imagecolorallocate($this->img, $this->bgR, $this->bgG, $this->bgB);
            imagefill($this->img, 0, 0, $bg);
            if ($this->bgclear) imagecolortransparent($this->img, $bg);
        }
        
        if ($this->noiseup) 
        {
            $this->ecriture();
            $this->bruit();
        }
        else
        {
          $this->bruit();
          $this->ecriture();
        }
        
        // Création du cadre
        if ($this->bgframe) 
        {
           $framecol = imagecolorallocate($this->img, ($this->bgR*3+$this->charR)/4, ($this->bgG*3+$this->charG)/4, ($this->bgB*3+$this->charB)/4);
           imagerectangle($this->img, 0, 0, $this->captchawidth-1, $this->captchaheight-1, $framecol);
        }
         
                    
        // Transformations supplémentaires: Grayscale et Brouillage
        // Vérifie si la fonction existe dans la version PHP installée
        if (function_exists('imagefilter')) 
        {
           if ($this->grayscal) imagefilter($this->img,IMG_FILTER_GRAYSCALE);
           if ($this->gaussianblur) imagefilter( $this->img,IMG_FILTER_GAUSSIAN_BLUR);
        }
        
        $this->captchasession['captchatime'] = time();
        $this->captchasession['capthcacptuse']++;       

        ploopi_ob_clean();

        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache'); 
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Expires: Sun, 1 Jan 2000 12:00:00 GMT');
        
        // Envoi de l'image finale au navigateur 
        switch (strtoupper($this->captchaformat)) 
        {  
            case "JPG"  :
            case "JPEG" : 
                header("Content-type: image/jpeg");
                imagejpeg($this->img, "", 80);
            break;
            
            case "GIF"  : 
                header("Content-type: image/gif");
                imagegif($this->img);
            break;

            case "PNG"  : 
            default     : 
                header("Content-type: image/png");
                imagepng($this->img);
        }
        
        imagedestroy ($this->img);
    }
    
    /**
     * Contrôle la saisie de l'utilisateur comparé au captcha
     * 
     * @param string $value
     * @return boolean true/false
     */
    public function verifCaptcha($value)
    {
        $value = addslashes($value);
        $value = str_replace(' ','',$value);  // supprime les espaces saisis par erreur.
        
        $value = ($this->difuplow ? $value : strtolower($value));
        $this->captchasession['code'] = ($this->difuplow ? $this->captchasession['code'] : strtolower($this->captchasession['code']));
        
        if (!empty($this->captchasession['code']) && ($this->captchasession['code'] === $value))
        {
            unset($this->captchasession);
            return true;
        }

        // on a essayé, c'est faux, on brouille le code pour les robots au cas ou on ne renew pas le captcha
        $this->captchasession['code'] = md5(uniqid(rand(1,999)));
        $this->captchasession['codesound'] = '';
        return false;
    }
}

/**
 * captcha sonore
 *
 * @package ploopi
 * @subpackage captcha sound
 * @copyright Sylvain BRISON, Drew Phillips, HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */
class captcha_sound
{
    private $captchasession = '';           // nom de la dimension supplémentaire pour le tableau en session
    
    private $soundpath;    // path des sons de voix
    
    /**
     * Constructeur de la classe captcha_sound
     * 
     * @param string $lang langue à utiliser (optionnel)
     * @param string $session nom de la session => $_SESSION['ploopi']['captcha'][$param] (optionnel)
     * @param string $path chemin jusque au répertoire des sons 
     * @return captcha_sound
     */
    function __construct($lang = '', $session = '', $path = '')
    {
        // changement de path
        $this->soundpath = (!empty($path)) ? $path : '.'._PLOOPI_SEP.'img'._PLOOPI_SEP.'sound'._PLOOPI_SEP;
        
        // changement de langue pour le path
        $this->soundpath .= (!empty($lang)) ? $lang : $_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_language'];
        
        if(substr($this->soundpath,-1) != _PLOOPI_SEP) $this->soundpath .= _PLOOPI_SEP;
        
        // Possibilité de modifier la session d'enregistrement
        if(!empty($session))
        {
            if(!isset($_SESSION['ploopi']['captcha'][$session])) $_SESSION['ploopi']['captcha'][$session] = '';
            $this->captchasession = &$_SESSION['ploopi']['captcha'][$session];
        }
        else
        {
            if(!isset($_SESSION['ploopi']['captcha'])) $_SESSION['ploopi']['captcha'] = '';
            $this->captchasession = &$_SESSION['ploopi']['captcha'];
        }
    } 
    
    /**
     * Génére un son mp3 correspondant au captcha
     * 
     * @return mp3
     */
    public function outputAudioFile()
    {
        if(isset($this->captchasession['codesound']) && !empty($this->captchasession['codesound']))
        {
            $outputaudio = '';
            foreach(str_split($this->captchasession['codesound'],1) as $letter)
            {
                $filename = $this->soundpath . $letter . '.mp3';
    
                $data = file_get_contents ($filename); // read file in

                // Brouillage du son
                $start = 4 + rand(1, 64); // 4 byte (32 bit) frame header
                $datalen = strlen($data) - $start - 256; // leave last 256 bytes unchanged
    
                for ($i = $start; $i < $datalen; $i += 64) 
                { 
                    $ch = ord($data{$i});
                    if ($ch < 9 || $ch > 119) continue;
                    $data{$i} = chr($ch + rand(-8, 8));
                }
                $outputaudio .= $data;
    
                fclose($fp);
            }

            ploopi_ob_clean();
            
            header('Content-Type: application/octet-stream');
            header('Content-Description: File Transfer');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: no-cache'); 
            header('Content-Disposition: inline; filename="securimage_audio_'.rand(1,10).'.mp3"');
            header("Content-Transfer-Encoding: binary\n");
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
            header('Content-Length: ' . strlen($outputaudio));
            header('Expires: Sun, 1 Jan 2000 12:00:00 GMT');
            header('Connection: close');  
            
            echo $outputaudio;
            exit;
        }        
    }
}
 ?>