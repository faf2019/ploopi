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
 * Gestion des articles
 *
 * @package webedit
 * @subpackage article
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

/**
 * Inclusion de la classe parent.
 */

include_once './include/classes/data_object.php';

/**
 * Inclusion de la classe article_backup qui permet de gérer la sauvegarde des versions antérieures.
 */

include_once './modules/webedit/class_article_backup.php';

/**
 * Inclusion de la classe tag qui permet de gérer les étiquettes associées aux articles
 */
include_once './modules/webedit/class_tag.php';

/**
 * Inclusion de la classe article_tag qui permet de gérer les étiquettes associées aux articles
 */
include_once './modules/webedit/class_article_tag.php';

/**
 * Classe d'accès aux table ploopi_mod_webedit_article et ploopi_mod_webedit_article_draft
 *
 * @package webedit
 * @subpackage article
 * @copyright Netlor, Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class webedit_article extends data_object
{
    private $original_content;

    /**
     * Constructeur de la classe
     *
     * @param string $type type d'article ('draft' / '')
     * @return webedit_article
     */

    function webedit_article($type = '')
    {
        if ($type == 'draft') parent::data_object('ploopi_mod_webedit_article_draft');
        else parent::data_object('ploopi_mod_webedit_article');

        $this->original_content = '';
        $this->fields['status'] = 'edit';
        $this->fields['content'] = '';
    }

    /**
     * Ouvre un article
     *
     * @param int $id identifiant de l'article
     * @return boolean true si l'article a été ouvert
     */

    function open($id)
    {
        $res = parent::open($id);

        if ($res) $this->original_content = $this->fields['content'];

        return $res;
    }

    /**
     * Enregistre l'article
     *
     * @return int identifiant de l'article
     *
     * @copyright Ovensia
     * @author Stéphane Escaich
     */

    function save()
    {
        if (empty($this->fields['metatitle'])) $this->fields['metatitle'] = $this->fields['title'];

        if (empty($this->fields['timestp'])) $this->fields['timestp'] = ploopi_createtimestamp();

        // Cas particulier des liens vers des documents (+ images) DIMS
        preg_match_all('/"(\.\/index-quick\.php\?dims_url=([^\"]*))"/i' , $this->fields['content'], $matches);

        if (!empty($matches[2]))
        {
            $arrReplace = array();
            $arrSearch = array();

            foreach($matches[2] as $key => $url)
            {
                $arrSearch[] = $matches[1][$key];
                $arrReplace[] = 'index-quick.php?'.str_replace('dims_op', 'ploopi_op', ploopi_base64_decode($url));
            }

            $this->fields['content'] = str_replace($arrSearch, $arrReplace, $this->fields['content']);
        }

        $this->fields['content_cleaned'] = $this->fields['content'];

        // filtre activé ?
        if (!$this->fields['disabledfilter']) $this->fields['content_cleaned'] = ploopi_htmlpurifier($this->fields['content_cleaned']);

        // Nettoyage des tags
        // Note : les tags ne sont réellement enregistrés qu'à la publication
        if (!empty($this->fields['tags']))
        {
            list($tags) = ploopi_getwords($this->fields['tags'], true, false, false);
            $this->fields['tags'] = implode(' ', array_keys($tags));
        }

        $res = parent::save();
        if ($this->gettablename() == 'ploopi_mod_webedit_article_draft' && $this->fields['content'] != $this->original_content)
        {
            $article_backup = new webedit_article_backup();
            $article_backup->fields['id_article'] = $this->fields['id'];
            $article_backup->fields['content'] = $this->fields['content'];
            $article_backup->fields['timestp'] = ploopi_createtimestamp();
            $article_backup->setuwm();
            $article_backup->save();
        }
        return($res);
    }

    /**
     * Supprime l'article et les données associées (sauvegardes, index du moteur de recherche)
     */

    function delete()
    {
        global $db;

        // mise à jour de la position des autres articles de la rubrique
        $db->query("UPDATE `".$this->gettablename()."` SET position = position - 1 WHERE position > {$this->fields['position']} AND id_heading = {$this->fields['id_heading']}");

        // si brouillon, suppression de l'article associé
        if ($this->gettablename() == 'ploopi_mod_webedit_article_draft')
        {
            $article = new webedit_article();
            $article->open($this->fields['id']);
            $article->delete();
        }
        else
        {
            // suppression des sauvegardes
            $db->query("DELETE FROM ploopi_mod_webedit_article_backup WHERE id_article = {$this->fields['id']}");

            // suppression de l'index
            ploopi_search_remove_index(_WEBEDIT_OBJECT_ARTICLE_PUBLIC, $this->fields['id']);
        }

        parent::delete();
    }

    /**
     * Publie un article (copie le contenu du brouillon dans l'article en ligne)
     *
     * @return boolean true s'il s'agit d'une première publication
     */

    function publish()
    {
        global $db;

        if ($this->gettablename() == 'ploopi_mod_webedit_article_draft')
        {
            $article = new webedit_article();
            $new = !$article->open($this->fields['id']);

            $article->fields['reference'] = $this->fields['reference'];
            $article->fields['title'] = $this->fields['title'];
            $article->fields['content'] = $this->fields['content'];
            $article->fields['content_cleaned'] = $this->fields['content_cleaned'];
            $article->fields['metatitle'] = $this->fields['metatitle'];
            $article->fields['metakeywords'] = $this->fields['metakeywords'];
            $article->fields['metadescription'] = $this->fields['metadescription'];
            $article->fields['tags'] = $this->fields['tags'];
            $article->fields['author'] = $this->fields['author'];
            $article->fields['version'] = $this->fields['version'];
            $article->fields['visible'] = $this->fields['visible'];
            $article->fields['timestp'] = $this->fields['timestp'];
            $article->fields['timestp_published'] = $this->fields['timestp_published'];
            $article->fields['timestp_unpublished'] = $this->fields['timestp_unpublished'];
            $article->fields['lastupdate_timestp'] = $this->fields['lastupdate_timestp'];
            $article->fields['lastupdate_id_user'] = $this->fields['lastupdate_id_user'];
            $article->fields['id_heading'] = $this->fields['id_heading'];
            $article->fields['id_module'] = $this->fields['id_module'];
            $article->fields['id_user'] = $this->fields['id_user'];
            $article->fields['id_workspace'] = $this->fields['id_workspace'];
            $article->fields['position'] = $this->fields['position'];
            $article->fields['disabledfilter'] = $this->fields['disabledfilter'];
            $article->fields['headcontent'] = $this->fields['headcontent'];
            $article->save();

            $this->index();

            // update article positions
            $sql =  "
                    UPDATE  ploopi_mod_webedit_article_draft draft,
                            ploopi_mod_webedit_article article

                    SET     article.position = draft.position
                    WHERE   article.id = draft.id
                    AND     draft.id_heading = {$this->fields['id_heading']}
                    ";

            $db->query($sql);

            $this->fields['status'] = 'edit';

            return $new;
        }

        return -1;
    }

    function index()
    {
        global $db;

        // Suppression des docs rattachés à l'article (on le récrée par la suite)
        $db->query("DELETE FROM ploopi_mod_webedit_docfile WHERE id_article = {$this->fields['id']}");

        // Recherche des liens vers des documents (du module doc)
        preg_match_all('/<a[^>]*href="(index-quick\.php\?ploopi_op=doc_file_download\&docfile_md5id=([a-z0-9]{32}))"[^>]*>([^>]*)<\/a>/i' , html_entity_decode($this->fields['content']), $matches);

        if (!empty($matches[2]) && file_exists('./modules/doc/class_docfile.php'))
        {
            include_once './modules/doc/class_docfile.php';
            include_once './modules/webedit/class_docfile.php';

            foreach($matches[2] as $doc_md5id)
            {
                $objDocFile = new docfile();

                if ($objDocFile->openmd5($doc_md5id))
                {
                    $objWebEditDocFile = new webedit_docfile();
                    //$objWebEditDocFile->open($this->fields['id'], $objDocFile->fields['id']);
                    $objWebEditDocFile->fields['id_article'] = $this->fields['id'];
                    $objWebEditDocFile->fields['id_docfile'] = $objDocFile->fields['id'];
                    $objWebEditDocFile->fields['md5id_docfile'] = $objDocFile->fields['md5id'];
                    $objWebEditDocFile->fields['id_module_docfile'] = $objDocFile->fields['id_module'];
                    $objWebEditDocFile->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
                    $objWebEditDocFile->save();
                }
            }
        }

        // suppression des liens article-tags existants
        $sql = "DELETE FROM ploopi_mod_webedit_article_tag WHERE id_article = {$this->fields['id']}";
        $db->query($sql);

        // récupération des tags
        list($tags) = ploopi_getwords($this->fields['tags'], true, false, false);
        $tags = array_keys($tags);
        foreach($tags as $tag)
        {
            $select = "SELECT id FROM ploopi_mod_webedit_tag WHERE tag = '".$db->addslashes($tag)."' AND id_module = {$this->fields['id_module']}";
            $rs = $db->query($select);
            if (!($row = $db->fetchrow($rs)))
            {
                $objTag = new webedit_tag();
                $objTag->fields['tag'] = $tag;
                $objTag->fields['id_module'] = $this->fields['id_module'];
                $id_tag = $objTag->save();
            }
            else $id_tag = $row['id'];

            $objArticleTag = new webedit_article_tag();
            $objArticleTag->fields['id_tag'] = $id_tag;
            $objArticleTag->fields['id_article'] = $this->fields['id'];
            $objArticleTag->save();
        }

        ploopi_search_create_index(_WEBEDIT_OBJECT_ARTICLE_PUBLIC, $this->fields['id'], $this->fields['title'], strip_tags(html_entity_decode($this->fields['content'])), "{$this->fields['metatitle']} {$this->fields['metakeywords']} {$this->fields['metadescription']}", true, $this->fields['timestp'], $this->fields['lastupdate_timestp']);
    }

    /**
     * Détermine si l'accès à cet article est autorisé
     *
     * @return true si l'accès est autorisé
     */

    function isenabled()
    {
        include_once './modules/webedit/class_heading.php';

        $heading = new webedit_heading();

        $today = ploopi_createtimestamp();
        return (
                    ($this->fields['timestp_published'] <= $today || empty($this->fields['timestp_published'])) &&
                    ($this->fields['timestp_unpublished'] >= $today || empty($this->fields['timestp_unpublished'])) &&
                    $heading->open($this->fields['id_heading'])
                );
    }

    /**
     * Retourne l'URL publique de l'article
     *
     * @return string url de l'article
     */

    function geturl()
    {
        ploopi_init_module('webedit', false, false, false);
        return(ploopi_urlrewrite("index.php?headingid={$this->fields['id_heading']}&articleid={$this->fields['id']}", webedit_getrewriterules(), $this->fields['metatitle']));
    }

    /**
     * Retourne un tableau contenant les tags (étiquettes) associés à l'article
     *
     * @return array tableau des tags
     */

    function gettags()
    {
        global $db;
        if (!$this->new)
        {
            $sql =  "
                    SELECT  t.*
                    FROM    ploopi_mod_webedit_tag t,
                            ploopi_mod_webedit_article_tag at
                    WHERE   t.id = at.id_tag
                    AND     at.id_article = {$this->fields['id']}
                    ";

            $db->query($sql);

            return($db->getarray());
        }
        else return(array());
    }

}
?>
