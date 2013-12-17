<?php
/*
    Copyright (c) 2002-2009 HeXad
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
 * Gestion des objets insérables dans une page de contenu (WebEdit)
 *
 * @package gallery
 * @subpackage wce
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Initialisation du module
 */

ploopi_init_module('gallery');

global $articleid;
global $headingid;
global $template_name;

global $arrGalleryOnce;

$template_gallery = new Template("./templates/frontoffice/$template_name");

if (file_exists("./templates/frontoffice/{$template_name}/gallery.tpl"))
{
    $template_gallery->set_filenames(array('gallery_display' => "gallery.tpl"));
    
    $template_gallery->assign_vars(
        array(
        "TEMPLATE_PATH" => './templates/frontoffice/'.$template_name
        )
    );
    
    include_once './modules/gallery/class/class_gallery_tpl.php';
    $objTpl = new gallery_tpl();
    
    include_once './modules/gallery/class/class_gallery.php';
    $objGallery = new gallery();
    
    if($objGallery->open($obj['object_id']))
    {
        $booError = true;

        $strNameCurlTpl = 'gallery1';
        if($objTpl->open($objGallery->fields['template']))
        {
            $strNameCurlTpl = $objTpl->fields['block'];
            
            // Css à incorporer au template
            if(!empty($objTpl->fields['addtoheadcss']) && !isset($arrGalleryOnce[$headingid.'-'.$articleid.'-'.$objGallery->fields['template']]))
            {
                global $template_body;
                
                $arrAddToHeadCSS = explode(',',$objTpl->fields['addtoheadcss']);
                foreach ($arrAddToHeadCSS as $strAddToHeadCSS)
                    $template_body->assign_block_vars('module_css', array('PATH' => './templates/frontoffice/'.$template_name.'/css/'.$strAddToHeadCSS));
            }
            
            // Css pour ie6 à incorporer au template
            if(!empty($objTpl->fields['addtoheadcssie']) && !isset($arrGalleryOnce[$headingid.'-'.$articleid.'-'.$objGallery->fields['template']]))
            {
                global $template_body;
                
                $arrAddToHeadCSS = explode(',',$objTpl->fields['addtoheadcssie']);
                foreach ($arrAddToHeadCSS as $strAddToHeadCSS)
                    $template_body->assign_block_vars('module_css_ie', array('PATH' => './templates/frontoffice/'.$template_name.'/css/'.$strAddToHeadCSS));
            }
        }
        
        $arrDirSelectTmp = $objGallery->getdirectories();
        if(!empty($arrDirSelectTmp))
        {
            foreach ($arrDirSelectTmp as $key => $dirSelect) $arrDirSelect[] = $dirSelect['id_directory'];
            
            // Requete de recherche de nb d'enregistrement pour les découpages de page. on en profite pour verif qu'il y a des images !
            $sql = "
                SELECT      f.id
            
                FROM        (ploopi_mod_doc_file f,
                            ploopi_mod_doc_folder fo)
                
                WHERE       LCASE(f.extension) IN ('jpg','jpeg','gif','png')
                AND         fo.id IN (".implode(',',$arrDirSelect).") 
                AND         fo.foldertype = 'public' 
                AND         f.id_folder = fo.id
            ";
            $resultSqlDir = $db->query($sql);
            
            $booError = !$db->numrows($resultSqlDir);

            if($db->numrows($resultSqlDir))
            {
                // Gestion des découpage par page
                $actual_page = (isset($_POST['id_cut_page']) && ($_POST['id_cut_page'] == $objGallery->fields['id'].'_1' || $_POST['id_cut_page'] == $objGallery->fields['id'].'_2')) ? $_POST[$_POST['id_cut_page'].'_page'] : 1;
                
                $paramCutPage = array(
                    'nbMax' => $db->numrows($resultSqlDir),                                 // Nombre d'enregistrement total
                    'by' => $objGallery->fields['nb_line']*$objGallery->fields['nb_col'],   // Nombre d'enregistrement par page
                    'page' => $actual_page                                                  // Page en cours
                );   
                $htmlPage1 = gallery_cut_page($objGallery->fields['id'].'_1',$paramCutPage);
                $htmlPage2 = gallery_cut_page($objGallery->fields['id'].'_2',$paramCutPage);

                $intNbPictDeb = ($actual_page-1)*$objGallery->fields['nb_line']*$objGallery->fields['nb_col'];
                $intNbPict = $objGallery->fields['nb_line']*$objGallery->fields['nb_col'];

                $sql = "
                    SELECT      f.id, f.name, f.description, f.version
                
                    FROM        (ploopi_mod_doc_file f,
                                ploopi_mod_doc_folder fo)
                    
                    WHERE       LCASE(f.extension) IN ('jpg','jpeg','gif','png')
                    AND         fo.id IN (".implode(',',$arrDirSelect).")
                    AND         fo.foldertype = 'public' 
                    AND         f.id_folder = fo.id
                    
                    ORDER BY    fo.name, f.name
                ";
                
                if($intNbPict > 0)
                    $sql .= " LIMIT {$intNbPictDeb},{$intNbPict}";
                
                $resultSqlDir = $db->query($sql);
                
                include_once './modules/doc/class_docfile.php';
                $objImgFile = new docfile();
    
                $booInitTemplate = true;
                $booInitLine = true;
                $intLine = $intCol = 1;
                $intCpt = 0;
                while ($row = $db->fetchrow($resultSqlDir))
                {
                    // On initialise le template en mettant la premiere image
                    if($booInitTemplate)
                    {
                        $booInitTemplate = false;
                        $template_gallery->assign_block_vars($strNameCurlTpl, 
                            array(
                            'ID_GALLERY'    => $objGallery->fields['id'],
                            'URL_VIEW'      => ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=view&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['view_width'].'&height='.$objGallery->fields['view_height'].'&color='.str_replace('#','',$objGallery->fields['view_color'])),
                            'URL_THUMB'     => ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=thumb&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['thumb_width'].'&height='.$objGallery->fields['thumb_height'].'&color='.str_replace('#','',$objGallery->fields['thumb_color'])),
                            'NAME'          => $row['name'],
                            'DESCRIPTION'   => $row['description'],
                            'THUMB_WIDTH'   => $objGallery->fields['thumb_width'],
                            'THUMB_HEIGHT'  => $objGallery->fields['thumb_height'],
                            'THUMB_COLOR'   => $objGallery->fields['thumb_color'],
                            'VIEW_WIDTH'    => $objGallery->fields['view_width'],
                            'VIEW_HEIGHT'   => $objGallery->fields['view_height'],
                            'VIEW_COLOR'    => $objGallery->fields['view_color'],
                            'NB_COL'        => $objGallery->fields['nb_col'],
                            'NB_LINE'       => $objGallery->fields['nb_line'],
                            'ID_UNIQ'       => uniqid(),
                            'PAGE_CUT_TOP'      => $htmlPage1,
                            'PAGE_CUT_BOTTOM'   => $htmlPage2
                            )
                        );
                        
                        if(!isset($arrGalleryOnce[$headingid.'-'.$articleid.'-'.$objGallery->fields['template']]))
                        {
                            $template_gallery->assign_block_vars($strNameCurlTpl.'.switch_once',array());
                        }
                    }
                        
                    // On initialise chaque ligne en mettant dedans la premiere image de chaque ligne
                    if($booInitLine)
                    {
                        $booInitLine = false;
                        $template_gallery->assign_block_vars($strNameCurlTpl.'.line', 
                            array(
                            'URL_VIEW'      => ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=view&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['view_width'].'&height='.$objGallery->fields['view_height'].'&color='.str_replace('#','',$objGallery->fields['view_color'])),
                            'URL_THUMB'     => ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=thumb&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['thumb_width'].'&height='.$objGallery->fields['thumb_height'].'&color='.str_replace('#','',$objGallery->fields['thumb_color'])),
                            'NAME'          => $row['name'],
                            'DESCRIPTION'   => $row['description'],
                            'THUMB_WIDTH'   => $objGallery->fields['thumb_width'],
                            'THUMB_HEIGHT'  => $objGallery->fields['thumb_height'],
                            'THUMB_COLOR'   => $objGallery->fields['thumb_color'],
                            'VIEW_WIDTH'    => $objGallery->fields['view_width'],
                            'VIEW_HEIGHT'   => $objGallery->fields['view_height'],
                            'VIEW_COLOR'    => $objGallery->fields['view_color'],
                            'NB_COL'        => $objGallery->fields['nb_col'],
                            'NB_LINE'       => $objGallery->fields['nb_line'],
                            'ID_UNIQ'       => uniqid ()
                            )
                        );
                    }
                    
                    $objImgFile->open($row['id']);
                    $arrMeta = $objImgFile->getmeta();
                    
                    $template_gallery->assign_block_vars($strNameCurlTpl.'.line.col', 
                        array(
                        'URL_VIEW'      => ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=view&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['view_width'].'&height='.$objGallery->fields['view_height'].'&color='.str_replace('#','',$objGallery->fields['view_color'])),
                        'URL_THUMB'     => ploopi_urlencode('index-light.php?ploopi_op=gallery_get_photo&type=thumb&id_image='.$row['id'].'&version='.$row['version'].'&width='.$objGallery->fields['thumb_width'].'&height='.$objGallery->fields['thumb_height'].'&color='.str_replace('#','',$objGallery->fields['thumb_color'])),
                        'NAME'          => $row['name'],
                        'DESCRIPTION'   => $row['description'],
                        'THUMB_WIDTH'   => $objGallery->fields['thumb_width'],
                        'THUMB_HEIGHT'  => $objGallery->fields['thumb_height'],
                        'THUMB_COLOR'   => $objGallery->fields['thumb_color'],
                        'VIEW_WIDTH'    => $objGallery->fields['view_width'],
                        'VIEW_HEIGHT'   => $objGallery->fields['view_height'],
                        'VIEW_COLOR'    => $objGallery->fields['view_color'],
                        'NB_COL'        => $objGallery->fields['nb_col'],
                        'NB_LINE'       => $objGallery->fields['nb_line'],
                        'ID_UNIQ'       => uniqid(),
                        'CPT'           => substr('0'.$intCpt,-2),
                        'NUM_LINE'      => $intLine,
                        'NUM_COL'       => $intCol
                        )
                    );
                    
                    if($intCol == $objGallery->fields['nb_col'])
                    {
                        $intCol = 0;
                        $intLine++;
                        $booInitLine = true;
                        $template_gallery->assign_block_vars($strNameCurlTpl.'.line', array());
                    }
                    $intCol++;
                    $intCpt++;
                }
                $arrGalleryOnce[$headingid.'-'.$articleid.'-'.$objGallery->fields['template']] = true;
            }
            else
            {
                $template_gallery->assign_block_vars('gallery_no_pict', array('MESS' => _GALLERY_FRONT_NO_PICT));
            }
        }
        else
        {
            $template_gallery->assign_block_vars('gallery_no_pict', array('MESS' => _GALLERY_FRONT_NO_PICT));
        }
    }
    
    $template_gallery->pparse('gallery_display');
    
}
else echo "ERREUR : template gallery.tpl manquant !";
?>
