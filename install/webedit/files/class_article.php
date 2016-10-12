<?php
/*
    Copyright (c) 2007-2016 Ovensia
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
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

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

include_once './modules/webedit/class_docfile.php';

include_once './modules/webedit/class_article_object.php';

/**
 * Classe d'accès aux table ploopi_mod_webedit_article et ploopi_mod_webedit_article_draft
 *
 * @package webedit
 * @subpackage article
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Stéphane Escaich
 */

class webedit_article extends ploopi\data_object
{
    private $original_content;

    /**
     * Constructeur de la classe
     *
     * @param string $type type d'article ('draft' / '')
     * @return webedit_article
     */

    public function __construct($type = '')
    {
        if ($type == 'draft') parent::__construct('ploopi_mod_webedit_article_draft');
        else parent::__construct('ploopi_mod_webedit_article');

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

    public function open(...$args)
    {
        $res = parent::open($args);

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

    public function save()
    {
        if (empty($this->fields['metatitle'])) $this->fields['metatitle'] = $this->fields['title'];

        if (empty($this->fields['timestp'])) $this->fields['timestp'] = ploopi\date::createtimestamp();

        $this->fields['content'] = preg_replace('/<span[^>]*contenteditable="false"[^>]*>\[\[(.*)\]\]<\/span>/i', '[[$1]]', $this->fields['content']);

        $this->fields['content_cleaned'] = $this->fields['content'];

        // filtre activé ?
        if (!$this->fields['disabledfilter']) $this->fields['content_cleaned'] = ploopi\str::htmlpurifier($this->fields['content_cleaned'], true);

        // Nettoyage des tags
        // Note : les tags ne sont réellement enregistrés qu'à la publication
        if (!empty($this->fields['tags']))
        {
            list($tags) = ploopi\str::getwords($this->fields['tags'], true, false, false);
            $this->fields['tags'] = implode(' ', array_keys($tags));
        }

        $res = parent::save();
        if ($this->gettablename() == 'ploopi_mod_webedit_article_draft' && $this->fields['content'] != $this->original_content)
        {
            $article_backup = new webedit_article_backup();
            $article_backup->fields['id_article'] = $this->fields['id'];
            $article_backup->fields['content'] = $this->fields['content'];
            $article_backup->fields['timestp'] = ploopi\date::createtimestamp();
            $article_backup->setuwm();
            $article_backup->save();
        }
        return($res);
    }

    /**
     * Supprime l'article et les données associées (sauvegardes, index du moteur de recherche)
     */

    public function delete()
    {
        $db = ploopi\db::get();

        // mise à jour de la position des autres articles de la rubrique
        $db->query("UPDATE `".$this->gettablename()."` SET position = position - 1 WHERE position > {$this->fields['position']} AND id_heading = {$this->fields['id_heading']}");

        // si brouillon, suppression de l'article associé
        if ($this->gettablename() == 'ploopi_mod_webedit_article_draft')
        {
            $article = new webedit_article();
            if($article->open($this->fields['id'])) $article->delete();
        }
        else
        {
            // suppression des sauvegardes
            $db->query("DELETE FROM ploopi_mod_webedit_article_backup WHERE id_article = {$this->fields['id']}");
            // suppression des commentaires
            $db->query("DELETE FROM ploopi_mod_webedit_article_comment WHERE id_article = {$this->fields['id']}");

            // suppression de l'index
            ploopi\search_index::remove(_WEBEDIT_OBJECT_ARTICLE_PUBLIC, $this->fields['id']);
        }

        parent::delete();
    }

    /**
     * Publie un article (copie le contenu du brouillon dans l'article en ligne)
     *
     * @return boolean true s'il s'agit d'une première publication
     */

    public function publish()
    {
        $db = ploopi\db::get();

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
            $article->fields['comments_allowed'] = $this->fields['comments_allowed'];
            $article->fields['width'] = $this->fields['width'];
            $article->fields['height'] = $this->fields['height'];
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

    public function index()
    {
        $db = ploopi\db::get();

        // Suppression des docs rattachés à l'article (on les récrée par la suite)
        $db->query("DELETE FROM ploopi_mod_webedit_docfile WHERE id_article = {$this->fields['id']}");

        // Recherche des liens vers des documents (du module doc)
        preg_match_all('/index-quick\.php[^\"]+docfile_md5id=([a-z0-9]{32})/i', ploopi\str::html_entity_decode($this->fields['content']), $arrMatches);

        if (!empty($arrMatches[1]) && file_exists('./modules/doc/class_docfile.php'))
        {
            include_once './modules/doc/class_docfile.php';

            foreach($arrMatches[1] as $doc_md5id)
            {
                $objDocFile = new docfile();

                if ($objDocFile->openmd5($doc_md5id))
                {
                    $objWebEditDocFile = new webedit_docfile();
                    if (!$objWebEditDocFile->open($this->fields['id'], $objDocFile->fields['id']))
                    {
                        $objWebEditDocFile->fields['id_article'] = $this->fields['id'];
                        $objWebEditDocFile->fields['id_docfile'] = $objDocFile->fields['id'];
                        $objWebEditDocFile->fields['md5id_docfile'] = $objDocFile->fields['md5id'];
                        $objWebEditDocFile->fields['id_module_docfile'] = $objDocFile->fields['id_module'];
                        $objWebEditDocFile->fields['id_module'] = $_SESSION['ploopi']['moduleid'];
                        $objWebEditDocFile->save();
                    }
                }
            }
        }

        // Suppression des objets rattachés à l'article (on les récrée par la suite)
        $db->query("DELETE FROM ploopi_mod_webedit_article_object WHERE id_article = {$this->fields['id']}");

        // Recherche des objets insérés
        if (preg_match_all('@\[\[(\d+),(\d+)(,([^/]+))?/([^\]]*)\]\]@i', ploopi\str::html_entity_decode($this->fields['content']), $arrMatches) !== false)
        {
            foreach(array_keys($arrMatches[0]) as $intKey)
            {
                if (isset($_SESSION['ploopi']['modules'][$arrMatches[2][$intKey]])) // Module existe ?
                {
                    // Association des objets à l'article
                    $objArticleObject = new webedit_article_object();
                    if (!$objArticleObject->open($this->fields['id'], $arrMatches[1][$intKey], $_SESSION['ploopi']['modules'][$arrMatches[2][$intKey]]['id_module_type'], $arrMatches[2][$intKey], $arrMatches[4][$intKey]))
                    {
                        $objArticleObject->fields['id_article'] = $this->fields['id'];
                        $objArticleObject->fields['id_wce_object'] = $arrMatches[1][$intKey];
                        $objArticleObject->fields['id_module_type'] = $_SESSION['ploopi']['modules'][$arrMatches[2][$intKey]]['id_module_type'];
                        $objArticleObject->fields['id_module'] = $arrMatches[2][$intKey];
                        $objArticleObject->fields['id_record'] = $arrMatches[4][$intKey];
                        $objArticleObject->save();
                    }
                }
            }
        }


        // suppression des liens article-tags existants
        $sql = "DELETE FROM ploopi_mod_webedit_article_tag WHERE id_article = {$this->fields['id']}";
        $db->query($sql);

        // récupération des tags
        list($tags) = ploopi\str::getwords($this->fields['tags'], true, false, false);
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

        ploopi\search_index::add(_WEBEDIT_OBJECT_ARTICLE_PUBLIC, $this->fields['id'], $this->fields['title'], strip_tags(ploopi\str::html_entity_decode($this->fields['content'])), "{$this->fields['metatitle']} {$this->fields['metakeywords']} {$this->fields['metadescription']}", true, $this->fields['timestp'], $this->fields['lastupdate_timestp']);
    }

    /**
     * Détermine si l'accès à cet article est autorisé
     *
     * @return true si l'accès est autorisé
     */

    public function isenabled()
    {
        include_once './modules/webedit/class_heading.php';

        $heading = new webedit_heading();

        $today = ploopi\date::createtimestamp();
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

    public function geturl()
    {
        ploopi\module::init('webedit', false, false, false);
        return(ploopi\str::urlrewrite("index.php?headingid={$this->fields['id_heading']}&articleid={$this->fields['id']}", webedit_getrewriterules(), $this->fields['metatitle']));
    }

    /**
     * Retourne un tableau contenant les tags (étiquettes) associés à l'article
     *
     * @return array tableau des tags
     */

    public function gettags()
    {
        $db = ploopi\db::get();
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
