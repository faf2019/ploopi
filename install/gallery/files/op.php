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
 * Opérations
 *
 * @package gallery
 * @subpackage op
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

 
 
 /**
 * Si l'utilisateur est connecté
 */

if ($_SESSION['ploopi']['connected'])
{
    /**
     * On vérifie qu'on est bien dans le module Gallery.
     * Ces opérations ne peuvent être effectuées que depuis le module Gallery.
     */
    if (ploopi_ismoduleallowed('gallery'))
    {
        switch($ploopi_op)
        {
            case 'gallery_get_preview_photos_directory':
                if(!isset($_SESSION['ploopi']['gallery']['rep_selected'])) $_SESSION['ploopi']['gallery']['rep_selected'] = array();
                if($_GET['id_directories'] != 'all')
                {
                    if(array_key_exists($_GET['id_directories'],$_SESSION['ploopi']['gallery']['rep_selected']))
                        unset($_SESSION['ploopi']['gallery']['rep_selected'][$_GET['id_directories']]);
                    else
                        $_SESSION['ploopi']['gallery']['rep_selected'][$_GET['id_directories']] = $_GET['id_directories'];
                }
                $strIdDirectory = ($_GET['id_directories'] == 'all') ? implode(',',array_keys($_SESSION['ploopi']['gallery']['rep_selected'])) : $_GET['id_directories'];

                if(empty($strIdDirectory))
                {
                    echo _GALLERY_EDIT_LABEL_NO_PICT;
                    ploopi_die();
                }
                
                $sql = "
                    SELECT      f.id,
                                f.md5id,
                                f.version,
                                fo.name,
                                u.id as user_id,
                                u.login,
                                u.lastname,
                                u.firstname,
                                w.id as workspace_id,
                                w.label
                
                    FROM        (ploopi_mod_doc_file f,
                                ploopi_mod_doc_folder fo)
                    
                    LEFT JOIN   ploopi_user u
                    ON          f.id_user = u.id
                
                    LEFT JOIN   ploopi_workspace w
                    ON          f.id_workspace = w.id
                
                    WHERE       LCASE(f.extension) IN ('jpg','jpeg','gif','png')
                    AND         fo.id IN ({$strIdDirectory}) 
                    AND         fo.foldertype = 'public' 
                    AND         f.id_folder = fo.id
                    
                    ORDER BY    fo.name, f.name
                ";
                $resultSqlPhotosDir = $db->query($sql);
                
                $c=0;
                ploopi_init_module('doc', false, false, false);
                
                include_once './modules/doc/class_docfile.php';
                
                $objImgFile = new docfile();

                $strTmpPath = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp';
                ploopi_makedir($strTmpPath);
            
                if($db->numrows($resultSqlPhotosDir))
                {
                    $oldNameDir = '';
                    while ($row = $db->fetchrow($resultSqlPhotosDir))
                    {
                        if($oldNameDir != $row['name']) echo '<h2 style="text-align: center; padding: 0; margin: 0;">'.$row['name'].'</h2>';
                        
                        $objImgFile->open($row['id']);
                        $arrMeta = $objImgFile->getmeta();
                        
                        ?>
                        <div class="gallery_bloc_image">
                            <div id="photo_preview_<?php echo $row['md5id']; ?>_<?php echo $row['version']; ?>" class="gallery_image_preview">
                                <img src="<?php echo ploopi_urlencode('admin-light.php?ploopi_op=gallery_admin_get_photo&md5_preview='.$row['md5id'].'&version='.$row['version'].'&'.ploopi_createtimestamp()); ?>" /><br/>
                            </div>
                            <div  class="gallery_image_info">
                                <?php
                                    $refresh = '<a href="javascript:void(0);" onclick="javascript:gallery_refresh_photo(\''.$row['md5id'].'\','.$row['version'].');" title="'._GALLERY_EDIT_REFRESH_PHOTO.'"><img src="./modules/gallery/img/refresh.png"></a>&nbsp;';
                                    echo $skin->open_simplebloc($objImgFile->fields['name'],'', '', $refresh);
                                    if(!empty($arrMeta))
                                    {
                                        $array_columns = array();
                                        $array_values = array();
                            
                                        $array_columns['left']['meta'] =
                                            array(
                                                'label' => 'Propriété',
                                                'width' => '150',
                                                'options' => array('sort' => true)
                                            );
                            
                                        $array_columns['auto']['valeur'] =
                                            array(
                                                'label' => 'Valeur',
                                                'options' => array('sort' => true)
                                            );
                            
                                        $c = 0;
                                        foreach ($arrMeta as $key => $meta)
                                        {
                                            $array_values[$c]['values']['meta']     = array('label' => $meta['meta'], 'style' => '');
                                            $array_values[$c]['values']['valeur']   = array('label' => $meta['value'], 'style' => '');
                                            $array_values[$c]['description'] = $meta['meta'];
                                            $array_values[$c]['link'] = '';
                                            $array_values[$c]['style'] = '';
                                            $c++;
                                        }

                                        $skin->display_array($array_columns, $array_values, 'file_meta', array('sortable' => true));
                                    }
                                    else
                                    {
                                        echo '<div style="padding: 2px 4px;">Pas d\'information sur le fichier</div>';
                                    }
                                    echo $skin->close_simplebloc();
                                ?>
                            </div>
                        </div>
                        <?php
                        
                        $oldNameDir = $row['name'];
                    }
                }
                else
                {
                    echo _GALLERY_EDIT_LABEL_NO_PICT;
                }                
                
                ploopi_die();
                break;
                
            case 'gallery_admin_get_photo':
                if(file_exists('./include/classes/mimethumb.php')) // Retrocompatibilité avec ploopi < 1.6.9.0
                {
                    $refresh = (isset($_GET['refresh'])) ? true : false;
                    
                    $intTimeCache = 86400; // 24h

                    include_once './include/classes/cache.php';
                    ploopi_ob_clean();
        
                    $objCache = new ploopi_cache(md5('gallery_admin_preview_'.$_GET['md5_preview'].'_'.$_GET['version']), $intTimeCache); // Attribution d'un groupe spécifique pour le cache pour permettre un clean précis
                    $objCache->set_groupe('gallery_admin_preview_'.$_SESSION['ploopi']['workspaceid'].'_'.$_SESSION['ploopi']['moduleid']); 
                    
                    if(!$objCache->start($refresh)) // si pas de cache on le crée
                    {
                        ploopi_init_module('doc', false, false, false);
                        
                        include_once './modules/doc/class_docfile.php';
                        include './include/classes/mimethumb.php';
                        
                        $objDoc = new docfile();
                        $objThumb = new mimethumb(130,0,0,'png','transparent');
                        
                        if($objDoc->openmd5($_GET['md5_preview']))
                            $objThumb->getThumbnail($objDoc->getfilepath(),$objDoc->fields['extension']);
                            
                        if(isset($objCache)) $objCache->end();
                    }
                    else
                    {
                        header("Content-Type: image/png");
                    }
                    ploopi_die();
                }
                else
                {
                    ploopi_init_module('doc', false, false, false);
                    
                    $strTmpPath = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp';
                    
                    include_once './modules/doc/class_docfile.php';
                    
                    $objImgFile = new docfile();
                    $objImgFile->openmd5($_GET['md5_preview']);
                    
                    if(!isset($_SESSION['ploopi']['gallery']['photo_preview'][$_GET['md5_preview']]) || isset($_GET['refresh']))
                        $_SESSION['ploopi']['gallery']['photo_preview'][$_GET['md5_preview']] = $strTmpPath.'/preview_'.$_GET['md5_preview'];
                        
                    if(!file_exists($_SESSION['ploopi']['gallery']['photo_preview'][$_GET['md5_preview']]) || isset($_GET['refresh']))
                        ploopi_resizeimage($objImgFile->getfilepath(), 0, 130, 0, 'png', 0, $_SESSION['ploopi']['gallery']['photo_preview'][$_GET['md5_preview']]);
                    
                    if (!empty($_SESSION['ploopi']['gallery']['photo_preview'][$_GET['md5_preview']])) ploopi_downloadfile($_SESSION['ploopi']['gallery']['photo_preview'][$_GET['md5_preview']], 'preview.png', false, false);
                    ploopi_die();
                }
                break;
            case 'gallery_refresh_photo':
                ?>
                <img src="<?php echo ploopi_urlencode('admin-light.php?ploopi_op=gallery_admin_get_photo&refresh=1&md5_preview='.$_GET['md5_preview'].'&version='.$_GET['version'].'&'.ploopi_createtimestamp()); ?>" />
                <?php
                ploopi_die();
                break;
        }
    }
}

