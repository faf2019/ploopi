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
 * Gestion des commentaires sur les articles
 *
 * @package webedit
 * @subpackage comment
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Classe d'accès à la table ploopi_mod_webedit_article_comment
 *
 * @package webedit
 * @subpackage comment
 * @copyright HeXad
 * @license GNU General Public License (GPL)
 * @author Xavier Toussaint
 */

class webedit_article_comment extends data_object
{
    
    /**
     * Constructeur de la classe
     *
     * @return webedit_article_commentaire
     */
    function __construct()
    {
        parent::data_object('ploopi_mod_webedit_article_comment');
    }


    /**
     * Sauvegarde les commentaires
     * 
     * @return int identifiant du commentaire
     */
    public function save($moduleid = '', $workspaceid = '')
    {
        if(empty($moduleid) || !is_numeric($moduleid)) $moduleid = $_SESSION['ploopi']['moduleid'];
        if(empty($workspaceid) || !is_numeric($workspaceid)) $workspaceid = $_SESSION['ploopi']['workspaceid'];
        
        // Contrôle des commentaire a priori ?
        $this->fields['publish'] = ($_SESSION['ploopi']['modules'][$moduleid]['comment_ctrl'] == 1) ? 0 : 1; 
        
        $this->fields['timestp'] = ploopi_createtimestamp();
        $this->fields['id_module'] = $moduleid;
        $this->fields['id_workspace'] = $workspaceid;
        
        return parent::save();
    }

    /**
     * Publie un commentaire
     *
     * @return int identifiant du commentaire
     */
    public function publish($publish = true)
    {
        $this->fields['publish'] = $publish;
        return parent::save();
    }
    
    /**
     * Insère le bloc de commentaire dans la fiche article
     *
     * @param int $id_article identifiant de l'article
     */
    
    function admin_comment($id_article)
    {
        $_SESSION['comment'][$id_article] =
            array(
                'id_article' => $id_article,
            );
        ?>
        <div id="webeditcomment_<?php echo $id_article; ?>">
        <?php $this->admin_comment_refresh($id_article); ?>
        </div>
        <?php
    }
    
    
    /**
     * Rafraichit le bloc de commentaire d'un article
     *
     * @param string $id_article
     */
    
    function admin_comment_refresh($id_article)
    {
        global $db;
        
        $select =   "
                    SELECT      *
                    FROM        {$this->gettablename()} c
                    WHERE       c.id_article = '{$id_article}'
                    AND         c.id_module = {$_SESSION['ploopi']['moduleid']}
                    AND         c.id_workspace = {$_SESSION['ploopi']['workspaceid']}
                    ORDER BY    timestp ASC
                    ";
        $sql_comment = $db->query($select);
    
        $nbcomment = ($db->numrows($sql_comment)) ? $db->numrows($sql_comment) : 0;
        
        $nbPublish = $nbWaitPublish = 0;
        if($nbcomment > 0)
        {
            while ($fields = $db->fetchrow($sql_comment))
            {
                if($fields['publish']) 
                    $nbPublish++; 
                else 
                    $nbWaitPublish++;
            }
            $db->dataseek($sql_comment);
        }
                
        $comment_show = (isset($_SESSION['ploopi']['comment']['show'][$id_article]));

        $ajaxRequestShow = ploopi_urlencode('admin-light.php?ploopi_op=webedit_comment_show&id_article='.$id_article); 
        ?>
        <a name="comment_<?php echo $id_article; ?>" style="display:none;"></a>
        <div style="overflow:hidden;">
            <a id="comment_count_<?php echo $id_article; ?>" class="webedit_comment_viewlist" href="javascript:void(0);" onclick="javascript:$('comment_list_<?php echo $id_article; ?>').style.display=($('comment_list_<?php echo $id_article; ?>').style.display=='block') ? 'none' : 'block'; new Ajax.Request('<?php  echo $ajaxRequestShow ?>', { method: 'get'});">
                <img border="0" src="<?php echo $_SESSION['ploopi']['template_path']; ?>/img/system/comment.png">
                <span>
                    <?php echo $nbcomment; ?> commentaire<?php echo ($nbcomment>1) ? 's' : ''; 
                    if($nbWaitPublish) echo ' ('.$nbWaitPublish.' en attente)'; 
                    ?>
                </span>
            </a>
    
            <div style="overflow: auto; height: 250px; display:<?php echo ($comment_show) ? 'block' : 'none'; ?>;" id="comment_list_<?php echo $id_article; ?>">
                <?php
                
                while ($fields = $db->fetchrow($sql_comment))
                {
                    $ldate = ploopi_timestamp2local($fields['timestp']);
                    $numrow = (!isset($numrow) || $numrow == 2) ? 1 : 2;
                    ?>
                    <div class="webedit_comment_row_<?php echo $numrow; ?>">
                        <div style="padding: 2px 0 0 8px;">
                            <div style="float: left;">Par <strong><?php echo "{$fields['nickname']}"; ?></strong> le <?php echo $ldate['date']; ?> à <?php echo $ldate['time']; ?></div>
                            <?php 
                            if (ploopi_isactionallowed(_WEBEDIT_ACTION_COMMENT))
                            {
                                if ($fields['publish'])
                                {
                                    ?>
                                    <div class="webedit_comment_publish" onclick="javascript:webedit_comment_publish('<?php echo $fields['id']; ?>','<?php echo $fields['id_article']; ?>',false); return false;">[ Cacher ]</div>                                    
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <div class="webedit_comment_publish" style="color:#00a600;" onclick="javascript:webedit_comment_publish('<?php echo $fields['id']; ?>','<?php echo $fields['id_article']; ?>',true); return false;">[ Publier ]</div>
                                    <?php
                                }
                                ?>
                                <div style="float:right; padding: 0 8px 0 0;">
                                    <a href="javascript:void(0);" onclick="javascript:webedit_comment_delete('<?php echo $fields['id']; ?>','<?php echo $fields['id_article']; ?>'); return false;">supprimer</a>
                                </div>
                                <?php
                            }
                            else
                            {
                               if ($fields['publish'])
                               {
                                   ?>
                                   <div style="float: left; font-weight:bold; color:#a60000;">[ En attente ]</div>
                                   <?php
                               }
                            }
                            ?>
                        </div>
                        <div style="clear: both; margin:0; padding:0 0 0 8px;"><?php echo $fields['email']; ?></div>
                        <div style="clear: both; padding:2px 4px;"><?php echo $fields['comment']; ?></div>                        
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
}
?>
