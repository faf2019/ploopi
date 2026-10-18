<?php
/*
    Copyright (c) 2007-2026 Ovensia
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
 * Gestion du skin 'ploopi4'
 *
 * Shell adaptatif (barre supérieure, menu latéral, thèmes clair et sombre,
 * densité) ; rendu du socle et des modules repris de ploopi3, sur la palette de
 * ploopi4 ; icônes vectorielles (img/icons.svg).
 *
 * @package ploopi
 * @subpackage skin
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 *
 * @see skin_common
 */

class skin extends ploopi\skin
{
    /**
     * Construction de la classe skin 'ploopi4'
     *
     * @return skin
     */

    function __construct()
    {
        parent::__construct('ploopi4');
    }

    /**
     * Code HTML d'une icône du sprite du gabarit (img/icons.svg)
     *
     * @param string $strIcon nom de l'icône (glyphe Tabler, cf. tools/icones/ploopi4.txt)
     * @param string $strClass classes CSS supplémentaires
     * @return string code html de l'icône
     */

    public function icon($strIcon, $strClass = '')
    {
        $strIcon = preg_replace('/[^a-z0-9-]/', '', strtolower($strIcon));
        $strClass = trim('p4-icon '.preg_replace('/[^a-zA-Z0-9_ -]/', '', $strClass));

        return "<svg class=\"{$strClass}\" aria-hidden=\"true\"><use href=\"{$this->values['path']}/icons.svg#i-{$strIcon}\"/></svg>";
    }

    /**
     * Crée un titre de page : un titre de niveau 1, à l'aspect de l'en-tête des écrans
     * de ploopi4 (le titre est fourni en html par le module, comme pour le socle)
     *
     * @param string $title titre de la page
     * @param string $style styles optionnels
     * @param string $additionnal_title complément (html), affiché sous le titre
     * @return string code html du titre
     */

    public function create_pagetitle($title, $style = '', $additionnal_title = '')
    {
        return '<div class="pagetitle p4-pagetitle"'.(strlen($style) > 0 ? ' style="'.$style.'"' : '').'>'
            .'<h1>'.$title.'</h1>'
            .($additionnal_title == '' ? '' : '<div class="p4-pagetitle-extra">'.$additionnal_title.'</div>')
            .'</div>';
    }

    /**
     * Affiche une arborescence : mêmes nœuds, mêmes identifiants (treeview_node<id>,
     * t<id>, n<id>) et même logique d'ouverture que le socle, lus par
     * ploopi.skin.treeview_shownode() ; chevron du sprite au lieu des images plus/moins,
     * filets et sélection en CSS (css/base_9_modules.css).
     *
     * @param array $nodes tableau associatif contenant les noeuds
     * @param array $treeview tableau contenant la hiérarchie des noeuds
     * @param string $node_id_sel identifiant du noeud sélectionné
     * @param string $node_id_from identifiant du noeud de départ
     * @param boolean $viewall true si tous les noeuds doivent être ouverts
     * @return string code html de l'arborescence
     */

    public function display_treeview(&$nodes, &$treeview, $node_id_sel = null, $node_id_from = null, $viewall = false)
    {
        if (is_null($node_id_from)) $node_id_from = key($treeview);
        if (!isset($treeview[$node_id_from])) return '';

        $nodesel_parents = (!is_null($node_id_sel) && isset($nodes[$node_id_sel])) ? $nodes[$node_id_sel]['parents'] : array();

        $html = '';

        foreach($treeview[$node_id_from] as $node_id)
        {
            $node = $nodes[$node_id];

            $is_node_sel = (!is_null($node_id_sel) && $node_id_sel == $node['id']);
            $node_parents = array_merge($node['parents'], array($node['id']));
            // Ouvert si les parents du noeud courant et du noeud sélectionné se superposent
            $is_node_opened = ($viewall || sizeof(array_intersect_assoc($nodesel_parents, $node_parents)) == sizeof($node_parents));
            $node_depth = sizeof($node['parents']);

            $html_children = ($is_node_sel || $is_node_opened || $node_depth == 1) ? $this->display_treeview($nodes, $treeview, $node_id_sel, $node['id'], $viewall) : '';
            $booOpen = $html_children != '';

            // Bouton de dépliage (à partir du second niveau, comme le socle)
            $strToggle = '';
            if ($node_depth > 1)
            {
                if (isset($treeview[$node_id]))
                {
                    $n_link = empty($node['node_link']) ? (empty($node['node_attrs']) ? 'javascript:void(0);' : '#') : $node['node_link'];
                    $n_onclick = empty($node['node_onclick']) ? '' : ' onclick="javascript:'.$node['node_onclick'].';"';
                    $n_onclick .= self::treeview_attrs(isset($node['node_attrs']) ? $node['node_attrs'] : null);
                    $strToggle = '<a class="p4-tree-toggle" href="'.$n_link.'"'.$n_onclick.' aria-label="'.ploopi\str::htmlentities(_PLOOPI_TREE_TOGGLE).'">'
                        .'<span class="p4-tree-twisty" id="t'.$node['id'].'" data-p4-open="'.(($is_node_sel || $is_node_opened) ? '1' : '0').'">'.$this->icon('chevron-right').'</span></a>';
                }
                else $strToggle = '<span class="p4-tree-toggle"></span>';
            }

            $link = empty($node['link']) ? (empty($node['attrs']) ? 'javascript:void(0);' : '#') : $node['link'];
            $onclick = empty($node['onclick']) ? '' : ' onclick="'.$node['onclick'].';"';
            $onclick .= self::treeview_attrs(isset($node['attrs']) ? $node['attrs'] : null);
            $status = empty($node['status']) ? '' : $node['status'];
            $strIcon = empty($node['icon']) ? '' : '<img class="p4-tree-icon" src="'.ploopi\str::htmlentities($node['icon']).'" alt="" />';

            $html .= '<div class="treeview_node p4-tree-node'.($node_depth == 1 ? ' p4-tree-root' : '').'" id="treeview_node'.$node['id'].'">'
                .'<div class="p4-tree-row'.($is_node_sel ? ' p4-tree-sel' : '').'">'
                .$strToggle.$strIcon
                .'<a class="p4-tree-label" href="'.$link.'"'.$onclick.($is_node_sel ? ' aria-current="true"' : '').'>'.ploopi\str::htmlentities($node['label']).'</a>'
                .$status
                .'</div>'
                .'<div class="p4-tree-children" id="n'.$node['id'].'" style="display:'.($booOpen ? 'block' : 'none').';">'.$html_children.'</div>'
                .'</div>';
        }

        return $html;
    }

