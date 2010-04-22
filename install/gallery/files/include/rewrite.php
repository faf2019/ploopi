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
 * Gère le rewriting inverse des URL du module GALLERY
 *
 * @package webedit
 * @subpackage rewrite
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

if(preg_match('/gallery\/dewslider-g([0-9]*)-sb([0,1]{1})-st([0,1]{1})-rs([0,1]{1})-t([0-9]{1,2})-at([b,t]{1})-ab([b,t]{1})-tr([f,s,p,o,w,b]{1})-s([0-9]{1,2})\.xml/', $arrParsedURI['path'], $arrMatches) == 1) 
{
    $ploopi_access_script = 'index-light';
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'ploopi_get_dewsliderXML';
    $_REQUEST['id_gallery'] = $_GET['id_gallery'] = $arrMatches[1];
    $_REQUEST['showbuttons'] = $_GET['showbuttons'] = ($arrMatches[2]) ? 'yes' : 'no';
    $_REQUEST['showtitles'] = $_GET['showtitles'] = ($arrMatches[3]) ? 'yes' : 'no';
    $_REQUEST['randomstart'] = $_GET['randomstart'] = ($arrMatches[4]) ? 'yes' : 'no';
    $_REQUEST['timer'] = $_GET['timer'] = intval($arrMatches[5]);
    $_REQUEST['aligntitles'] = $_GET['aligntitles'] = ($arrMatches[6] == 'b') ? 'bottom' : 'top';
    $_REQUEST['alignbuttons'] = $_GET['alignbuttons'] = ($arrMatches[7] == 'b') ? 'bottom' : 'top';
    switch ($arrMatches[8])
    {
        case 'f':
            $_REQUEST['transition'] = $_GET['transition'] = 'fade';
            break;
        case 's':
            $_REQUEST['transition'] = $_GET['transition'] = 'slide';
            break;
        case 'p':
            $_REQUEST['transition'] = $_GET['transition'] = 'push';
            break;
        case 'o':
            $_REQUEST['transition'] = $_GET['transition'] = 'pop';
            break;
        case 'w':
            $_REQUEST['transition'] = $_GET['transition'] = 'warp';
            break;
        default:
        case 'b':
            $_REQUEST['transition'] = $_GET['transition'] = 'blur';
            break;
    }
    $_REQUEST['speed'] = $_GET['speed'] = intval($arrMatches[9]);
    $booRewriteRuleFound = true;    
}
elseif(preg_match('/gallery\/flip-g([0-9]*)-(name|desc|linkself|linkblank|lightbox)-transp([0,1]{1})-friction([0-9]{1,3})-fullscreen([0,1]{1})-fieldofview([0-9]{1,3})-margin([-]?[0-9]{1,4})-([-]?[0-9]{1,4})-([-]?[0-9]{1,4})-([-]?[0-9]{1,4})-flip([a,m,k]{1})-vertical([0,1]{1})-speed([-]?[0-9]{1,3})-default_speed([-]?[0-9]{1,3})-reset_delay([0-9]{1,3})-amount([0-9]{1,4})-blur([0-9]{1,3})-distance([-]?[0-9]{1,4})-alpha([0-9]{1,3})\.xml/', $arrParsedURI['path'], $arrMatches) == 1) 
{
    /*
     gallery/flip-g20-lightbox-transp1-friction5-fullscreen0-fieldofview55-margin0-0-100-0-flipm-vertical1-speed180-default_speed45-reset_delay30-amount100-blur2-distance0-alpha50.xml
    */
     
    $ploopi_access_script = 'index-light';
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'ploopi_get_flipXML';
    $_REQUEST['id_gallery'] = $_GET['id_gallery'] = $arrMatches[1];
    
    if(in_array($arrMatches[2], array('name', 'desc', 'linkself', 'linkblank', 'lightbox')))
    	$_REQUEST['onmouse'] = $_GET['onmouse'] = $arrMatches[2];
    else 
    	$_REQUEST['onmouse'] = $_GET['onmouse'] = 'none';
    
    $_REQUEST['transparent'] = $_GET['transparent'] = $arrMatches[3];
    
    $_REQUEST['friction'] = $_GET['friction'] = ($arrMatches[4]>=1 && $arrMatches[4] <= 100) ? $arrMatches[4] : 5;
    $_REQUEST['fullscreen'] = $_GET['fullscreen'] = ($arrMatches[5] == 1) ? 'true' : 'false';
    $_REQUEST['fieldofview'] = $_GET['fieldofview'] = ($arrMatches[6]>=1 && $arrMatches[6] <= 179) ? $arrMatches[6] : 55;
    /* margins */
    $_REQUEST['margin_top'] = $_GET['margin_top'] = ($arrMatches[7]>=-1000 && $arrMatches[7] <= 1000) ? $arrMatches[7] : 0;
    $_REQUEST['margin_right'] = $_GET['margin_right'] = ($arrMatches[8]>=-1000 && $arrMatches[8] <= 1000) ? $arrMatches[8] : 0;
    $_REQUEST['margin_bottom'] = $_GET['margin_bottom'] = ($arrMatches[9]>=-1000 && $arrMatches[9] <= 1000) ? $arrMatches[9] : 0;
    $_REQUEST['margin_left'] = $_GET['margin_left'] = ($arrMatches[10]>=-1000 && $arrMatches[10] <= 1000) ? $arrMatches[10] : 0;
    
    /* interaction */
    if($arrMatches[11] == 'a') $_REQUEST['flip'] = $_GET['flip'] = 'auto';
    elseif($arrMatches[11] == 'm') $_REQUEST['flip'] = $_GET['flip'] = 'mouse';
    elseif($arrMatches[11] == 'k') $_REQUEST['flip'] = $_GET['flip'] = 'keyboard';
    
    $_REQUEST['vertical'] = $_GET['vertical'] = ($arrMatches[12] == 1) ? 'true' : 'false';
    $_REQUEST['speed'] = $_GET['speed'] = ($arrMatches[13]>=-360 && $arrMatches[13] <= 360) ? $arrMatches[13] : 180;
    $_REQUEST['default_speed'] = $_GET['default_speed'] = ($arrMatches[14]>=-360 && $arrMatches[14] <= 360) ? $arrMatches[14] : 45;
    $_REQUEST['reset_delay'] = $_GET['reset_delay'] = ($arrMatches[15]>=0 && $arrMatches[15] <= 600) ? $arrMatches[15] : 30;
    
    /* reflection */
    $_REQUEST['amount'] = $_GET['amount'] = ($arrMatches[16]>=0 && $arrMatches[16] <= 1000) ? $arrMatches[16] : 100;
    $_REQUEST['blur'] = $_GET['blur'] = ($arrMatches[17]>=0 && $arrMatches[17] <= 100) ? $arrMatches[17] : 2;
    $_REQUEST['distance'] = $_GET['distance'] = ($arrMatches[18]>=-1000 && $arrMatches[18] <= 1000) ? $arrMatches[18] : 0;
    $_REQUEST['alpha'] = $_GET['alpha'] = ($arrMatches[19]>=0 && $arrMatches[19] <= 100) ? $arrMatches[19].'%' : '50%';
    
    $booRewriteRuleFound = true;
}
elseif(preg_match('/gallery\/carousel-g([0-9]*)-(name|desc|linkself|linkblank|lightbox)-transp([0,1]{1})-friction([0-9]{1,3})-fullscreen([0,1]{1})-margin([-]?[0-9]{1,4})-([-]?[0-9]{1,4})-([-]?[0-9]{1,4})-([-]?[0-9]{1,4})-([0-9]{1,2})-([0-9]{1,3})-rotation([a,m,k]{1})-view_point([n,m,k]{1})-speed([-]?[0-9]{1,3})-default_speed([-]?[0-9]{1,3})-default_view_point([0-9]{1,3})-reset_delay([0-9]{1,3})-size([0-9]{1,3})-amount([0-9]{1,3})-blur([0-9]{1,3})-blur_quality([1,2,3]{1})-amount([0-9]{1,3})-blur([0-9]{1,3})-distance([-]?[0-9]{1,4})-alpha([0-9]{1,3})\.xml/', $arrParsedURI['path'], $arrMatches) == 1) 
{
    /*
    /gallery/carousel-g1-none-transp0-friction5-fullscreen0-margin0-0-0-0-33-50-rotationm-view_pointm-speed90-default_speed45-default_view_point20-reset_delay30-size50-amount50-blur10-blur_quality3-amount100-blur2-distance0-alpha50.xml
    */
    
    $ploopi_access_script = 'index-light';
    $_REQUEST['ploopi_op'] = $_GET['ploopi_op'] = 'ploopi_get_carouselXML';
    $_REQUEST['id_gallery'] = $_GET['id_gallery'] = $arrMatches[1];
    
    if($arrMatches[2] == 'name') $_REQUEST['onmouse'] = $_GET['onmouse'] = 'name';
    elseif($arrMatches[2] == 'desc') $_REQUEST['onmouse'] = $_GET['onmouse'] = 'desc';
    elseif($arrMatches[2] == 'link') $_REQUEST['onmouse'] = $_GET['onmouse'] = 'link';
    elseif($arrMatches[2] == 'lightbox') $_REQUEST['onmouse'] = $_GET['onmouse'] = 'lightbox';
        
    $_REQUEST['transparent'] = $_GET['transparent'] = $arrMatches[3];
    $_REQUEST['friction'] = $_GET['friction'] = ($arrMatches[4]>=1 && $arrMatches[4] <= 100) ? $arrMatches[4] : 5;
    $_REQUEST['fullscreen'] = $_GET['fullscreen'] = ($arrMatches[5] == 1) ? 'true' : 'false';

    /* margins */
    $_REQUEST['margin_top'] = $_GET['margin_top'] = ($arrMatches[6]>=-1000 && $arrMatches[6] <= 1000) ? $arrMatches[6] : 0;
    $_REQUEST['margin_right'] = $_GET['margin_right'] = ($arrMatches[7]>=-1000 && $arrMatches[7] <= 1000) ? $arrMatches[7] : 0;
    $_REQUEST['margin_bottom'] = $_GET['margin_bottom'] = ($arrMatches[8]>=-1000 && $arrMatches[8] <= 1000) ? $arrMatches[8] : 0;
    $_REQUEST['margin_left'] = $_GET['margin_left'] = ($arrMatches[9]>=-1000 && $arrMatches[9] <= 1000) ? $arrMatches[9] : 0;
    $_REQUEST['horizontal_ratio'] = $_GET['horizontal_ratio'] = ($arrMatches[10]>=1 && $arrMatches[10] <= 50) ? $arrMatches[10].'%' : '33%';
    $_REQUEST['vertical_ratio'] = $_GET['vertical_ratio'] = ($arrMatches[11]>=1 && $arrMatches[11] <= 100) ? $arrMatches[11].'%' : '50%';
    
    /* interaction */
    if($arrMatches[12] == 'a') $_REQUEST['rotation'] = $_GET['rotation'] = 'auto';
    elseif($arrMatches[12] == 'm') $_REQUEST['rotation'] = $_GET['rotation'] = 'mouse';
    elseif($arrMatches[12] == 'k') $_REQUEST['rotation'] = $_GET['rotation'] = 'keyboard';
    
    if($arrMatches[13] == 'n') $_REQUEST['view_point'] = $_GET['view_point'] = 'none';
    elseif($arrMatches[13] == 'm') $_REQUEST['view_point'] = $_GET['view_point'] = 'mouse';
    elseif($arrMatches[13] == 'k') $_REQUEST['view_point'] = $_GET['view_point'] = 'keyboard';
    
    $_REQUEST['speed'] = $_GET['speed'] = ($arrMatches[14]>=-360 && $arrMatches[14] <= 360) ? $arrMatches[14] : 180;
    $_REQUEST['default_speed'] = $_GET['default_speed'] = ($arrMatches[15]>=-360 && $arrMatches[15] <= 360) ? $arrMatches[15] : 45;
    $_REQUEST['default_view_point'] = $_GET['default_view_point'] = ($arrMatches[16]>=0 && $arrMatches[16] <= 100) ? $arrMatches[16].'%' : '20%';
    $_REQUEST['reset_delay'] = $_GET['reset_delay'] = ($arrMatches[17]>=0 && $arrMatches[17] <= 600) ? $arrMatches[17] : 30;
    
    /* far_photos */
    $_REQUEST['far_size'] = $_GET['far_size'] = ($arrMatches[18]>=0 && $arrMatches[18] <= 100) ? $arrMatches[18].'%' : '50%';
    $_REQUEST['far_amount'] = $_GET['far_amount'] = ($arrMatches[19]>=0 && $arrMatches[19] <= 100) ? $arrMatches[19].'%' : '50%';
    $_REQUEST['far_blur'] = $_GET['far_blur'] = ($arrMatches[20]>=0 && $arrMatches[20] <= 100) ? $arrMatches[20] : 10;
    $_REQUEST['far_blur_quality'] = $_GET['far_blur_quality'] = ($arrMatches[21]>=1 && $arrMatches[21] <= 3) ? $arrMatches[21] : 3;
    
    /* reflection */
    $_REQUEST['amount'] = $_GET['amount'] = ($arrMatches[22]>=0 && $arrMatches[22] <= 1000) ? $arrMatches[22] : 100;
    $_REQUEST['blur'] = $_GET['blur'] = ($arrMatches[23]>=0 && $arrMatches[23] <= 100) ? $arrMatches[23] : 2;
    $_REQUEST['distance'] = $_GET['distance'] = ($arrMatches[24]>=-1000 && $arrMatches[24] <= 1000) ? $arrMatches[24] : 0;
    $_REQUEST['alpha'] = $_GET['alpha'] = ($arrMatches[25]>=0 && $arrMatches[25] <= 100) ? $arrMatches[25].'%' : '50%';
    
    $booRewriteRuleFound = true;
    }
/*
ploopi_print_r($arrMatches);
ploopi_print_r($_REQUEST);
ploopi_die();
*/
?>