// Op du front
switch($ploopi_op)
{
    case 'gallery_get_photo':
        $intTimeCache = 2592000; // 30 jours
        
        include_once './include/classes/cache.php';
        
        ploopi_ob_clean();

        header("Content-Type: image/jpg"); // pour le cache

        $objCache = new ploopi_cache('galleryjpg'.$_GET['id_image'].$_GET['version'].$_GET['width'].$_GET['height'].$_GET['color'], $intTimeCache);
                
        if(!$objCache->start()) // si pas de cache on le crée
        {
            ploopi_init_module('doc', false, false, false);
            
            include_once './modules/doc/class_docfile.php';
        
            $objImgFile = new docfile();
            $objImgFile->open($_GET['id_image']);
            
            if($_GET['color'] == 'transparence')
                ploopi_resizeimage($objImgFile->getfilepath(), 0, $_GET['width'], $_GET['height'], 'png', 0, '', 'transparent');
            else
                ploopi_resizeimage($objImgFile->getfilepath(), 0, $_GET['width'], $_GET['height'], array('jpg',90), 0, '', '#'.$_GET['color']);
            
            if(isset($objCache)) $objCache->end();
        }
        ploopi_die();
        break;
    case 'ploopi_get_dewsliderXML':
        // Vidage du buffer
        ploopi_ob_clean();
        
        $strXML = '<?xml version="1.0" encoding="UTF-8" ?>'."\r\n";
        $strXML .= '<album showbuttons="'.$_GET['showbuttons'].'" showtitles="'.$_GET['showtitles'].'" randomstart="'.$_GET['randomstart'].'" timer="'.$_GET['timer'].'" aligntitles="'.$_GET['aligntitles'].'" alignbuttons="'.$_GET['alignbuttons'].'" transition="'.$_GET['transition'].'" speed="'.$_GET['speed'].'">'."\r\n";
        
        include_once './modules/gallery/class/class_gallery.php';
        $objGallery = new gallery();
        if($objGallery->open($_GET['id_gallery']))
        {
            $color = ($objGallery->fields['view_transparence']) ? 'transparence' : str_replace('#','',$objGallery->fields['view_color']);
            
            $arrDirSelectTmp = $objGallery->getdirectories();
            if(!empty($arrDirSelectTmp))
            {
                
                foreach ($arrDirSelectTmp as $key => $dirSelect) $arrDirSelect[] = $dirSelect['id_directory'];
                $sql = "
                    SELECT      f.id, f.name, f.version
                
                    FROM        (ploopi_mod_doc_file f,
                                ploopi_mod_doc_folder fo)
                    
                    WHERE       LCASE(f.extension) IN ('jpg','jpeg','gif','png')
                    AND         fo.id IN (".implode(',',$arrDirSelect).") 
                    AND         fo.foldertype = 'public' 
                    AND         f.id_folder = fo.id
                    
                    ORDER BY    fo.name, f.name
                ";
            
                $resultSqlDir = $db->query($sql);

                include_once './modules/doc/class_docfile.php';
                $objImgFile = new docfile();
    
                while ($row = $db->fetchrow($resultSqlDir))
                {
                    
                     $strXML .= "\t".'<img src="'.htmlspecialchars(ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=view&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['view_width'].'&height='.$objGallery->fields['view_height'].'&color='.$color)).'" title="'.$row['name'].'" />'."\r\n";
                }
                
            }
        }
        $strXML .= '</album>';
        
        echo utf8_encode($strXML);
        header('Content-Type: text/xml');
        ploopi_die();
        break;

    case 'ploopi_get_flipXML':
         
        // Vidage du buffer
        ploopi_ob_clean();
        
        $strXML = '<?xml version="1.0" encoding="UTF-8" ?>'."\r\n";
        $strXML .= '<slide_show>'."\r\n";
        
        include_once './modules/gallery/class/class_gallery.php';
        $objGallery = new gallery();
        if($objGallery->open($_GET['id_gallery']))
        {
            $color = ($objGallery->fields['thumb_transparence']) ? 'transparence' : str_replace('#','',$objGallery->fields['thumb_color']);
            
            $arrDirSelectTmp = $objGallery->getdirectories();
            if(!empty($arrDirSelectTmp))
            {
                $strXML .= "\t".'<options>'."\r\n";
                $strXML .= "\t".'<debug>false</debug>'."\r\n";
                $strXML .= "\t".'<background>'.(($_GET['transparent']) ? 'transparent' : $objGallery->fields['thumb_color']).'</background>'."\r\n";
                $strXML .= "\t".'<friction>'.$_GET['friction'].'</friction>'."\r\n";
                $strXML .= "\t".'<fullscreen>'.$_GET['fullscreen'].'</fullscreen>'."\r\n";
                $strXML .= "\t".'<fieldOfView>'.$_GET['fieldofview'].'</fieldOfView>'."\r\n";
                $strXML .= "\t".'<margins>'."\r\n";
                $strXML .= "\t".'<top>'.$_GET['margin_top'].'</top>'."\r\n";
                $strXML .= "\t".'<left>'.$_GET['margin_right'].'</left>'."\r\n";
                $strXML .= "\t".'<bottom>'.$_GET['margin_bottom'].'</bottom>'."\r\n";
                $strXML .= "\t".'<right>'.$_GET['margin_left'].'</right>'."\r\n";
                $strXML .= "\t".'</margins>'."\r\n";
                $strXML .= "\t".'<interaction>'."\r\n";
                $strXML .= "\t".'<flip>'.$_GET['flip'].'</flip>'."\r\n";
                $strXML .= "\t".'<vertical>'.$_GET['vertical'].'</vertical>'."\r\n";
                $strXML .= "\t".'<speed>'.$_GET['speed'].'</speed>'."\r\n";
                $strXML .= "\t".'<default_speed>'.$_GET['default_speed'].'</default_speed>'."\r\n";
                $strXML .= "\t".'<reset_delay>'.$_GET['reset_delay'].'</reset_delay>'."\r\n";
                $strXML .= "\t".'</interaction>'."\r\n";
                $strXML .= "\t".'<reflection>'."\r\n";
                $strXML .= "\t".'<amount>'.$_GET['amount'].'</amount>'."\r\n";
                $strXML .= "\t".'<blur>'.$_GET['blur'].'</blur>'."\r\n";
                $strXML .= "\t".'<distance>'.$_GET['distance'].'</distance>'."\r\n";
                $strXML .= "\t".'<alpha>'.$_GET['alpha'].'</alpha>'."\r\n";
                $strXML .= "\t".'</reflection>'."\r\n";
                $strXML .= "\t".'</options>'."\r\n";
                
                foreach ($arrDirSelectTmp as $key => $dirSelect) $arrDirSelect[] = $dirSelect['id_directory'];
                $sql = "
                    SELECT      f.id, f.name, f.version, f.description
                
                    FROM        (ploopi_mod_doc_file f,
                                ploopi_mod_doc_folder fo)
                    
                    WHERE       LCASE(f.extension) IN ('jpg','jpeg','gif','png')
                    AND         fo.id IN (".implode(',',$arrDirSelect).") 
                    AND         fo.foldertype = 'public' 
                    AND         f.id_folder = fo.id
                    
                    ORDER BY    fo.name, f.name
                ";
            
                $resultSqlDir = $db->query($sql);

                include_once './modules/doc/class_docfile.php';
                $objImgFile = new docfile();
				$intCpt = 0;
                while ($row = $db->fetchrow($resultSqlDir))
                {
                    $strXML .= "\t".'<photo';

					if($_GET['onmouse'] == 'desc' && !empty($row['description']))
                        $strXML .= ' title="'.htmlspecialchars($row['description']).'"';
                    elseif($_GET['onmouse'] == 'linkself' && !empty($row['description']) && ploopi_is_url($row['description']))
                        $strXML .= ' title="'.htmlspecialchars($row['name']).'" href="'.htmlspecialchars($row['description']).'"';
                    elseif($_GET['onmouse'] == 'linkblank' && !empty($row['description']) && ploopi_is_url($row['description']))
                        $strXML .= ' title="'.htmlspecialchars($row['name']).'" href="'.htmlspecialchars($row['description']).'" target="_blank"';
                    elseif($_GET['onmouse'] == 'lightbox')
                        $strXML .= ' title="'.htmlspecialchars($row['name']).'" href="javascript:mainLightbox.start($(\''.$_GET['id_gallery'].'_'.substr('0'.$intCpt,-2).'\'))" target="_self"';
                    else
                        $strXML .= ' title="'.htmlspecialchars($row['name']).'"';
                        
                     $strXML .= '>'.htmlspecialchars(ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=view&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['thumb_width'].'&height='.$objGallery->fields['thumb_height'].'&color='.$color)).'</photo>'."\r\n";
                     
                     $intCpt++;
                }
                
            }
        }
        $strXML .= '</slide_show>';
        
        echo utf8_encode($strXML);
        header('Content-Type: text/xml');
        ploopi_die();
        break;

    case 'ploopi_get_carouselXML':
         
        // Vidage du buffer
        ploopi_ob_clean();
        
        $strXML = '<?xml version="1.0" encoding="UTF-8" ?>'."\r\n";
        $strXML .= '<slide_show>'."\r\n";
        
        include_once './modules/gallery/class/class_gallery.php';
        $objGallery = new gallery();
        if($objGallery->open($_GET['id_gallery']))
        {
            $color = ($objGallery->fields['thumb_transparence']) ? 'transparence' : str_replace('#','',$objGallery->fields['thumb_color']);
            
            $arrDirSelectTmp = $objGallery->getdirectories();
            if(!empty($arrDirSelectTmp))
            {
                $strXML .= "\t".'<options>'."\r\n";
                $strXML .= "\t".'<debug>false</debug>'."\r\n";
                $strXML .= "\t".'<background>'.(($_GET['transparent']) ? 'transparent' : $objGallery->fields['thumb_color']).'</background>'."\r\n";
                $strXML .= "\t".'<friction>'.$_GET['friction'].'</friction>'."\r\n";
                $strXML .= "\t".'<fullscreen>'.$_GET['fullscreen'].'</fullscreen>'."\r\n";

                $strXML .= "\t".'<margins>'."\r\n";
                $strXML .= "\t".'<top>'.$_GET['margin_top'].'</top>'."\r\n";
                $strXML .= "\t".'<left>'.$_GET['margin_right'].'</left>'."\r\n";
                $strXML .= "\t".'<bottom>'.$_GET['margin_bottom'].'</bottom>'."\r\n";
                $strXML .= "\t".'<right>'.$_GET['margin_left'].'</right>'."\r\n";
                $strXML .= "\t".'<horizontal_ratio>'.$_GET['horizontal_ratio'].'</horizontal_ratio>'."\r\n";
                $strXML .= "\t".'<vertical_ratio>'.$_GET['vertical_ratio'].'</vertical_ratio>'."\r\n";
                $strXML .= "\t".'</margins>'."\r\n";
                
                $strXML .= "\t".'<interaction>'."\r\n";
                $strXML .= "\t".'<rotation>'.$_GET['rotation'].'</rotation>'."\r\n";
                $strXML .= "\t".'<view_point>'.$_GET['view_point'].'</view_point>'."\r\n";
                $strXML .= "\t".'<speed>'.$_GET['speed'].'</speed>'."\r\n";
                $strXML .= "\t".'<default_speed>'.$_GET['default_speed'].'</default_speed>'."\r\n";
                $strXML .= "\t".'<default_view_point>'.$_GET['default_view_point'].'</default_view_point>'."\r\n";
                $strXML .= "\t".'<reset_delay>'.$_GET['reset_delay'].'</reset_delay>'."\r\n";
                $strXML .= "\t".'</interaction>'."\r\n";
                
                $strXML .= "\t".'<far_photos>'."\r\n";
                $strXML .= "\t".'<size>'.$_GET['far_size'].'</size>'."\r\n";
                $strXML .= "\t".'<amount>'.$_GET['far_amount'].'</amount>'."\r\n";
                $strXML .= "\t".'<blur>'.$_GET['far_blur'].'</blur>'."\r\n";
                $strXML .= "\t".'<blur_quality>'.$_GET['far_blur_quality'].'</blur_quality>'."\r\n";
                $strXML .= "\t".'</far_photos>'."\r\n";
                
                $strXML .= "\t".'<reflection>'."\r\n";
                $strXML .= "\t".'<amount>'.$_GET['amount'].'</amount>'."\r\n";
                $strXML .= "\t".'<blur>'.$_GET['blur'].'</blur>'."\r\n";
                $strXML .= "\t".'<distance>'.$_GET['distance'].'</distance>'."\r\n";
                $strXML .= "\t".'<alpha>'.$_GET['alpha'].'</alpha>'."\r\n";
                $strXML .= "\t".'</reflection>'."\r\n";
                $strXML .= "\t".'</options>'."\r\n";
                
                foreach ($arrDirSelectTmp as $key => $dirSelect) $arrDirSelect[] = $dirSelect['id_directory'];
                $sql = "
                    SELECT      f.id, f.name, f.version, f.description
                
                    FROM        (ploopi_mod_doc_file f,
                                ploopi_mod_doc_folder fo)
                    
                    WHERE       LCASE(f.extension) IN ('jpg','jpeg','gif','png')
                    AND         fo.id IN (".implode(',',$arrDirSelect).") 
                    AND         fo.foldertype = 'public' 
                    AND         f.id_folder = fo.id
                    
                    ORDER BY    fo.name, f.name
                ";
            
                $resultSqlDir = $db->query($sql);

                include_once './modules/doc/class_docfile.php';
                $objImgFile = new docfile();
                
                $intCpt = 0;
                while ($row = $db->fetchrow($resultSqlDir))
                {
                    $strXML .= "\t".'<photo';

					if($_GET['onmouse'] == 'desc' && !empty($row['description']))
                        $strXML .= ' title="'.htmlspecialchars($row['description']).'"';
                    elseif($_GET['onmouse'] == 'linkself' && !empty($row['description']) && ploopi_is_url($row['description']))
                        $strXML .= ' title="'.htmlspecialchars($row['name']).'" href="'.htmlspecialchars($row['description']).'" target="_self"';
                    elseif($_GET['onmouse'] == 'linkblank' && !empty($row['description']) && ploopi_is_url($row['description']))
                        $strXML .= ' title="'.htmlspecialchars($row['name']).'" href="'.htmlspecialchars($row['description']).'" target="_blank"';
                    elseif($_GET['onmouse'] == 'lightbox')
                        $strXML .= ' title="'.htmlspecialchars($row['name']).'" href="javascript:mainLightbox.start($(\''.$_GET['id_gallery'].'_'.substr('0'.$intCpt,-2).'\'))" target="_self"';
                    else
                        $strXML .= ' title="'.htmlspecialchars($row['name']).'"';
                                            
                    $strXML .= '>'.htmlspecialchars(ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=view&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['thumb_width'].'&height='.$objGallery->fields['thumb_height'].'&color='.$color)).'</photo>'."\r\n";
                    $intCpt++;
                }
            }
        }
        $strXML .= '</slide_show>';
        
        echo utf8_encode($strXML);
        header('Content-Type: text/xml');
        ploopi_die();
        break;
    default:
        break;
}
?>