    /**
     * En-tête d'une fenêtre : titre (poignée de déplacement) et bouton de fermeture.
     * Mêmes classes que le socle (handle_<id>, ploopi_popup_close + data-popup), lues
     * par jQuery UI et par l'écouteur délégué de js/utilities.js.
     *
     * @param string $title titre (html)
     * @param string $popupid id de la fenêtre
     * @param boolean $booClosable true si la fenêtre se ferme
     * @return string code html
     */

    private function _popup_head($title, $popupid, $booClosable = true)
    {
        $strClose = $booClosable ? '<button type="button" class="ploopi_popup_close p4-popup-close" id="close_'.$popupid.'" data-popup="'.$popupid.'" title="Fermer" aria-label="Fermer">'.$this->icon('x').'</button>' : '';

        return '<div class="simplebloc_title p4-popup-head"><div class="simplebloc_titleleft"><div class="p4-popup-title handle_'.$popupid.'">'.$title.'</div>'.$strClose.'</div></div>';
    }

    /**
     * Crée un faux popup (div) : même structure que le socle, en-tête de ploopi4
     *
     * @param string $title titre du popup
     * @param string $content contenu du popup (html)
     * @param string $popupid id du popup (propriété html id)
     * @return string code html du popup
     */

    public function create_popup($title, $content, $popupid = 'ploopi_popup')
    {
        // Déplaçable, et mise en vue selon system_focus_popup, par js/popup.js dès son
        // insertion (ploopi.popup.enhance) : pas de script renvoyé avec la fenêtre (CSP)
        $strFocus = empty($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_focus_popup']) ? '' : ' data-p4-popup-focus="1"';

        return '
                <div class="simplebloc p4-popup" style="margin:0;" data-p4-popup="'.ploopi\str::htmlentities($popupid).'"'.$strFocus.'>
                    <a name="anchor_'.$popupid.'"></a>
                    '.$this->_popup_head($title, $popupid).'
                    <div class="simplebloc_content">'.$content.'</div>
                </div>
                ';
    }

    /**
     * Crée un faux popup et l'ouvre via javascript : même structure que le socle,
     * en-tête de ploopi4
     *
     * @param string $title titre du popup
     * @param string $content contenu du popup (html)
     * @param string $popupid id du popup (propriété html id)
     * @param array $arrOptions options (cf. ploopi\skin::open_popup)
     * @return string code html du popup
     */

    public function open_popup($title, $content, $popupid = 'ploopi_popup', $arrOptions = array())
    {
        $arrOptions = array_merge(array(
            'intWidth' => 200,
            'booCentered' => true,
            'booClosable' => true,
            'intPosx' => 0,
            'intPosy' => 0,
            'stringJsBeforeStart' => '',
            'stringJsAfterFinish' => '',
        ), $arrOptions);

        $strOptionAnchor = ($_SESSION['ploopi']['modules'][_PLOOPI_MODULE_SYSTEM]['system_focus_popup']) ? "document.location.href = '#anchor_{$popupid}';" : '';

        return '
                <div id="'.$popupid.'" style="display:none;">
                    <div class="simplebloc p4-popup" style="margin:0;">
                        <a name="anchor_'.$popupid.'"></a>
                        '.$this->_popup_head($title, $popupid, $arrOptions['booClosable']).'
                        <div class="simplebloc_content">'.$content.'</div>
                    </div>
                </div>
                <script type="text/javascript" nonce="'.ploopi\csp::nonce().'">
                    jQuery(function() {
                        '.$arrOptions['stringJsBeforeStart'].'
                        ploopi.popup.ize(\''.$popupid.'\', '.$arrOptions['intWidth'].', '.($arrOptions['booCentered'] ? 'true' : 'false').', '.$arrOptions['intPosx'].', '.$arrOptions['intPosy'].');
                        jQuery(\'#'.$popupid.'\').draggable({ zindex: 99999, handle: \'.handle_'.$popupid.'\'});
                        '.$arrOptions['stringJsAfterFinish'].'
                        '.$strOptionAnchor.'
                    });
                </script>
                ';
    }
}
?>
