<?php
/*
    Copyright (c) 2007-2020 Ovensia
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

namespace ploopi\news2;

use ploopi;


abstract class tools
{

    // Action : Une au moins
    const ACTION_ANY        = -1;

    // Action : Ajouter une news
    const ACTION_WRITE      = 1;

    // Action : Modifier une news
    const ACTION_MODIFY     = 2;

    // Action : Supprimer une news
    const ACTION_DELETE     = 3;

    // Action : Publier une news
    const ACTION_PUBLISH    = 4;

    // Action : Gérer les catégories
    const ACTION_MANAGECAT  = 5;

    // Objet NEWS2
    const OBJECT_NEWS2      = 1;


    /**
     * Liste des news du module
     */
    public static function getNews($moduleid, $published = true, $limit = 0) {
        $objQuery = new ploopi\query_select();
        $objQuery->add_select('ploopi_mod_news2_entry.*');
        $objQuery->add_select("IFNULL(ploopi_mod_news2_cat.title, 'Inconnue') as titlecat");
        $objQuery->add_from('ploopi_mod_news2_entry');
        $objQuery->add_leftjoin('ploopi_mod_news2_cat ON ploopi_mod_news2_cat.id = ploopi_mod_news2_entry.id_cat');
        $objQuery->add_where("ploopi_mod_news2_entry.id_module = ".$moduleid);
        $objQuery->add_where("ploopi_mod_news2_entry.id_workspace IN (".ploopi\system::viewworkspaces().")");
        if ($published) $objQuery->add_where("ploopi_mod_news2_entry.published = 1");
        $objQuery->add_orderby('date_publish desc,titlecat');
        if ($limit > 0) $objQuery->add_limit($limit);
        return $objQuery->execute();
    }


    /**
     * Liste des catégories de news du module
     */
    public static function getCategories($moduleid) {
        $objQuery = new ploopi\query_select();
        $objQuery->add_from('ploopi_mod_news2_cat');
        $objQuery->add_where("id_module = ".$moduleid);
        $objQuery->add_orderby('title');
        return $objQuery->execute();
    }


    /**
     * Tableau des catégories de news du module
     */
    public static function getCategoriesArray($moduleid) {
        $arrCat = [ 0 => '(Aucune Catégorie)' ];
        $objRs = self::getCategories($moduleid);
        while ($cat = $objRs->fetchrow()) {
            $arrCat[$cat['id']] = $cat['title'];
        }
        return $arrCat;
    }

    // Ajoute une saisie d'image
    // N'utilise pas la fonction standard pour intégrer le bouton
    // dans la même zone que la saisie de texte
    static function addImg($panel,$curFld,$field,$lab,$pre) {
        $panel->addField(
            new ploopi\form_html(
                '<div id="'.$pre.$curFld.'_form"><label for="'
                .$pre.$curFld.'">'.$lab.'<span></span></label>
                <input type="text" name="'.$pre.$curFld.'" id="'
                .$pre.$curFld.'" value="'.$field.'" placeholder="URL de l\'image" />
                <img src="./modules/news2/img/find_image-32_clr.png" id="'.$pre.$curFld.'_img"
                onclick="news2_search_img(\''.$pre.$curFld.'\');"/></div>'
            )
        );
    }


    // Ajoute unlien interne vers une rubrique ou un article
    // N'utilise pas la fonction standard pour intégrer le bouton
    // dans la même zone que la saisie de texte
    static function addLink($panel,$curFld,$field,$lab,$pre) {
        $panel->addField(
            new ploopi\form_html(
                '<div id="'.$pre.$curFld.'_form"><label for="'
                .$pre.$curFld.'">'.$lab.'<span></span></label>
                <input type="text" name="'.$pre.$curFld.'" id="'
                .$pre.$curFld.'" value="'.$field.'" placeholder="Lien interne ou externe" />'
                .'<img src="./modules/news2/img/ico_choose_article.png" '
                .'style="display:block;float:left;cursor:pointer;margin:2px 4px;" '
                .'title="Choisir un article ou une rubrique" alt="Choisir" '
                .'onclick="javascript:ploopi.popup.show(ploopi.xhr.send('
                .'\'admin-light.php\',\'ploopi_env=\'+_PLOOPI_ENV+\'&amp;ploopi_op=news2_selectredirect\',false)'
                .', 300, event, true,\'news2_popup_selectredirect\');"/>'
                .'</div>'
            )
        );
    }


    static function news2_getrewriterules() {
        return array(
            'patterns' => array(
                // Blog
                '/index.php\?headingid=([0-9]*)&numpage=([0-9]*)&yearmonth=([0-9]{6})&day=([0-9]{2})/',
                '/index.php\?headingid=([0-9]*)&numpage=([0-9]*)&yearmonth=([0-9]{6})/',
                '/index.php\?headingid=([0-9]*)&numpage=([0-9]*)&year=([0-9]{4})/',
                '/index.php\?headingid=([0-9]*)&numpage=([0-9]*)/',
                '/index.php\?headingid=([0-9]*)&yearmonth=([0-9]{6})&day=([0-9]{2})/',
                '/index.php\?headingid=([0-9]*)&yearmonth=([0-9]{6}|<YEARMONTH>)/',
                '/index.php\?headingid=([0-9]*)&year=([0-9]{4}|<YEAR>)/',
                // Article
                '/index.php\?headingid=([0-9]*)&articleid=([0-9]*)&comment_return=([0-9]*)/',
                '/index.php\?headingid=([0-9]*)&articleid=([0-9]*)/',
                '/index.php\?headingid=([0-9]*)&comment_return=([0-9]*)/',
                '/index.php\?headingid=([0-9]*)/',
                '/index.php\?articleid=([0-9]*)&comment_return=([0-9]*)/',
                '/index.php\?articleid=([0-9]*)/',
                // Divers
                '/index.php\?ploopi_op=webedit_unsubscribe&subscription_email=([a-z0-9]{32})/',
                '/index.php\?query_tag=([a-zA-Z0-9]*)/',
                '/index.php\?ploopi_op=webedit_backend&format=([a-z]*)&headingid=([0-9]*)/',
                '/index.php\?ploopi_op=webedit_backend&format=([a-z]*)/',
                '/index.php\?ploopi_op=webedit_backend&query_tag=([a-zA-Z0-9]*)&moduleid=([0-9]*)/',

            ),

            'replacements' => array(
                // Blog
                'blog/<FOLDERS><TITLE>-h$1p$2ym$3d$4.<EXT>',
                'blog/<FOLDERS><TITLE>-h$1p$2ym$3.<EXT>',
                'blog/<FOLDERS><TITLE>-h$1p$2y$3.<EXT>',
                'blog/<FOLDERS><TITLE>-h$1p$2.<EXT>',
                'blog/<FOLDERS><TITLE>-h$1ym$2d$3.<EXT>',
                'blog/<FOLDERS><TITLE>-h$1ym$2.<EXT>',
                'blog/<FOLDERS><TITLE>-h$1y$2.<EXT>',
                // Article
                'articles/<FOLDERS><TITLE>-h$1a$2r$3.<EXT>', // avec reponse (de commentaire par ex.)
                'articles/<FOLDERS><TITLE>-h$1a$2.<EXT>',
                'articles/<FOLDERS><TITLE>-h$1r$2.<EXT>', // avec reponse (de commentaire par ex.)
                'articles/<FOLDERS><TITLE>-h$1.<EXT>',
                'articles/<FOLDERS><TITLE>-a$1r$2.<EXT>', // avec reponse (de commentaire par ex.)
                'articles/<FOLDERS><TITLE>-a$1.<EXT>',
                // Divers
                'unsubscribe/$1/index.<EXT>',
                'tags/$1.<EXT>',
                'web/$1/<TITLE>-h$2.xml',
                'web/$1/<TITLE>.xml',
                'tag3D/$1/$2.xml'
                )
        );
    }



    static function news2_gettreeview(
        $arrHeadings = array(),
        $articles = array(),
        $moduleid = -1,
        $onclickfct = 'news2_select_article_or_heading'
    ) {
        //ploopi\module::init('webedit');
        include_once './modules/webedit/class_heading.php';
        include_once './modules/webedit/class_article.php';

        // ploopi\loader::getdb();

        $prefix = 'r';
        $treeview = array('list' => array(), 'tree' => array());
        if (!empty($arrHeadings['list'])) {
            foreach($arrHeadings['list'] as $id => $fields) {
                $link = '';
                $arrParents = array();
                if (isset($arrHeadings['list'][$fields['id_heading']]))
                    foreach(explode(';', $arrHeadings['list'][$fields['id_heading']]['parents']) as $hid_parent)
                        if (isset($arrHeadings['list'][$hid_parent]))
                            $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                $linkedurl = ploopi\str::urlrewrite(
                    "index.php?headingid={$fields['id']}",
                    self::news2_getrewriterules(),
                    $fields['label'], $arrParents
                );
                $onclick = "{$onclickfct}('{$linkedurl}', '".addslashes($fields['label'])."')";
                //$onclick = "{$onclickfct}('{$fields['id']}', '', '".addslashes($fields['label'])."')";
                $node = array(
                    'id' => 'h'.$prefix.$fields['id'],
                    'label' => $fields['label'],
                    'description' => $fields['description'],
                    'parents' => preg_split('/;/', $fields['parents']),
                    'node_link' => '',
                    'node_onclick' =>
                        "ploopi.skin.treeview_shownode('h{$prefix}{$fields['id']}', '"
                        .ploopi\crypt::queryencode("ploopi_op=news2_detail_heading&hid=h{$prefix}{$fields['id']}")
                        ."', 'admin-light.php')",
                    'link' => $link,
                    'onclick' => $onclick,
                    'icon' => (
                        $fields['id_heading'] == 0)
                        ? './modules/webedit/img/base.png'
                        : './modules/webedit/img/folder.png'
                );
                // on rajoute 'h' devant chaque parent
                foreach($node['parents'] as $key => $value) $node['parents'][$key] = 'h'.$prefix.$value;
                $treeview['list']['h'.$prefix.$fields['id']] = $node;
                $treeview['tree']['h'.$prefix.$fields['id_heading']][] = 'h'.$prefix.$fields['id'];
            }
        }

        $today = ploopi\date::createtimestamp();
        if (!empty($articles['list'])) {
            foreach($articles['list'] as $id => $fields) {
                if (isset($treeview['list']['h'.$prefix.$fields['id_heading']])) {
                    $status =
                        ($fields['status'] == 'wait')
                        ? '<sup style="margin-left:2px;color:#ff0000;font-weight:bold;">*</sup>'
                        : '';
                    $dateok =
                        ($fields['date_ok'])
                        ? ''
                        : '<sup style="margin-left:2px;color:#ff0000;font-weight:bold;">~</sup>';
                    $link = '';
                    $arrParents = array();
                    if (isset($arrHeadings['list'][$fields['id_heading']]))
                        foreach(explode(';', $arrHeadings['list'][$fields['id_heading']]['parents']) as $hid_parent)
                            if (isset($arrHeadings['list'][$hid_parent]))
                                $arrParents[] = $arrHeadings['list'][$hid_parent]['label'];
                    $linkedurl = ploopi\str::urlrewrite(
                        "index.php?headingid={$fields['id_heading']}&articleid={$fields['id']}",
                        self::news2_getrewriterules(),
                        $fields['title'],
                        $arrParents
                    );
                    $onclick = "{$onclickfct}('{$linkedurl}', '".addslashes($fields['title'])."')";
                    $node = array(
                        'id' => 'a'.$prefix.$fields['id'],
                        'label' => $fields['title'],
                        'status' => $status.$dateok,
                        'description' => $fields['metadescription'],
                        'parents' => array_merge(
                            $treeview['list']['h'.$prefix.$fields['id_heading']]['parents'],
                            array('h'.$prefix.$fields['id_heading'])
                        ),
                        'node_link' => '',
                        'node_onclick' => '',
                        'link' => $link,
                        'onclick' => $onclick,
                        'icon' => "./modules/webedit/img/doc{$fields['new_version']}.png"
                    );
                    $treeview['list']['a'.$prefix.$fields['id']] = $node;
                    $treeview['tree']['h'.$prefix.$fields['id_heading']][] = 'a'.$prefix.$fields['id'];
                }
            }
        }
        return($treeview);
    }

}
