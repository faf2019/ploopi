<?php
/*
    Copyright (c) 2007-2018 Ovensia
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

namespace ploopi;

use ploopi;

/**
 * Gestion de l'affichage d'éléments d'interface
 * Regroupe des méthodes génériques pour afficher blocs, menus, onglets, barres d'outils, popups, etc...
 *
 * @package ploopi
 * @subpackage skin
 * @copyright Ovensia, HeXad
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

class skin
{
    /**
     * Singleton
     *
     * @var skin
     */
    private static $_skin = null;

    public $values = [];

    /**
     * Constructeur
     *
     * @param string $skin nom du skin (nom du dossier)
     */

    public function __construct($skin)
    {
        $this->values = [];
        $this->values['path'] = "./templates/{$_SESSION['ploopi']['mode']}/{$skin}/img";
        $this->values['inifile'] = "./templates/{$_SESSION['ploopi']['mode']}/{$skin}/skin.ini";
        if (file_exists($this->values['inifile'])) $this->values = array_merge($this->values,parse_ini_file($this->values['inifile']));
    }

    /**
     * Accès au skin et initialisation du singleton
     *
     * @return skin singleton skin
     */
    public static function get() {
        if (is_null(self::$_skin)) {
            if ($_SESSION['ploopi']['mode'] == 'frontoffice') {
                if (!empty($_SESSION['ploopi']['frontoffice']['template_path']) && file_exists("{$_SESSION['ploopi']['frontoffice']['template_path']}/class_skin.php")) {
                    include_once "{$_SESSION['ploopi']['frontoffice']['template_path']}/class_skin.php";
                    self::$_skin = new \skin();
                }
            }
            else {
                include_once "{$_SESSION['ploopi']['template_path']}/class_skin.php";
                self::$_skin = new \skin();
            }
        }

        return self::$_skin;
    }

    /**
     * Gabarit de backoffice à servir pour un nom de gabarit.
     *
     * Une déclinaison (skin.ini : extends = <gabarit>, sans index.tpl) ne porte que sa
     * palette : les fichiers servis (gabarits, scripts, icônes, polices, feuilles) sont
     * ceux du gabarit étendu, et sa feuille css/palette.css est chargée après les autres.
     * Un seul niveau : le gabarit étendu ne peut pas être lui-même une déclinaison.
     *
     * @param string $strName nom du gabarit (dossier de templates/backoffice)
     * @return array|null ['path' => dossier servi, 'palette' => feuille de la déclinaison ou ''], null si le gabarit n'existe pas
     */

    public static function resolve($strName)
    {
        $strBase = './templates/backoffice';
        $funcValide = function($strNom) use ($strBase) {
            return is_string($strNom) && preg_match('/^[A-Za-z0-9_][A-Za-z0-9._-]*$/', $strNom) && is_dir("{$strBase}/{$strNom}");
        };

        if (!$funcValide($strName)) return null;

        $strPath = "{$strBase}/{$strName}";
        if (file_exists("{$strPath}/index.tpl")) return array('path' => $strPath, 'palette' => '');

        $arrIni = file_exists("{$strPath}/skin.ini") ? @parse_ini_file("{$strPath}/skin.ini") : false;
        $strParent = is_array($arrIni) && isset($arrIni['extends']) ? $arrIni['extends'] : '';
        if (!$funcValide($strParent) || !file_exists("{$strBase}/{$strParent}/index.tpl")) return null;

        return array(
            'path' => "{$strBase}/{$strParent}",
            'palette' => file_exists("{$strPath}/css/palette.css") ? "{$strPath}/css/palette.css" : ''
        );
    }

    /**
     * Génération du gabarit (clé "version" de skin.ini, 0 si absente).
     * Les écrans qui ont une présentation propre à une génération (ploopi4 : 4)
     * la testent ; les autres gabarits gardent la présentation historique.
     *
     * @return int génération du gabarit
     */

    public function version()
    {
        return isset($this->values['version']) ? (int) $this->values['version'] : 0;
    }

    /**
     * Code HTML d'une icône du jeu du gabarit.
     * Les gabarits sans jeu d'icônes vectorielles ne rendent rien.
     *
     * @param string $strIcon nom de l'icône
     * @param string $strClass classes CSS supplémentaires
     * @return string code html de l'icône
     */

    public function icon($strIcon, $strClass = '')
    {
        return '';
    }

    /**
     * Nom d'icône d'un type de module, lu dans skin.ini (icon_<type>, sinon icon_default)
     *
     * @param string $strModuleType type de module
     * @return string nom de l'icône (vide si le gabarit n'en définit pas)
     */

    public function moduleicon($strModuleType)
    {
        if (isset($this->values["icon_{$strModuleType}"])) return $this->values["icon_{$strModuleType}"];
        return isset($this->values['icon_default']) ? $this->values['icon_default'] : '';
    }

    /**
     * Préférences d'affichage de l'utilisateur : thème (auto, light, dark) et densité
     * (comfortable, compact). Connecté : paramètres system_theme et system_density
     * (défaut, espace, utilisateur). Déconnecté : dernier thème choisi sur ce navigateur
     * (cookie ploopi_theme posé par le gabarit), densité par défaut.
     *
     * @return array [thème, densité]
     */

    public static function getdisplayprefs()
    {
        $strTheme = $strDensity = '';

        if (!empty($_SESSION['ploopi']['connected']))
        {
            $strTheme = param::get('system_theme', _PLOOPI_MODULE_SYSTEM);
            $strDensity = param::get('system_density', _PLOOPI_MODULE_SYSTEM);
        }
        elseif (isset($_COOKIE['ploopi_theme'])) $strTheme = $_COOKIE['ploopi_theme'];

        if (!in_array($strTheme, array('auto', 'light', 'dark'), true)) $strTheme = 'auto';
        if (!in_array($strDensity, array('comfortable', 'compact'), true)) $strDensity = 'comfortable';

        return array($strTheme, $strDensity);
    }

    /**
     * Créé un haut de bloc
     *
     * @param string $title titre du bloc
     * @param string $style styles optionnels du bloc
     * @param string $styletitle styles optionnels du titre
     * @param srting $additionnal_title titre additionnel
     * @return string code html de l'entête du bloc
     */

    public function open_simplebloc($title = '', $style = '', $styletitle = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"simplebloc\" style=\"{$style}\">";
        else $res = "<div class=\"simplebloc\">";

        if ($title!=null) $res .= "<div class=\"simplebloc_title\" id=\"handle\" style=\"{$styletitle}\"><div class=\"simplebloc_titleleft\">{$additionnal_title}{$title}</div></div>";

        $res .= '<div class="simplebloc_content">';

        return $res;
    }

    /**
     * Créé un bas de bloc (ferme le dernier bloc ouvert)
     *
     * @return string code html du pied du bloc
     */

    function close_simplebloc()
    {
        return '<div style="clear:both;"></div></div><div class="simplebloc_footer"></div></div>';
    }

    /**
     * Crée un titre de page
     *
     * @param string $title titre de la page
     * @param string $style styles optionnels
     * @return string code html du titre
     */

    public function create_pagetitle($title, $style = '', $additionnal_title = '')
    {
        if (strlen($style)>0) $res = "<div class=\"pagetitle\" style=\"{$style}\"><p>{$additionnal_title}</p>{$title}</div>";
        else $res = "<div class=\"pagetitle\"><p>{$additionnal_title}</p>{$title}</div>";

        return $res;
    }

    /**
     * Crée une barre d'outils (icones)
     *
     * @param array $icons tableau associatif d'icones (propriétés : title, url, icon, width, confirm, javascript)
     * @param string $iconsel clé de l'icone sélectionnée (par référence), sélectionne par défaut la première icone
     * @param boolean $sel true si la sélection est gérée, par défaut tru
     * @param boolean $vertical true si l'affichage est vertical, par défaut false
     * @return string code html de la barre d'outils
     */

    public function create_toolbar($icons, &$iconsel, $sel = true, $vertical = false)
    {
        if (is_null($iconsel) || !isset($icons[$iconsel])) $iconsel = -1;

        $icons_content_left = '';
        $icons_content_right = '';

        if ($sel)
        {
            foreach($icons AS $key => $value)
            {
                if ($iconsel == -1) $iconsel = $key;
            }
        }

        foreach($icons AS $key => $value)
        {
            if (isset($icons[$key]['position']) && $icons[$key]['position'] == 'right')
            {
                if ($sel)
                {
                    $icons_content_right .= $this->create_icon($icons[$key], ($iconsel == $key), $key, $vertical);
                }
                else
                {
                    $icons_content_right .= $this->create_icon($icons[$key], false, $key, $vertical);
                }
            }
            else
            {
                if ($sel)
                {
                    $icons_content_left .= $this->create_icon($icons[$key], ($iconsel == $key), $key, $vertical);
                }
                else
                {
                    $icons_content_left .= $this->create_icon($icons[$key], false, $key, $vertical);
                }
            }
        }

        $res =  "
                <div class=\"toolbar\">
                    <div class=\"toolbar_left\">{$icons_content_left}</div>
                    <div class=\"toolbar_right\">{$icons_content_right}</div>
                </div>
                ";

        return $res;
    }

    /**
     * Crée une icone pour la barre d'outils
     *
     * @param array $icon icone à afficher (propriétés : title, url, icon, width, confirm)
     * @param boolean $sel true si l'icone est sélectionnée
     * @param string $key clé (propriété id) de l'icone
     * @param boolean $vertical true si l'affichage est vertical
     * @return string code html de l'icone
     */

    public function create_icon($icon, $sel, $key, $vertical)
    {
        $confirm = isset($icon['confirm']);

        $title = $icon['title'];

        $strHrefTitle = str::htmlentities(strip_tags($title));

        // Lus par l'écouteur délégué de js/utilities.js (a.skin_icon)
        $data = 'data-url="'.str::htmlentities(crypt::urlencode($icon['url'])).'"';
        if ($confirm && $icon['confirm'] != '') $data .= ' data-confirm="'.str::htmlentities($icon['confirm']).'"';

        if (isset($icon['icon']))
        {
            $classpng = '';
            //if (strtolower(substr($icon['icon'],-4,4)) == '.png') $classpng = 'class="png"';
            $image = "<img $classpng alt=\"{$strHrefTitle}\" src=\"$icon[icon]\">";
        }
        else $image = '';

        $class = (($vertical) ? 'toolbar_icon_vertical' : 'toolbar_icon').($sel ? '_sel' : '');

        $style = (!empty($icon['width'])) ? "style=\"width:{$icon['width']}px;\"" : '';

        $res = "
            <div class=\"{$class}\" id=\"{$key}\" {$style}>
                <a class=\"skin_icon\" href=\"#\" {$data} title=\"Accéder à &laquo; {$strHrefTitle} &raquo;\">
                    <div class=\"toolbar_icon_image\">$image</div>
                    <p>$title</p>
                </a>
            </div>
        ";

        return $res;
    }

    /**
     * Crée une barre d'onglets
     *
     * @param array $tabs tableau associatif d'onglets (propriétés : title, url, width)
     * @param string $tabsel clé de l'onglet sélectionné (par référence), sélectionne par défaut le premier onglet
     * @return string code html de la barre d'onglets
     */

    public function create_tabs($tabs, &$tabsel)
    {

        $res = "<div class=\"tabs\">";

        if (!isset($tabs[$tabsel])) $tabsel = -1;

        foreach($tabs AS $key => $value)
        {
            if ($tabsel == -1) $tabsel = $key;
            $res .= $this->create_tab($tabs[$key], ($tabsel==$key));
        }

        $res .= "</div>";

        return $res;
    }

    /**
     * Crée un onglet
     *
     * @param array $tab onglet (propriétés : title, url, width)
     * @param boolean $sel true si l'onglet est sélectionné
     * @return string code html de l'onglet
     */

    public function create_tab($tab, $sel)
    {
        if (!empty($tab['width'])) $style = "style=\"width:{$tab['width']}px;\"";
        else $style = '';

        $strHrefTitle = str::htmlentities(strip_tags($tab['title']));

        if ($sel) $res = "<a href=\"".crypt::urlencode($tab['url'])."\" title=\"Accéder à l'onglet &laquo; {$strHrefTitle} &raquo;\"  class=\"selected\" {$style}>{$tab['title']}</a>";
        else  $res = "<a href=\"".crypt::urlencode($tab['url'])."\" title=\"Accéder à l'onglet &laquo; {$strHrefTitle} &raquo;\"  {$style}>{$tab['title']}</a>";
        return $res;
    }

    /**
     * Crée un faux popup (div)
     *
     * @param string $title titre du popup
     * @param string $content contenu du popup (html)
     * @param string $popupid id du popup (propriété html id)
     * @return string code html du popup
     */

    public function create_popup($title, $content, $popupid = 'ploopi_popup')
    {
        $strOptionAnchor = ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_focus_popup']) ? "document.location.href = '#anchor_{$popupid}';" : '';

        $res =  '
                <div class="simplebloc" style="margin:0;">
                    <a name="anchor_'.$popupid.'"></a>
                    <div class="simplebloc_title">
                        <div class="simplebloc_titleleft">
                            <img alt="Fermer" title="Fermer le popup" id="close_'.$popupid.'" class="ploopi_popup_close" data-popup="'.$popupid.'" style="display:block;float:right;cursor:pointer;" src="'.$this->values['path'].'/template/close_popup.png">
                            <div style="overflow:auto;cursor:move;" class="handle_'.$popupid.'">'.$title.'</div>
                        </div>
                    </div>
                    <div class="simplebloc_content">'.$content.'</div>
                    <div class="simplebloc_footer handle_'.$popupid.'" style="cursor:move;"></div>
                </div>
                <script type="text/javascript">
                jQuery(\'#'.$popupid.'\').draggable({ zindex: 99999, handle: \'.handle_'.$popupid.'\'});
                '.$strOptionAnchor.'
                </script>
                ';

        return($res);
    }


    /**
     * Crée un faux popup et l'ouvre via javascript
     *
     * @param string $title titre du popup
     * @param string $content contenu du popup (html)
     * @param string $popupid id du popup (propriété html id)
     * @return string code html du popup
     */

    public function open_popup($title, $content, $popupid = 'ploopi_popup', $arrOptions = array())
    {
        $arrDefaultOptions = array(
            'intWidth' => 200,
            'booCentered' => true,
            'booClosable' => true,
            'intPosx' => 0,
            'intPosy' => 0,
            'stringJsBeforeStart' => '',
            'stringJsAfterFinish' => '',
        );

        $arrOptions = array_merge($arrDefaultOptions, $arrOptions);

        $strOptionAnchor = ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_focus_popup']) ? "document.location.href = '#anchor_{$popupid}';" : '';

        $strClosable = $arrOptions['booClosable'] ? '<img alt="Fermer" title="Fermer le popup" id="close_'.$popupid.'" class="ploopi_popup_close" data-popup="'.$popupid.'" style="display:block;float:right;margin:2px;cursor:pointer;" src="'.$this->values['path'].'/template/close_popup.png">' : '';

        $res =  '
                <div id="'.$popupid.'" style="display:none;">
                    <div class="simplebloc" style="margin:0;">
                        <a name="anchor_'.$popupid.'"></a>
                        <div class="simplebloc_title">
                            <div class="simplebloc_titleleft">
                                '.$strClosable.'
                                <div style="overflow:auto;cursor:move;" class="handle_'.$popupid.'">'.$title.'</div>
                            </div>
                        </div>
                        <div class="simplebloc_content">'.$content.'</div>
                        <div class="simplebloc_footer" style="cursor:move;" class="handle_'.$popupid.'"></div>
                    </div>
                </div>
                <script type="text/javascript">
                    jQuery(function() {
                        '.$arrOptions['stringJsBeforeStart'].'
                        ploopi.popup.ize(\''.$popupid.'\', '.$arrOptions['intWidth'].', '.($arrOptions['booCentered'] ? 'true' : 'false').', '.$arrOptions['intPosx'].', '.$arrOptions['intPosy'].');
                        jQuery(\'#'.$popupid.'\').draggable({ zindex: 99999, handle: \'.handle_'.$popupid.'\'});
                        '.$arrOptions['stringJsAfterFinish'].'
                        '.$strOptionAnchor.'
                    });

                </script>
                ';

        return($res);
    }

    /**
     * Affiche un tableau avancé
     *
     * @param array $arrColumns définition des colonnes
     * @param array $arrValues contenu du tableau
     * @param string $strArrayId identifiant du tableau
     * @param array $arrOptions options d'affichage
     *
     * propriétés des colonnes : label, width, styles, options
     * propriétés des valeurs : label, style, sort_label
     * propriétés des options : height, sortable, orderby_default, sort_default
     *
     * <code>
     * <?php
     * $array_columns = array();
     * $array_values = array();
     *
     * $array_columns['left']['colonne1'] = array('label' => 'Colonne 1', 'width' => '100', 'options' => array('sort' => true));
     * $array_columns['right']['colonne2'] = array('label' => 'Colonne 2', 'width' => '100','options' => array('sort' => true));
     * $array_columns['auto']['colonneauto'] = array('label' => 'Colonne Auto', 'options' => array('sort' => true));
     *
     * $c = 0;
     *
     * $array_values[$c]['values']['colonne1'] = array('label' => 'valeur1');
     * $array_values[$c]['values']['colonne2'] = array('label' => 'valeur2', 'style' => 'text-align:right');
     * $array_values[$c]['values']['colonneauto'] = array('label' => 'valeur3', 'sort_label' => '3');
     *
     * $skin->display_array($array_columns, $array_values, 'id_tableau', array('height' => 200, 'sortable' => true, 'orderby_default' => 'valeur3', 'sort_default' => 'DESC', 'callback' => 'jsfunc'));
     * ?>
     * </code>
     */

    public function display_array(&$arrColumns, &$arrValues, $strArrayId = null, $arrOptions = null)
    {
        // Un tableau nommé par le module retrouve son tri et sa page (ploopi4 : dans le navigateur)
        $booPersist = !empty($strArrayId);
        if (empty($strArrayId)) $strArrayId = md5(uniqid(rand(), true));

        $sort = $orderby = '';

        /*
        if (!empty($_SESSION['ploopi']['arrays'][$strArrayId]))
        {
            // preserve orderby & sort values
            $orderby = $_SESSION['ploopi']['arrays'][$strArrayId]['orderby'];
            $sort = $_SESSION['ploopi']['arrays'][$strArrayId]['sort'];
        }
        */

        if (empty($arrOptions['page'])) $arrOptions['page'] = 1;
        if (empty($arrOptions['limit'])) $arrOptions['limit'] = 0;
        if (empty($arrOptions['callback'])) $arrOptions['callback'] = '';

        $array = array('columns' => &$arrColumns, 'values' => &$arrValues, 'options' => &$arrOptions, 'orderby' => &$orderby, 'sort' => &$sort);

        if (!empty($array['options']['sortable']) && $array['options']['sortable'])
        {
            $array['sortable_columns'] = array();
            if (!empty($array['columns']['left']))
            {
                foreach($array['columns']['left'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['left'][$id]['sortdata'] = array('array' => $strArrayId, 'orderby' => $id, 'callback' => $array['options']['callback']);
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

            if (!empty($array['columns']['auto']))
            {
                foreach($array['columns']['auto'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['auto'][$id]['sortdata'] = array('array' => $strArrayId, 'orderby' => $id, 'callback' => $array['options']['callback']);
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

            if (!empty($array['columns']['right']))
            {
                foreach($array['columns']['right'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['right'][$id]['sortdata'] = array('array' => $strArrayId, 'orderby' => $id, 'callback' => $array['options']['callback']);
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

            if (!empty($array['columns']['actions_left']))
            {
                foreach($array['columns']['actions_left'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['actions_left'][$id]['sortdata'] = array('array' => $strArrayId, 'orderby' => $id, 'callback' => $array['options']['callback']);
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

            if (!empty($array['columns']['actions_right']))
            {
                foreach($array['columns']['actions_right'] as $id => $c)
                {
                    if (!empty($c['options']['sort']))
                    {
                        $array['columns']['actions_right'][$id]['sortdata'] = array('array' => $strArrayId, 'orderby' => $id, 'callback' => $array['options']['callback']);
                        $array['sortable_columns'][] = $id;
                    }
                }
            }

        }

        // Gabarits de génération 4 : tableau trié et paginé dans le navigateur (js/skin.js),
        // sans aller-retour serveur ni variable sérialisée
        if ($this->version() >= 4)
        {
            echo $this->_array_render_fluid($strArrayId, $array, $booPersist);
            return;
        }

        $objSV = new serializedvar($strArrayId);
        $objSV->save($array);
        ?>
        <div class="ploopi_explorer_main<?php if (isset($arrOptions['class'])) echo ' '.$arrOptions['class']; ?>" id="ploopi_explorer_main_<?php echo $strArrayId; ?>" style="visibility:visible;">
        <?php $this->display_array_refresh($strArrayId); ?>
        </div>
        <?php

    }


    private function array_page($strArrayId, $intPage, $strPage, $intSel = 0, $callback = '')
    {
        return $intSel == $intPage ? str_replace('{p}', $strPage, '<strong>{p}</strong>') : str_replace(array('{p}', '{id}'), array($strPage, $intPage), '<a href="javascript:void(0);" class="ploopi_array_page" data-array="'.$strArrayId.'" data-page="{id}" data-callback="'.$callback.'">{p}</a>');
    }

    /**
     * Clé de tri d'une ligne pour une colonne : sort_label s'il est défini, sinon label,
     * en majuscules sans accents (vide si la ligne n'a pas la colonne)
     *
     * @param array $arrRow ligne du tableau
     * @param string $strCol clé de la colonne
     * @param boolean $booText true pour trier sur le texte affiché (tableau fluide)
     * @return string clé de tri
     */

    private static function _array_sortkey($arrRow, $strCol, $booText = false)
    {
        if (!isset($arrRow['values'][$strCol])) return '';
        $arrCell = $arrRow['values'][$strCol];
        $strKey = (string) (isset($arrCell['sort_label']) ? $arrCell['sort_label'] : (isset($arrCell['label']) ? $arrCell['label'] : ''));

        // Tableau fluide : texte affiché (sans balises, entités décodées), pour que
        // « &Eacute;coles » ou un lien <a href…> se trient sur leur libellé
        if ($booText) $strKey = self::_array_text($strKey);

        return strtoupper(str::convertaccents($strKey));
    }

    /**
     * Texte d'un libellé HTML : sans balises, entités décodées
     *
     * @param string $strHtml libellé
     * @return string texte brut
     */

    private static function _array_text($strHtml)
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $strHtml), ENT_QUOTES, 'UTF-8')));
    }

    /**
     * Rendu fluide d'un tableau avancé (gabarits de génération 4) : un <table> unique,
     * colonnes dans l'ordre visuel de l'affichage historique, tri et pagination par
     * js/skin.js, affichage en cartes quand le conteneur est étroit (CSS du gabarit).
     *
     * Le tri initial est fait ici (le JavaScript le rejoue, puis restaure le tri et la
     * page mémorisés pour la session de l'onglet) ; les libellés et les liens de ligne
     * sont rendus comme dans l'affichage historique (HTML fourni par le module).
     *
     * @param string $strArrayId identifiant du tableau
     * @param array $array tableau préparé par display_array()
     * @param boolean $booPersist true si le tri et la page sont à mémoriser
     * @return string code html
     */

    private function _array_render_fluid($strArrayId, $array, $booPersist)
    {
        $e = function($str) { return str::htmlentities((string) $str); };

        // Ordre visuel : les colonnes « à droite » flottaient à droite, la première
        // déclarée la plus à droite
        $arrCols = $arrGroups = array();
        foreach(array('actions_left' => false, 'left' => false, 'auto' => false, 'right' => true, 'actions_right' => true) as $strGroup => $booReverse)
        {
            if (empty($array['columns'][$strGroup]) || !is_array($array['columns'][$strGroup])) continue;
            foreach(($booReverse ? array_reverse($array['columns'][$strGroup], true) : $array['columns'][$strGroup]) as $strKey => $arrCol)
            {
                $arrCols[$strKey] = $arrCol;
                $arrGroups[$strKey] = $strGroup;
            }
        }

        // Colonne « titre » : porte le lien de ligne et ouvre la carte sur petit écran
        $strTitle = '';
        foreach(array('auto', 'left', 'right') as $strGroup)
        {
            $strTitle = (string) array_search($strGroup, $arrGroups, true);
            if ($strTitle !== '') break;
        }

        // Tri : comme l'affichage historique (tri par défaut, sinon première colonne triable)
        $booSortable = !empty($array['options']['sortable']);
        $arrSortable = $booSortable && !empty($array['sortable_columns']) ? $array['sortable_columns'] : array();
        $strOrderBy = '';
        if ($booSortable)
        {
            if (!empty($array['options']['orderby_default']) && isset($arrCols[$array['options']['orderby_default']])) $strOrderBy = (string) $array['options']['orderby_default'];
            elseif (!empty($arrSortable)) $strOrderBy = (string) $arrSortable[0];
        }
        $strSort = !empty($array['options']['sort_default']) && strtoupper($array['options']['sort_default']) == 'DESC' ? 'DESC' : 'ASC';

        $funcNumeric = function($strKey) use ($arrCols) { return isset($arrCols[$strKey]['options']['sort_flag']) && $arrCols[$strKey]['options']['sort_flag'] == SORT_NUMERIC; };

        // Colonnes dont les cellules portent une clé de tri
        $arrKeyed = array_flip($arrSortable);
        if ($strOrderBy !== '') $arrKeyed[$strOrderBy] = true;

        $arrValues = is_array($array['values']) ? $array['values'] : array();
        $arrRank = array_flip(array_keys($arrValues));
        $arrOrder = array_keys($arrValues);

        if ($strOrderBy !== '')
        {
            $arrSortKeys = array();
            foreach($arrValues as $mixKey => $arrRow) $arrSortKeys[$mixKey] = self::_array_sortkey($arrRow, $strOrderBy, true);
            $booNumeric = $funcNumeric($strOrderBy);
            $intDir = $strSort == 'DESC' ? -1 : 1;
            usort($arrOrder, function($a, $b) use ($arrSortKeys, $arrRank, $booNumeric, $intDir) {
                $c = $booNumeric ? floatval($arrSortKeys[$a]) <=> floatval($arrSortKeys[$b]) : strnatcmp($arrSortKeys[$a], $arrSortKeys[$b]);
                if ($c == 0) $c = $arrRank[$a] <=> $arrRank[$b];
                return $c * $intDir;
            });
        }

        // Page initiale
        $intTotal = sizeof($arrValues);
        $intLimit = empty($array['options']['limit']) ? 0 : max(0, (int) $array['options']['limit']);
        if ($intLimit >= $intTotal) $intLimit = 0;
        $intPages = $intLimit ? (int) ceil($intTotal / $intLimit) : 1;
        $intPage = empty($array['options']['page']) ? 1 : min(max(1, (int) $array['options']['page']), $intPages);
        $intFrom = $intLimit ? ($intPage - 1) * $intLimit : 0;
        $intTo = $intLimit ? min($intFrom + $intLimit, $intTotal) : $intTotal;

        $strClass = 'ploopi_explorer_main p4-arr-main'.(isset($array['options']['class']) ? ' '.$array['options']['class'] : '');

        $res = '<div class="'.$e($strClass).'" id="ploopi_explorer_main_'.$e($strArrayId).'" data-p4-arr="'.$e($strArrayId).'"'
            .' data-p4-arr-orderby="'.$e($strOrderBy).'" data-p4-arr-sort="'.$strSort.'" data-p4-arr-limit="'.$intLimit.'" data-p4-arr-page="'.$intPage.'"'
            .(empty($array['options']['callback']) ? '' : ' data-p4-arr-callback="'.$e($array['options']['callback']).'"')
            .($booPersist ? ' data-p4-arr-persist="1"' : '')
            .' data-p4-arr-of="'.$e(_PLOOPI_ARRAY_OF).'">';

        if (empty($arrValues))
        {
            return $res.'<div class="p4-ui p4-empty">'.$this->icon('list-details').'<p>'.$e(_PLOOPI_ARRAY_EMPTY).'</p></div></div>';
        }

        // Barre de tri de l'affichage en cartes (les en-têtes y sont masqués)
        if (!empty($arrSortable))
        {
            $res .= '<div class="p4-ui p4-arr-sortbar"><label><span>'.$e(_PLOOPI_ARRAY_SORTBY).'</span><select class="p4-select" data-p4-arr-select>';
            foreach($arrSortable as $strKey)
            {
                $strKey = (string) $strKey;
                $strLabel = self::_array_text(isset($arrCols[$strKey]['label']) ? $arrCols[$strKey]['label'] : '');
                $res .= '<option value="'.$e($strKey).'"'.($strKey == $strOrderBy ? ' selected' : '').'>'.$e($strLabel == '' ? $strKey : $strLabel).'</option>';
            }
            $res .= '</select></label>';
            $res .= '<button type="button" class="p4-btn p4-btn-sm p4-btn-icon" data-p4-arr-dir aria-label="'.$e(_PLOOPI_ARRAY_REVERSE).'" title="'.$e(_PLOOPI_ARRAY_REVERSE).'">'.$this->icon($strSort == 'DESC' ? 'chevron-down' : 'chevron-up').'</button></div>';
        }

        $res .= '<div class="p4-table-wrap'.(empty($array['options']['height']) ? '' : ' p4-arr-scroll').'"'.(empty($array['options']['height']) ? '' : ' style="max-height:'.(int) $array['options']['height'].'px"').'>';
        $res .= '<table class="p4-table p4-arr"><thead class="p4-ui"><tr>';

        foreach($arrCols as $strKey => $arrCol)
        {
            $strKey = (string) $strKey;
            $booActions = strpos($arrGroups[$strKey], 'actions') === 0;
            $strLabel = isset($arrCol['label']) ? (string) $arrCol['label'] : '';

            $arrClass = array();
            if ($booActions) $arrClass[] = 'p4-arr-acts';
            if (!empty($arrCol['class'])) $arrClass[] = $arrCol['class'];

            $strStyle = '';
            if (!$booActions && !empty($arrCol['width']) && is_numeric($arrCol['width'])) $strStyle = 'width:'.(int) $arrCol['width'].'px;';
            if (!empty($arrCol['style'])) $strStyle .= $arrCol['style'];

            $res .= '<th'.(empty($arrClass) ? '' : ' class="'.$e(implode(' ', $arrClass)).'"').($strStyle == '' ? '' : ' style="'.$e($strStyle).'"');
            if ($strKey === $strOrderBy) $res .= ' aria-sort="'.($strSort == 'DESC' ? 'descending' : 'ascending').'"';
            $res .= ' scope="col">';

            if (!empty($arrCol['sortdata']))
            {
                $strIcon = $strKey === $strOrderBy ? ($strSort == 'DESC' ? 'chevron-down' : 'chevron-up') : 'arrows-sort';
                $res .= '<button type="button" class="p4-sort" data-p4-arr-sort="'.$e($strKey).'"'.($funcNumeric($strKey) ? ' data-p4-arr-num' : '').'>'
                    .($strLabel == '' ? '<span class="p4-sr-only">'.$e($strKey).'</span>' : $strLabel).$this->icon($strIcon).'</button>';
            }
            elseif (!empty($arrCol['url']) || !empty($arrCol['onclick']))
            {
                $res .= '<a href="'.(empty($arrCol['url']) ? 'javascript:void(0);' : $arrCol['url']).'"'.(empty($arrCol['onclick']) ? '' : ' onclick="javascript:'.$arrCol['onclick'].'"').'>'.$strLabel.'</a>';
            }
            elseif ($booActions && self::_array_text($strLabel) == '') $res .= '<span class="p4-sr-only">'.$e(_PLOOPI_ARRAY_ACTIONS).'</span>';
            else $res .= $strLabel;

            $res .= '</th>';
        }

        $res .= '</tr></thead><tbody>';

        foreach($arrOrder as $intPos => $mixKey)
        {
            $v = $arrValues[$mixKey];
            $arrRowValues = isset($v['values']) && is_array($v['values']) ? $v['values'] : array();
            $booLink = !empty($v['link']) || !empty($v['onclick']);

            $res .= '<tr'.(empty($v['id']) ? '' : ' id="'.$e($v['id']).'"')
                .' class="'.$e(trim(($booLink ? 'p4-arr-link ' : '').(empty($v['class']) ? '' : $v['class']))).'"'
                .(empty($v['style']) ? '' : ' style="'.$e($v['style']).'"')
                .' data-p4-arr-i="'.$arrRank[$mixKey].'"'
                .($intPos < $intFrom || $intPos >= $intTo ? ' hidden' : '').'>';

            foreach($arrCols as $strKey => $arrCol)
            {
                $strKey = (string) $strKey;
                $booActions = strpos($arrGroups[$strKey], 'actions') === 0;
                $arrCell = isset($arrRowValues[$strKey]) && is_array($arrRowValues[$strKey]) ? $arrRowValues[$strKey] : array();
                $strLabel = isset($arrCell['label']) ? (string) $arrCell['label'] : '';
                if (trim($strLabel) == '&nbsp;') $strLabel = '';

                $arrClass = array();
                if ($booActions) $arrClass[] = 'p4-arr-acts';
                if ($strKey === $strTitle) $arrClass[] = 'p4-arr-title';
                if (!empty($arrCell['class'])) $arrClass[] = $arrCell['class'];

                // Lien de la ligne : ancre de la cellule titre, étendue à toute la ligne
                // (CSS) ; vide à côté du contenu s'il est lui-même interactif
                if ($booLink && $strKey === $strTitle)
                {
                    $strDescription = empty($v['description']) ? '' : self::_array_text($v['description']);
                    $strAnchor = '<a class="p4-arr-rowlink" href="'.(empty($v['link']) ? 'javascript:void(0);' : $v['link']).'"'
                        .($strDescription == '' ? '' : ' title="'.$e($strDescription).'"')
                        .(empty($v['onclick']) ? '' : ' onclick="'.$v['onclick'].'"')
                        .(empty($v['option']) ? '' : ' '.$v['option']);

                    if (preg_match('/<(a|button|input|select|textarea|label)\b/i', $strLabel))
                    {
                        $strAria = $strDescription == '' ? self::_array_text($strLabel) : $strDescription;
                        $strLabel = $strAnchor.' aria-label="'.$e($strAria).'"></a>'.$strLabel;
                    }
                    else $strLabel = $strAnchor.'>'.$strLabel.'</a>';
                }

                $res .= '<td'.(empty($arrClass) ? '' : ' class="'.$e(implode(' ', $arrClass)).'"')
                    .(empty($arrCell['style']) ? '' : ' style="'.$e($arrCell['style']).'"');
                if (!$booActions)
                {
                    $strColLabel = self::_array_text(isset($arrCol['label']) ? $arrCol['label'] : '');
                    if ($strColLabel != '') $res .= ' data-label="'.$e($strColLabel).'"';
                }
                if (isset($arrKeyed[$strKey])) $res .= ' data-p4-arr-key="'.$e(self::_array_sortkey($v, $strKey, true)).'"';
                $res .= '>'.$strLabel.'</td>';
            }

            $res .= '</tr>';
        }

        $res .= '</tbody></table></div>';

        // Pied : lignes affichées et pagination (boutons construits par js/skin.js)
        if ($intLimit)
        {
            $res .= '<div class="p4-ui p4-arr-foot"><span class="p4-arr-count" data-p4-arr-count>'.($intFrom + 1).'–'.$intTo.' '.$e(_PLOOPI_ARRAY_OF).' '.$intTotal.'</span>'
                .'<nav class="p4-pager" data-p4-arr-pager aria-label="'.$e(_PLOOPI_ARRAY_PAGINATION).'"'
                .' data-p4-arr-prev="'.$e(_PLOOPI_ARRAY_PREVIOUS).'" data-p4-arr-next="'.$e(_PLOOPI_ARRAY_NEXT).'"></nav></div>';
        }

        return $res.'</div>';
    }

    /**
     * Rafraichit l'affichage d'un tableau avancé
     *
     * @param string $strArrayId id du tableau
     * @param string $orderby colonne de tri
     */

    public function display_array_refresh($strArrayId, $strOrderBy = null, $intIdPage = null)
    {
        // On récupère le tableau stocké en session (identifié par array_id)
        $objSV = new serializedvar($strArrayId);
        $array = $objSV->read();

        // Le tableau n'existe pas, pas normal, on sort
        if ($array == false) return;

        $sort_img = '';
        $sortable_img = "<img src=\"{$this->values['path']}/arrays/arrows.png\" style=\"opacity:0.5\" />";

        // index temporaire (pour tri à l'affichage)
        $array['index'] = array();

        // si le tableau est "triable" (option)
        if (!empty($array['options']['sortable']) && $array['options']['sortable'])
        {
            // On crée une liste applatie des colonnes pour vérifier l'existence de la colonne de tri
            $columns = array();
            if (!empty($array['columns']['auto'])) $columns += $array['columns']['auto'];
            if (!empty($array['columns']['right'])) $columns += $array['columns']['right'];
            if (!empty($array['columns']['left'])) $columns += $array['columns']['left'];
            if (!empty($array['columns']['actions_right'])) $columns += $array['columns']['actions_right'];
            if (!empty($array['columns']['actions_left'])) $columns += $array['columns']['actions_left'];

            // initialisation  du tri par défaut pour le tableau courant
            if (empty($array['orderby']))
            {
                if (!empty($_SESSION['ploopi']['arrays'][$strArrayId]))
                {

                    if (isset($columns[$_SESSION['ploopi']['arrays'][$strArrayId]['orderby']]))
                    {
                        // Lecture du tri en session
                        $array['orderby'] = $_SESSION['ploopi']['arrays'][$strArrayId]['orderby'];
                        $array['sort'] = $_SESSION['ploopi']['arrays'][$strArrayId]['sort'];
                    }
                }

                if (empty($array['orderby'])) {
                    if (!empty($array['options']['orderby_default'])) $array['orderby'] = $array['options']['orderby_default'];
                    elseif (!empty($array['sortable_columns'][0])) $array['orderby'] = $array['sortable_columns'][0];
                }
            }

            $array['sort_flag'] = isset($columns[$array['orderby']]['options']['sort_flag']) ? $columns[$array['orderby']]['options']['sort_flag'] : SORT_STRING;

            if (empty($array['sort']))
            {
                if (!empty($array['options']['sort_default'])) $array['sort'] = $array['options']['sort_default'];
                else $array['sort'] = 'ASC';
            }
            // on réinitialise l'ordre de tri si l'ordreby est différent du précédent
            if (!empty($strOrderBy))
            {
                if ($strOrderBy != $array['orderby']) $array['sort'] = 'ASC';
                else $array['sort'] = ($array['sort'] == 'ASC') ? 'DESC' : 'ASC';
            }

            // récupération de la valeur de l'orderby en session ou en parametre (par défaut en paramètre)
            $array['orderby'] = (empty($strOrderBy)) ? $array['orderby'] : $strOrderBy;

            $c = 0;
            foreach($array['values'] as $key => $value)
            {
                $idx = sprintf("%s_%06s", self::_array_sortkey($value, $array['orderby']), $c++);
                $array['index'][$idx] = $key;
            }

            $flag = isset($array['sort_flag']) ? $array['sort_flag'] : SORT_STRING;

            if ($array['sort'] == 'ASC') ksort($array['index'], $flag);
            else krsort($array['index'], $flag);

            $sort_img = ($array['sort'] == 'DESC') ? "<img src=\"{$this->values['path']}/arrays/arrow_down.png\" />" : "<img src=\"{$this->values['path']}/arrays/arrow_up.png\" />";

        }
        else $array['index'] = array_keys($array['values']);

        // Lecture page en session
        if (isset($_SESSION['ploopi']['arrays'][$strArrayId]['page'])) $array['options']['page'] = $_SESSION['ploopi']['arrays'][$strArrayId]['page'];
        if (!empty($intIdPage) && is_numeric($intIdPage)) $array['options']['page'] = $intIdPage;

        // Sauvegarde du tri + page en session
        $_SESSION['ploopi']['arrays'][$strArrayId] = array(
            'orderby' => $array['orderby'],
            'sort' => $array['sort'],
            'page' => $array['options']['page'],
        );

        $objSV->save($array);

        // Affichage des pages (optionnel)
        if ($array['options']['limit'] > 0 && $array['options']['limit'] < sizeof($array['values']))
        {
            $intNbPages = ceil(sizeof($array['values']) / $array['options']['limit']);

            $arrPages = array();

            // Fleche page précédente
            if ($array['options']['page'] > 1) $arrPages[] = $this->array_page($strArrayId, $array['options']['page']-1, '&laquo;', 0, $array['options']['callback']);

            // On affiche toujours la premiere page
            $arrPages[] = $this->array_page($strArrayId, 1, 1, $array['options']['page'], $array['options']['callback']);

            // Affichage "..." après première page
            if ($array['options']['page'] > 4) $arrPages[] = '...';

            // Boucle sur les pages autour de la page sélectionnée (-2 à +2 si existe)
            for ($i = $array['options']['page'] - 2; $i <= $array['options']['page'] + 2; $i++)
            {
                if ($i>1 && $i<$intNbPages) $arrPages[] = $this->array_page($strArrayId, $i, $i, $array['options']['page'], $array['options']['callback']);
            }

            // Affichage "..." avant dernière page
            if ($array['options']['page'] < $intNbPages - 3) $arrPages[] = '...';

            // Dernière page
            if ($intNbPages>1) $arrPages[] = $this->array_page($strArrayId, $intNbPages, $intNbPages, $array['options']['page'], $array['options']['callback']);

            // Fleche page suivante
            if ($array['options']['page'] < $intNbPages) $arrPages[] = $this->array_page($strArrayId, $array['options']['page']+1, '&raquo;', 0, $array['options']['callback']);

            ?>
            <div class="ploopi_explorer_multipage" style="border-bottom:1px solid #c0c0c0;padding:2px 4px;text-align:right;">
                Pages : <?php echo implode(' ', $arrPages); ?>
            </div>
            <?php
        }


        $i = 0;
        $w = 0;

        // on insère d'abord la colonne optionnelle de droite (actions)
        if (!empty($array['columns']['actions_right']))
        {
            foreach($array['columns']['actions_right'] as $id => $c)
            {
                $w += $c['width'];
                ?>
                <div style="right:<?php echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<?php echo $strArrayId; ?>_<?php echo $i; ?>"></div>
                <?php
                $i++;
            }
        }

        // on insère ensuite les colonnes de données de droite (optionnelles)
        if (!empty($array['columns']['right']))
        {
            foreach($array['columns']['right'] as $c)
            {
                $w += $c['width'];
                ?>
                <div style="right:<?php echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<?php echo $strArrayId; ?>_<?php echo $i; ?>"></div>
                <?php
                $i++;
            }
        }

        $w = 0;

        // plus la colonne optionnelle de gauche (actions)
        if (!empty($array['columns']['actions_left']))
        {
            foreach($array['columns']['actions_left'] as $id => $c)
            {
                $w += $c['width'];
                ?>
                <div style="left:<?php echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<?php echo $strArrayId; ?>_<?php echo $i; ?>"></div>
                <?php
                $i++;
            }
        }


        // puis les colonnes de données de gauche (optionnelles)
        if (!empty($array['columns']['left']))
        {
            foreach($array['columns']['left'] as $c)
            {
                $w += $c['width'];
                ?>
                <div style="left:<?php echo $w; ?>px;" class="ploopi_explorer_column" id="ploopi_explorer_column_<?php echo $strArrayId; ?>_<?php echo $i; ?>"></div>
                <?php
                $i++;
            }
        }

        // on gère ensuite l'affichage des titres de colonne
        ?>
        <div style="position:relative;">
            <div class="ploopi_explorer_title" id="ploopi_explorer_title_<?php echo $strArrayId; ?>">
                <?php
                // titres des colonnes d'action (à droite)
                if (!empty($array['columns']['actions_right']))
                {
                    foreach($array['columns']['actions_right'] as $id => $c)
                    {
                        $img = '';
                        $class = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $class = 'ploopi_explorer_element_selected';
                        }
                        elseif (!empty($c['options']['sort'])) {
                            $img = $sortable_img;
                        }

                        if (isset($c['class'])) $class = trim($class.' '.$c['class']);

                        if (!empty($c['url']) || !empty($c['onclick']) || !empty($c['sortdata'])) {
                            ?>
                            <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; if (!empty($c['sortdata'])) echo ' data-array="'.str::htmlentities($c['sortdata']['array']).'" data-orderby="'.str::htmlentities($c['sortdata']['orderby']).'" data-callback="'.str::htmlentities($c['sortdata']['callback']).'"'; ?>" style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element<?php if (!empty($c['sortdata'])) echo ' ploopi_array_sort'; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></a>
                            <?php
                        }
                        else {
                            ?>
                            <span style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></span>
                            <?php
                        }
                    }
                }

                // titres des colonnes de données à droite
                if (!empty($array['columns']['right']))
                {
                    foreach($array['columns']['right'] as $id => $c)
                    {
                        $img = '';
                        $class = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $class = 'ploopi_explorer_element_selected';
                        }
                        elseif (!empty($c['options']['sort'])) {
                            $img = $sortable_img;
                        }

                        if (isset($c['class'])) $class = trim($class.' '.$c['class']);

                        if (!empty($c['url']) || !empty($c['onclick']) || !empty($c['sortdata'])) {
                            ?>
                            <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; if (!empty($c['sortdata'])) echo ' data-array="'.str::htmlentities($c['sortdata']['array']).'" data-orderby="'.str::htmlentities($c['sortdata']['orderby']).'" data-callback="'.str::htmlentities($c['sortdata']['callback']).'"'; ?>" style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element<?php if (!empty($c['sortdata'])) echo ' ploopi_array_sort'; ?> ploopi_explorer_element_right <?php echo $class; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></a>
                            <?php
                        }
                        else {
                            ?>
                            <span style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element ploopi_explorer_element_right <?php echo $class; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></span>
                            <?php
                        }
                    }
                }

                // titres des colonnes d'action (à gauche)
                if (!empty($array['columns']['actions_left']))
                {
                    foreach($array['columns']['actions_left'] as $id => $c)
                    {
                        $img = '';
                        $class = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $class = 'ploopi_explorer_element_selected';
                        }
                        elseif (!empty($c['options']['sort'])) {
                            $img = $sortable_img;
                        }

                        if (isset($c['class'])) $class = trim($class.' '.$c['class']);

                        if (!empty($c['url']) || !empty($c['onclick']) || !empty($c['sortdata'])) {
                            ?>
                            <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; if (!empty($c['sortdata'])) echo ' data-array="'.str::htmlentities($c['sortdata']['array']).'" data-orderby="'.str::htmlentities($c['sortdata']['orderby']).'" data-callback="'.str::htmlentities($c['sortdata']['callback']).'"'; ?>" style="width:<?php echo $c['width']; ?>px;float:left;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element<?php if (!empty($c['sortdata'])) echo ' ploopi_array_sort'; ?> <?php echo $class; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></a>
                            <?php
                        }
                        else {
                            ?>
                            <span style="width:<?php echo $c['width']; ?>px;float:left;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element <?php echo $class; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></span>
                            <?php
                        }
                    }
                }

                // titres des colonnes de données à gauche
                if (!empty($array['columns']['left']))
                {
                    foreach($array['columns']['left'] as $id => $c)
                    {
                        $img = '';
                        $class = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $class = 'ploopi_explorer_element_selected';
                        }
                        elseif (!empty($c['options']['sort'])) {
                            $img = $sortable_img;
                        }

                        if (isset($c['class'])) $class = trim($class.' '.$c['class']);

                        if (!empty($c['url']) || !empty($c['onclick']) || !empty($c['sortdata'])) {
                            ?>
                            <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; if (!empty($c['sortdata'])) echo ' data-array="'.str::htmlentities($c['sortdata']['array']).'" data-orderby="'.str::htmlentities($c['sortdata']['orderby']).'" data-callback="'.str::htmlentities($c['sortdata']['callback']).'"'; ?>" style="width:<?php echo $c['width']; ?>px;float:left;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element<?php if (!empty($c['sortdata'])) echo ' ploopi_array_sort'; ?> <?php echo $class; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></a>
                            <?php
                        }
                        else {
                            ?>
                            <span style="width:<?php echo $c['width']; ?>px;float:left;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element <?php echo $class; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></span>
                            <?php
                        }
                    }
                }

                // titre de la colonne centrale (auto)
                if (!empty($array['columns']['auto']))
                {
                    foreach($array['columns']['auto'] as $id => $c)
                    {
                        $img = '';
                        $class = '';
                        if ($array['orderby'] == $id)
                        {
                            $img = $sort_img;
                            if (empty($c['style'])) $c['style'] = '';
                            $class = 'ploopi_explorer_element_selected';
                        }
                        elseif (!empty($c['options']['sort'])) {
                            $img = $sortable_img;
                        }

                        if (isset($c['class'])) $class = trim($class.' '.$c['class']);

                        if (!empty($c['url']) || !empty($c['onclick']) || !empty($c['sortdata'])) {
                            ?>
                            <a href="<?php echo (!empty($c['url'])) ? $c['url'] : 'javascript:void(0);'; ?>" <?php if (!empty($c['onclick'])) echo "onclick=\"javascript:{$c['onclick']}\""; if (!empty($c['sortdata'])) echo ' data-array="'.str::htmlentities($c['sortdata']['array']).'" data-orderby="'.str::htmlentities($c['sortdata']['orderby']).'" data-callback="'.str::htmlentities($c['sortdata']['callback']).'"'; ?>" style="<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element<?php if (!empty($c['sortdata'])) echo ' ploopi_array_sort'; ?> <?php echo $class; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></a>
                            <?php
                        }
                        else {
                            ?>
                            <span style="overflow:auto;<?php if (!empty($c['style'])) echo $c['style']; ?>" class="ploopi_explorer_element <?php echo $class; ?>"><p><span><?php echo $c['label'] == '' ? '&nbsp;' : $c['label']; ?></span><?php echo $img; ?></p></span>
                            <?php
                        }

                    }
                }
                ?>
            </div>

            <?php
            // Gestion de l'affichage des lignes de données
            ?>

            <div <?php if (!empty($array['options']['height'])) echo "style=\"height:{$array['options']['height']}px;overflow:auto;\""; ?> id="ploopi_explorer_values_outer_<?php echo $strArrayId; ?>">

                <div id="ploopi_explorer_values_inner_<?php echo $strArrayId; ?>">
                <?php
                if (!empty($array['values']))
                {
                    /*
                    // On se positionne sur la bonne page de données
                    reset($array['values']);
                    $intLines = $array['options']['limit'];

                    if ($intLines > 0 && $intLines < sizeof($array['values']))
                    {
                        for ( $i=0 ; $i < ($array['options']['page']-1) * $intLines ; $i++) next($array['values']);
                        if ($intLines > sizeof($array['values']) - ($array['options']['page']-1) * $intLines) $intLines = sizeof($array['values']) - ($array['options']['page']-1) * $intLines;
                    }
                    else $intLines = sizeof($array['values']);
                    */

                    // On se positionne sur la bonne page de données
                    reset($array['index']);
                    $intLines = $array['options']['limit'];

                    if ($intLines > 0 && $intLines < sizeof($array['index']))
                    {
                        for ( $i=0 ; $i < ($array['options']['page']-1) * $intLines ; $i++) next($array['index']);
                        if ($intLines > sizeof($array['index']) - ($array['options']['page']-1) * $intLines) $intLines = sizeof($array['index']) - ($array['options']['page']-1) * $intLines;
                    }
                    else $intLines = sizeof($array['index']);

                    // On parcourt le nombre de ligne souhaité
                    for ($i = 0 ; $i < $intLines ; $i++)
                    {
                        $v = $array['values'][current($array['index'])];

                        // alternance des couleurs (une ligne sur 2) : on joue sur les css
                        $color = (empty($color) || $color == 1) ? 2 : 1;
                        ?>
                        <div <?php if (!empty($v['id'])) echo "id=\"{$v['id']}\""; ?> class="ploopi_explorer_line_<?php echo $color; ?><?php if (!empty($v['class'])) echo " {$v['class']}"; ?>" <?php if (!empty($v['style'])) echo "style=\"{$v['style']}\""; ?>>
                            <?php
                            if (!empty($array['columns']['actions_right']))
                            {
                                foreach($array['columns']['actions_right'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div class="ploopi_explorer_tools<?php if (!empty($v['values'][$id]['class'])) echo ' '.$v['values'][$id]['class']; ?>" style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>"><?php echo $v['values'][$id]['label']; ?></div>
                                    <?php
                                }
                            }

                            if (!empty($array['columns']['actions_left']))
                            {
                                foreach($array['columns']['actions_left'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div class="ploopi_explorer_tools<?php if (!empty($v['values'][$id]['class'])) echo ' '.$v['values'][$id]['class']; ?>" style="width:<?php echo $c['width']; ?>px;float:left;<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>"><?php echo $v['values'][$id]['label']; ?></div>
                                    <?php
                                }
                            }



                            $option = (empty($v['option'])) ? '' : $v['option'];

                            if (!empty($v['link']) || !empty($v['onclick']))
                            {
                                $onclick = (empty($v['onclick'])) ? '' : "onclick=\"{$v['onclick']}\"";
                                $title = (empty($v['description'])) ? '' : 'title="'.strip_tags($v['description']).'"';
                                ?>
                                <a class="ploopi_explorer_link" href="<?php echo $v['link']; ?>" <?php echo $title ; ?> <?php echo $onclick ; ?> <?php echo $option; ?>>
                                <?php
                            }
                            if (!empty($array['columns']['right']))
                            {
                                foreach($array['columns']['right'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div style="width:<?php echo $c['width']; ?>px;float:right;<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element<?php if (!empty($v['values'][$id]['class'])) echo ' '.$v['values'][$id]['class']; ?>"><p><?php echo $v['values'][$id]['label']; ?></p></div>
                                    <?php
                                }
                            }

                            if (!empty($array['columns']['left']))
                            {
                                foreach($array['columns']['left'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div style="width:<?php echo $c['width']; ?>px;float:left;<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element<?php if (!empty($v['values'][$id]['class'])) echo ' '.$v['values'][$id]['class']; ?>"><p><?php echo $v['values'][$id]['label']; ?></p></div>
                                    <?php
                                }
                            }

                            if (!empty($array['columns']['auto']))
                            {
                                foreach($array['columns']['auto'] as $id => $c)
                                {
                                    if (!isset($v['values'][$id]['label']) || $v['values'][$id]['label'] == '') $v['values'][$id]['label'] = '&nbsp;';
                                    ?>
                                    <div style="<?php if (!empty($v['values'][$id]['style'])) echo $v['values'][$id]['style']; ?>" class="ploopi_explorer_element<?php if (!empty($v['values'][$id]['class'])) echo ' '.$v['values'][$id]['class']; ?>"><p><?php echo $v['values'][$id]['label']; ?></p></div>
                                    <?php
                                }
                            }

                            if (!empty($v['link']) || !empty($v['onclick']))
                            {
                                ?>
                                </a>
                                <?php
                            }

                            ?>
                        </div>
                        <?php

                        next($array['index']);
                    }
                }
                ?>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            ploopi.skin.array_renderupdate('<?php echo $strArrayId; ?>');
        </script>
        <?php
    }

    /**
     * Affiche un treeview
     *
     * @param array $nodes tableau associatif contenant les noeuds
     * @param array $treeview tableau contenant la hiérarchie des noeuds
     * @param string $node_id_sel identifiant du noeud sélectionné
     * @param string $node_id_from identifiant du noeud de départ (permet de n'afficher qu'un sous-ensemble)
     * @param boolean $viewall true tous les noeuds de l'arbre doivent être ouvert (false par défaut)
     * @return string code html du treeview
     */

    /**
     * Attributs d'un lien de l'arborescence (clés 'attrs' du libellé, 'node_attrs' du
     * dépliage) : data-ploopi-* lus par js/actions.js et js/skin.js, en remplacement de
     * 'onclick' et 'node_onclick' (CSP stricte). Les noms sont filtrés, les valeurs échappées.
     *
     * @param array $arrAttrs attributs (nom => valeur)
     * @return string attributs (HTML)
     */
    public static function treeview_attrs($arrAttrs)
    {
        $strAttrs = '';
        if (empty($arrAttrs) || !is_array($arrAttrs)) return $strAttrs;
        foreach($arrAttrs as $strNom => $mixValeur)
        {
            if (!preg_match('/^data-[a-z0-9-]+$/', $strNom)) continue;
            $strAttrs .= ' '.$strNom.'="'.str::htmlentities(is_array($mixValeur) ? json_encode($mixValeur) : (string) $mixValeur).'"';
        }
        return $strAttrs;
    }

    public function display_treeview(&$nodes, &$treeview, $node_id_sel = null, $node_id_from = null, $viewall = false)
    {
        // recherche du premier noeud
        if (is_null($node_id_from)) $node_id_from = key($treeview);

        if (!is_null($node_id_sel) && isset($nodes[$node_id_sel])) $nodesel = $nodes[$node_id_sel];

        // code html généré par ce niveau de boucle
        $html = '';

        if (isset($treeview[$node_id_from]))
        {
            $c = 0;
            foreach($treeview[$node_id_from] as $node_id)
            {
                // noeud courant
                $node = $nodes[$node_id];

                // true si le noeud courant est sélectionné
                $is_node_sel = (!is_null($node_id_sel) && ($node_id_sel == $node['id']));

                // parents du noeud sélectionné
                $nodesel_parents = (isset($nodesel)) ? $nodesel['parents'] : array();

                // parents du noeud courant
                $node_parents = array_merge($node['parents'], array($node['id']));

                // true si le noeud est ouvert : le noeud est ouvert si les parents du noeud courant et du noeud sélectionné se superposent
                $is_node_opened = ($viewall || sizeof(array_intersect_assoc($nodesel_parents, $node_parents)) == sizeof($node_parents));

                // true si le noeud est le dernier fils de son père
                $is_node_last = ($c == sizeof($treeview[$node_id_from])-1);

                // profondeur du noeud ( = nombre de noeuds parents)
                $node_depth = sizeof($node['parents']);

                $node_link = '';
                $bg = '';

                if ($node_depth == 1)
                {
                    // au premier niveau de profondeur, on ne crée pas de décalage
                    $marginleft = 0;
                }
                else
                {
                    $type_node = 'join';
                    if (isset($treeview[$node_id])) $type_node = ($is_node_sel || $is_node_opened) ? 'minus' : 'plus';

                    if (!$is_node_last)
                    {
                        $type_node .= 'bottom';
                        $bg = "background:url({$_SESSION['ploopi']['template_path']}/img/treeview/line.png) 0 0 repeat-y;";
                    }

                    $n_link = (empty($node['node_link'])) ? (empty($node['node_attrs']) ? 'javascript:void(0);' : '#') : $node['node_link'];
                    $n_onclick = (empty($node['node_onclick'])) ? '' : 'onclick="javascript:'.$node['node_onclick'].';"';
                    $n_onclick .= self::treeview_attrs(isset($node['node_attrs']) ? $node['node_attrs'] : null);

                    $node_link = "<a href=\"{$n_link}\" {$n_onclick}><img id=\"t{$node['id']}\" style=\"display:block;float:left;\" src=\"{$_SESSION['ploopi']['template_path']}/img/treeview/{$type_node}.png\" /></a>";

                    $marginleft = 20;
                }

                // récupération du code html des noeuds fils par un appel récursif
                $html_children = ($is_node_sel || $is_node_opened || $node_depth == 1) ? $this->display_treeview($nodes, $treeview, $node_id_sel, $node['id'], $viewall) : '';

                // si du contenu à afficher, display = 'block'
                $display = ($html_children == '') ? 'none' : 'block';

                // si le noeud courant est sélectionné on le met en gras
                $style_sel = ($is_node_sel) ? 'bold' : 'none';

                // lien sur le libellé
                $link = (empty($node['link'])) ? (empty($node['attrs']) ? 'javascript:void(0);' : '#') : $node['link'];

                // onclick sur le libellé (ou attributs data-* : js/actions.js, sans script en ligne)
                $onclick = (empty($node['onclick'])) ? '' : 'onclick="'.$node['onclick'].';"';
                $onclick .= self::treeview_attrs(isset($node['attrs']) ? $node['attrs'] : null);

                // label supplémentaire
                $status = (empty($node['status'])) ? '' : $node['status'];

                // génération du code html du noeud courant
                $html .= "
                    <div class=\"treeview_node\" id=\"treeview_node{$node['id']}\" style=\"{$bg}\">
                        <div>
                            {$node_link}<img src=\"{$node['icon']}\" />
                            <div style=\"display:block;margin-left:".($marginleft+20)."px;line-height:18px;font-weight:{$style_sel};\">
                                <a href=\"{$link}\" {$onclick}>".str::htmlentities($node['label'])."</a>
                                {$status}
                            </div>
                        </div>
                        <div style=\"margin-left:{$marginleft}px;display:{$display};\" id=\"n{$node['id']}\">{$html_children}</div>
                    </div>
                ";

                $c++;
            }
        }
        return $html;

    }


    /**
     * Affichage d'une liste de choix paramétrable
     *
     * @param string $id identifiant du champ de formulaire
     * @param string $name nom du champ de formulaire
     * @param array $arrValues tableau des valeurs de la liste
     * @param array $arrUserOptions options d'affichage
     * @param string $selecteditem clé de l'élément sélectionné
     * @return string code html de la liste
     */

    public function display_selectbox($id, $name, $arrValues, $arrUserOptions = null, $selecteditem = null)
    {
        // Options par défaut
        $arrOptions =
            array(
                'input_width' => null,
                'menu_width' => null,
                'onchange' => null
            );

        // Merge avec les options utilisateur
        $arrOptions = array_merge($arrOptions, $arrUserOptions);

        // Démarrage bufferisation
        ob_start();
        ?>
        <input type="hidden" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php if (!empty($selecteditem)) echo str::htmlentities($selecteditem); ?>" <?php if (!empty($arrOptions['onchange'])) echo 'onchange="javascript:'.$arrOptions['onchange'].'";'; ?>/>

        <div class="ploopi_selectbox" style="display:inline-block;<?php if (!empty($arrOptions['input_width'])) echo "width:{$arrOptions['input_width']};"; ?>">
            <div class="ploopi_selectbox_button" id="ploopi_selectbox_button<?php echo $id; ?>" onclick="javascript:document.getElementById('ploopi_selectbox_list<?php echo $id; ?>').style.display='block';">
                <div class="ploopi_selectbox_button_content" id="ploopi_selectbox_button_content<?php echo $id; ?>" >
                    <?php
                    if (!empty($arrValues[$selecteditem]))
                    {
                        $menu = $arrValues[$selecteditem];

                        if (!empty($menu['icon']))
                        {
                            ?>
                            <img src="<?php echo $menu['icon']; ?>">
                            <?php
                        }
                        ?>
                        <span><?php echo $menu['label']; ?></span><?php if (!empty($menu['label_extended'])) echo $menu['label_extended'];
                    }
                    ?>
                </div>
            </div>

            <div id="ploopi_selectbox_list<?php echo $id; ?>" class="ploopi_selectbox_list" style="display:none;<?php if (!empty($arrOptions['menu_width'])) echo "width:{$arrOptions['menu_width']};"; ?>" onclick="document.getElementById('ploopi_selectbox_list<?php echo $id; ?>').style.display='none';" onmouseout="document.getElementById('ploopi_selectbox_list<?php echo $id; ?>').style.display='none';">
                <ul onmouseover="document.getElementById('ploopi_selectbox_list<?php echo $id; ?>').style.display='block';">
                    <?php
                    foreach($arrValues as $key => $menu)
                    {
                        switch($menu['type'])
                        {
                            case 'group':
                                ?>
                                <li style="font-weight:bold;padding:2px 4px;"><?php echo $menu['label']; ?></li>
                                <?php
                            break;

                            case 'select':
                                ?>
                                <li>
                                    <a href="javascript:void(0);" <?php if (!empty($menu['onclick'])) echo 'onclick="javascript:'.$menu['onclick'].'"'; ?> onclick="javascript:document.getElementById('ploopi_selectbox_button_content<?php echo $id; ?>').innerHTML = this.innerHTML; document.getElementById('<?php echo $id; ?>').value = '<?php echo addslashes($key); ?>'; ploopi.event.dispatch_onchange('<?php echo $id; ?>');return false;" title="Accéder à <?php echo str::htmlentities($menu['label']); ?>">
                                        <?php
                                        if (!empty($menu['icon']))
                                        {
                                            ?>
                                            <img src="<?php echo $menu['icon']; ?>">
                                            <?php
                                        }
                                        ?>
                                        <span><?php echo $menu['label']; ?></span><?php if (!empty($menu['label_extended'])) echo $menu['label_extended']; ?>
                                    </a>
                                </li>
                                <?php
                            break;

                            case 'link':
                                ?>
                                <li>
                                    <a href="<?php echo $menu['link']; ?>" <?php if (!empty($menu['onclick'])) echo 'onclick="javascript:'.$menu['onclick'].'"'; ?> <?php if (!empty($menu['target'])) echo 'target="'.$menu['target'].'"'; ?> title="Accéder à <?php echo str::htmlentities($menu['label']); ?>">
                                        <?php
                                        if (!empty($menu['icon']))
                                        {
                                            ?>
                                            <img src="<?php echo $menu['icon']; ?>">
                                            <?php
                                        }
                                        ?>
                                        <span><?php echo $menu['label']; ?></span><?php if (!empty($menu['label_extended'])) echo $menu['label_extended']; ?>
                                    </a>
                                </li>
                                <?php
                            break;
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
        $strContent = ob_get_contents();
        ob_end_clean();

        return $strContent;
    }
}
