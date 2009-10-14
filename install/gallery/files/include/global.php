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
 * Fonctions, constantes, variables globales
 *
 * @package gallery
 * @subpackage global
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

Define ('_GALLERY_OBJECT_GALLERY',          1);

define ('_GALLERY_ACTION_ADD_GALLERY',      1);
define ('_GALLERY_ACTION_MODIFY_GALLERY',   2);
define ('_GALLERY_ACTION_DELETE_GALLERY',   3);

define ('_GALLERY_ACTION_ADMIN_GALLERY',    10);

define ('_GALLERY_TAB_LIST',        1);
define ('_GALLERY_TAB_NEW',         2);
define ('_GALLERY_TAB_EDIT',        3);

function gallery_show_directories($arrData, $level = 0)
{
    if(isset($arrData[0])) $arrData = $arrData[0];
    $cpt = 1;
    
    $arr_depth = count($arrData);
    
    foreach($arrData as $id => $data)
    {
        
        $is_last = ($cpt == $arr_depth);
        
        $bg = '';
        
        $type_node = 'join';
        
        if(isset($data['id']))
        {
            $id_directorie = 'id="gallery_treeview_dir_'.$data['id'].'"';
            $name = $data['name'];
            $cursor = 'cursor: pointer;';
            $onClick = 'onclick="javascript:ploopi_checkbox_click(event, \'_gallery_directory_'.$id.'\');"';
        }
        else
        {
            $id_directorie = '';
            $name = '<i>'._GALLERY_EDIT_DIRECTORY_PRIVATE.'</i>';
            $cursor = '';
            $onClick = '';
        }
        
        if (!$is_last)
        {
            $type_node .= 'bottom';
            $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/treeview/line.png) 0 0 repeat-y;";
        }
        ?>
        <div class="treeview_node" id="treeview_directory_<?php echo $id; ?>" style=" <?php echo $cursor.$bg; ?>">
            <div <?php echo $onClick; ?> style="clear: both; padding: 0; margin: 0;">
                <img style="display:block; float:left; padding:0; margin: 0;" src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/treeview/<?php echo $type_node; ?>.png" />
                <img src="./modules/gallery/img/folder.png" style="padding:0; margin: 0;"/>
                <?php
                if(isset($data['id']))
                {
                    $select = ($data['dir_selected']) ? 'checked="checked"' : '';
                    echo '<input style="display:block; margin: 1px 2px 0 2px; padding: 0; height: 15px; float: left;" type="checkbox" name="_gallery_directory[]" id="_gallery_directory_'.$id.'" value="'.$data['id'].'" onchange="javascript:gallery_show_preview_rep('.$data['id'].')" '.$select.'}/>';
                }
                ?>
                <div <?php echo $id_directorie; ?> class="gallery_treeview_dir" ><?php echo $name; ?></div>
            </div>
            <?php 
            if(isset($data['child']))
            {
                ?>
                <div style="margin-left: 20px; display:block;" id="n<?php echo $id; ?>">
                    <?php gallery_show_directories($data['child'],$level+1); ?>
                </div>
                <?php
            } 
            ?>
        </div>
        <?php
        $cpt = $cpt+1;
    }
}

/**
 * Affiche un découpage par page
 *
 * @param string $form_id Nom unique du form
 * @param array $param propriétés du découpage
 *
 * @return code html des pages
 *         $POST: $form_id+_begin = début du limit
 *                $form_id+_page  = page cliquée
 *                $form_id+_by    = by selectionné
 *
 * propriétés du découpage :
 *      - nbmax   : nombre d'enregistrement total
 *      - by      : nb enregistrement par page
 *      - page    : page en cours
 *      - action  : action à passer au form (optionnel)
 *      - post    : liste de input hidden a passer = array(id/name => value,...) (optionnel)
 *      - answerby: liste des découpages à porposer = array(10,25,50,100,...) (optionnel)
 *
 */

function gallery_cut_page($form_id,$param)
{
  if(empty($form_id)) return '';
  if(!isset($param['nbMax']) || $param['nbMax'] <= 0) return '';
  if(!isset($param['by']) || $param['by'] <= 0 || $param['nbMax'] <= $param['by']) return '';

  $nbPage = ceil($param['nbMax']/$param['by']); // forcement > 1 a cause du test "$param['nbMax'] <= $param['by']" juste au dessus

  //Correction de la page actuel au cas où...
  if(!isset($param['page']) || (isset($param['page']) && $param['page'] < 1))  $param['page'] = 1;
  if($param['page'] > $nbPage ) $param['page'] = $nbPage;

  $html = '<form action="" id="'.$form_id.'" name="'.$form_id.'" method="post">
           <input type="hidden" name="id_cut_page" value="'.$form_id.'">
           <input type="hidden" id="'.$form_id.'_page" name="'.$form_id.'_page" value="'.$param['page'].'">
           <div class="gallery2_page_cut">';

  /* Si on a moins de 5 pages */
  if($nbPage <= 5)
  {
    $html .= '<div>';
    /* ajout des Pages */
    for ($page = 1; $page <= $nbPage; $page++)
    {
      //class utilisée
      $class = ($page == $param['page']) ? 'gallery2_page_cut_select' : 'gallery2_page_cut';
      $html .= '<input type="submit" class="'.$class.'" value="'.$page.'" onClick="javascript:$(\''.$form_id.'_page\').value='.$page.';">';
    }
    $html .= '</div>';
  }
  else /* Si on a plus de 5 pages */
  {
    $html .= '<div>';
    /* Recherche où on est */
    if($param['page'] < 4) // On est au debut
    {
      $button = 'end';
      $debPage = 1;
      $maxPage = 5;
    }
    elseif($param['page'] > ($nbPage-4)) // on est à la fin
    {
      $button = 'begin';
      $debPage = ($nbPage-5);
      $maxPage = $nbPage;
    }
    else // on est au milieu...
    {
      $button = 'extrem';
      $debPage = ($param['page']-2);
      $maxPage = ($param['page']+2);
    }

    /* ajout des << et < (ou pas) */
    if($param['page'] > 1)
    {
      $html .= '<input type="submit" class="gallery2_page_cut" value="&lt;&lt;" onClick="javascript:$(\''.$form_id.'_page\').value=1;">';
      $html .= '<input type="submit" class="gallery2_page_cut" value="&lt;" onClick="javascript:$(\''.$form_id.'_page\').value='.($param['page']-1).';">';
    }

    if($button == 'begin' || $button == 'extrem')
      $html .= '<input type="submit" class="gallery2_page_cut_disable" value="...">';

    /* ajout des Pages */
    for ($page = $debPage; $page <= $maxPage; $page++)
    {
      //class utilisée
      $class = ($page == $param['page']) ? 'gallery2_page_cut_select' : 'gallery2_page_cut';
      $html .= '<input type="submit" class="'.$class.'" value="'.$page.'" onClick="javascript:$(\''.$form_id.'_page\').value='.$page.';">';
    }

    if($button == 'end' || $button == 'extrem')
      $html .= '<input type="button" class="gallery2_page_cut_disable" value="...">';

    /* ajout des > et >> (ou pas) */
    if($param['page'] < $nbPage)
    {
      $html .= '<input type="submit" class="gallery2_page_cut" value="&gt;" onClick="javascript:$(\''.$form_id.'_page\').value='.($param['page']+1).';">';
      $html .= '<input type="submit" class="gallery2_page_cut" value="&gt;&gt;" onClick="javascript:$(\''.$form_id.'_page\').value='.$nbPage.';">';
    }
    $html .= '</div>';
  }

  $html .= '</div></form>';

  return $html;
}
?>