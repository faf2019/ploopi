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

namespace ploopi\system;

use ploopi;

/**
 * Composants des écrans refondus du module system (gabarits de génération 4).
 *
 * Produit le balisage des classes p4- de ploopi4 (templates/backoffice/ploopi4/css) :
 * en-têtes de page, cartes, sections de formulaire, champs, boutons, menus, états
 * vides. Les comportements (confirmation, validation, tableaux filtrables…) sont
 * portés par des attributs data-p4-* que lit modules/system/include/p4.js : aucun
 * gestionnaire en ligne.
 *
 * Les URL internes sont fournies déjà chiffrées (ploopi\crypt::urlencode) ; les
 * libellés sont fournis en clair et échappés ici.
 *
 * @package system
 * @subpackage ui
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class ui
{
    /**
     * Le script des comportements a-t-il déjà été déclaré dans la page ?
     *
     * @var boolean
     */

    private static $booScript = false;

    /**
     * Échappe un texte pour l'affichage
     *
     * @param string $str texte
     * @return string texte échappé
     */

    public static function e($str)
    {
        return ploopi\str::htmlentities((string) $str);
    }

    /**
     * Icône du sprite du gabarit
     *
     * @param string $strIcon nom du glyphe (tools/icones/ploopi4.txt)
     * @param string $strClass classes supplémentaires
     * @return string code html
     */

    public static function icon($strIcon, $strClass = '')
    {
        return ploopi\skin::get()->icon($strIcon, $strClass);
    }

    /**
     * Attributs html à partir d'un tableau (nom => valeur) ; true : attribut booléen,
     * false ou null : attribut absent
     *
     * @param array $arrAttrs attributs
     * @return string attributs, précédés d'une espace
     */

    public static function attrs($arrAttrs)
    {
        $strHtml = '';

        foreach($arrAttrs as $strName => $mixValue)
        {
            if ($mixValue === false || $mixValue === null) continue;
            $strName = preg_replace('/[^a-z0-9_:-]/', '', strtolower($strName));
            if ($mixValue === true) $strHtml .= " {$strName}";
            else $strHtml .= " {$strName}=\"".self::e($mixValue).'"';
        }

        return $strHtml;
    }

    /**
     * Déclare une fois le script des comportements des écrans (include/p4.js)
     *
     * @return string balise script, ou rien si déjà déclarée
     */

    public static function script()
    {
        if (self::$booScript) return '';
        self::$booScript = true;

        return '<script type="text/javascript" src="./modules/system/include/p4.js?v='.urlencode(_PLOOPI_VERSION).'"></script>';
    }

    /**
     * Bouton ou lien d'action
     *
     * Options : label, href (url chiffrée : lien), icon, class (primary, ghost, soft,
     * danger, sm, icon), title, confirm (message), type (button, submit), attrs
     * (attributs supplémentaires), disabled, hidelabel (libellé masqué sur petit écran)
     *
     * @param array $arrBtn options
     * @return string code html
     */

    public static function button($arrBtn)
    {
        $arrClasses = array('p4-btn');
        if (!empty($arrBtn['class']))
        {
            foreach(explode(' ', $arrBtn['class']) as $strClass) if ($strClass != '') $arrClasses[] = "p4-btn-{$strClass}";
        }

        $strLabel = isset($arrBtn['label']) ? $arrBtn['label'] : '';
        $booIconOnly = !empty($arrBtn['class']) && in_array('icon', explode(' ', $arrBtn['class']));

        $arrAttrs = array(
            'class' => implode(' ', $arrClasses),
            'title' => isset($arrBtn['title']) ? $arrBtn['title'] : ($booIconOnly ? $strLabel : null),
            'aria-label' => $booIconOnly ? $strLabel : null,
            'data-p4-confirm' => empty($arrBtn['confirm']) ? null : $arrBtn['confirm']
        );

        if (!empty($arrBtn['attrs'])) $arrAttrs = array_merge($arrAttrs, $arrBtn['attrs']);

        $strInner = (empty($arrBtn['icon']) ? '' : self::icon($arrBtn['icon'], 'p4-icon-sm'));
        if (!$booIconOnly && $strLabel != '') $strInner .= empty($arrBtn['hidelabel']) ? self::e($strLabel) : '<span class="p4-hide-sm">'.self::e($strLabel).'</span>';

        if (!empty($arrBtn['href']) && empty($arrBtn['disabled']))
        {
            return '<a'.self::attrs(array_merge(array('href' => $arrBtn['href']), $arrAttrs)).'>'.$strInner.'</a>';
        }

        $arrAttrs['type'] = empty($arrBtn['type']) ? 'button' : $arrBtn['type'];
        $arrAttrs['disabled'] = !empty($arrBtn['disabled']);

        return '<button'.self::attrs($arrAttrs).'>'.$strInner.'</button>';
    }

    /**
     * En-tête de page : titre, sous-titre, actions, lien de retour
     *
     * @param string $strTitle titre
     * @param string $strSubtitle sous-titre
     * @param array $arrActions boutons (cf. button()) ou code html
     * @param array $arrBack lien de retour : array(libellé, url chiffrée)
     * @return string code html
     */

    public static function pagehead($strTitle, $strSubtitle = '', $arrActions = array(), $arrBack = null)
    {
        $strHtml = '<div class="p4-page-head p4-ui"><div class="p4-grow">';

        if (!empty($arrBack)) $strHtml .= '<a class="p4-back" href="'.self::e($arrBack[1]).'">'.self::icon('arrow-left', 'p4-icon-sm').self::e($arrBack[0]).'</a>';

        $strHtml .= '<h1>'.self::e($strTitle).'</h1>';
        if ($strSubtitle != '') $strHtml .= '<p>'.self::e($strSubtitle).'</p>';
        $strHtml .= '</div>';

        if (!empty($arrActions)) $strHtml .= '<div class="p4-row p4-wrap">'.self::actions($arrActions).'</div>';

        return $strHtml.'</div>';
    }

    /**
     * Suite de boutons
     *
     * @param mixed $mixActions tableau de boutons (cf. button()) ou code html
     * @return string code html
     */

    public static function actions($mixActions)
    {
        if (!is_array($mixActions)) return $mixActions;

        $strHtml = '';
        foreach($mixActions as $mixAction) $strHtml .= is_array($mixAction) ? self::button($mixAction) : $mixAction;

        return $strHtml;
    }

    /**
     * Ouverture d'une carte
     *
     * @param string $strTitle titre (vide : pas d'en-tête)
     * @param string $strIcon icône du titre
     * @param mixed $mixActions actions de l'en-tête
     * @param array $arrOptions class, id, subtitle, tag (section par défaut), body (false : pas de corps)
     * @return string code html
     */

    public static function card_open($strTitle = '', $strIcon = '', $mixActions = array(), $arrOptions = array())
    {
        $strTag = empty($arrOptions['tag']) ? 'section' : $arrOptions['tag'];
        $strClass = 'p4-card p4-ui'.(empty($arrOptions['class']) ? '' : " {$arrOptions['class']}");

        $strHtml = "<{$strTag}".self::attrs(array('class' => $strClass, 'id' => isset($arrOptions['id']) ? $arrOptions['id'] : null)).'>';

        if ($strTitle != '' || !empty($mixActions))
        {
            $strHtml .= '<div class="p4-card-header">';
            if ($strIcon != '') $strHtml .= '<span class="p4-card-icon">'.self::icon($strIcon).'</span>';
            $strHtml .= '<div class="p4-grow"><h2>'.self::e($strTitle).'</h2>';
            if (!empty($arrOptions['subtitle'])) $strHtml .= '<p class="p4-card-sub">'.self::e($arrOptions['subtitle']).'</p>';
            $strHtml .= '</div>';
            if (!empty($mixActions)) $strHtml .= '<div class="p4-row p4-wrap">'.self::actions($mixActions).'</div>';
            $strHtml .= '</div>';
        }

        if (!isset($arrOptions['body']) || $arrOptions['body']) $strHtml .= '<div class="p4-card-body">';

        return $strHtml;
    }

    /**
     * Fermeture d'une carte
     *
     * @param string $strFooter pied de carte (code html)
     * @param array $arrOptions tag, body (cf. card_open())
     * @return string code html
     */

    public static function card_close($strFooter = '', $arrOptions = array())
    {
        $strTag = empty($arrOptions['tag']) ? 'section' : $arrOptions['tag'];

        $strHtml = (!isset($arrOptions['body']) || $arrOptions['body']) ? '</div>' : '';
        if ($strFooter != '') $strHtml .= '<div class="p4-card-footer p4-row p4-wrap">'.$strFooter.'</div>';

        return $strHtml."</{$strTag}>";
    }

    /**
     * Ouverture d'une section de formulaire : titre et explication, puis champs
     *
     * @param string $strTitle titre
     * @param string $strDesc explication
     * @param string $strIcon icône
     * @return string code html
     */

    public static function section_open($strTitle, $strDesc = '', $strIcon = '')
    {
        $strHtml = '<section class="p4-fsec"><div class="p4-fsec-head">';
        $strHtml .= '<h3>'.($strIcon == '' ? '' : self::icon($strIcon, 'p4-icon-sm')).self::e($strTitle).'</h3>';
        if ($strDesc != '') $strHtml .= '<p>'.self::e($strDesc).'</p>';

        return $strHtml.'</div><div class="p4-fsec-body p4-fgrid">';
    }

    /**
     * Fermeture d'une section de formulaire
     *
     * @return string code html
     */

    public static function section_close()
    {
        return '</div></section>';
    }

    /**
     * Champ : libellé au-dessus, contrôle, aide
     *
     * Options : for (identifiant du contrôle), hint (aide), width (c3, c4, c6, c12),
     * required (marque *), class
     *
     * @param string $strLabel libellé
     * @param string $strControl code html du contrôle
     * @param array $arrOptions options
     * @return string code html
     */

    public static function field($strLabel, $strControl, $arrOptions = array())
    {
        $strWidth = empty($arrOptions['width']) ? 'c6' : $arrOptions['width'];
        $strClass = "p4-field p4-{$strWidth}".(empty($arrOptions['class']) ? '' : " {$arrOptions['class']}");

        $strHtml = '<div class="'.self::e($strClass).'">';

        if ($strLabel != '')
        {
            $strHtml .= '<label'.self::attrs(array('for' => empty($arrOptions['for']) ? null : $arrOptions['for'])).'>'.self::e($strLabel);
            if (!empty($arrOptions['required'])) $strHtml .= ' <abbr class="p4-req" title="obligatoire">*</abbr>';
            $strHtml .= '</label>';
        }

        $strHtml .= $strControl;
        if (!empty($arrOptions['hint'])) $strHtml .= '<span class="p4-hint">'.$arrOptions['hint'].'</span>';

        return $strHtml.'</div>';
    }

    /**
     * Zone de saisie
     *
     * @param string $strName nom (et identifiant par défaut)
     * @param string $strValue valeur
     * @param array $arrAttrs attributs supplémentaires (type, id, required, check, label…)
     * @return string code html
     */

    public static function input($strName, $strValue = '', $arrAttrs = array())
    {
        $arrAttrs = array_merge(array('class' => 'p4-input', 'type' => 'text', 'name' => $strName, 'id' => $strName, 'value' => (string) $strValue), self::_check($arrAttrs));

        return '<input'.self::attrs($arrAttrs).' />';
    }

    /**
     * Zone de texte
     *
     * @param string $strName nom (et identifiant par défaut)
     * @param string $strValue valeur
     * @param array $arrAttrs attributs supplémentaires
     * @return string code html
     */

    public static function textarea($strName, $strValue = '', $arrAttrs = array())
    {
        $arrAttrs = array_merge(array('class' => 'p4-textarea', 'name' => $strName, 'id' => $strName), self::_check($arrAttrs));

        return '<textarea'.self::attrs($arrAttrs).'>'.self::e($strValue).'</textarea>';
    }

    /**
     * Liste de choix
     *
     * @param string $strName nom (et identifiant par défaut)
     * @param array $arrChoices valeur => libellé
     * @param mixed $mixSelected valeur choisie
     * @param array $arrAttrs attributs supplémentaires ; « empty » : libellé d'un choix vide en tête
     * @return string code html
     */

    public static function select($strName, $arrChoices, $mixSelected = null, $arrAttrs = array())
    {
        $strEmpty = null;
        if (array_key_exists('empty', $arrAttrs))
        {
            $strEmpty = $arrAttrs['empty'];
            unset($arrAttrs['empty']);
        }

        $arrAttrs = array_merge(array('class' => 'p4-select', 'name' => $strName, 'id' => $strName), self::_check($arrAttrs));

        $strHtml = '<select'.self::attrs($arrAttrs).'>';
        if (!is_null($strEmpty)) $strHtml .= '<option value="">'.self::e($strEmpty).'</option>';

        foreach($arrChoices as $mixValue => $strLabel)
        {
            $booSel = !is_null($mixSelected) && (string) $mixValue === (string) $mixSelected;
            $strHtml .= '<option value="'.self::e($mixValue).'"'.($booSel ? ' selected="selected"' : '').'>'.self::e($strLabel).'</option>';
        }

        return $strHtml.'</select>';
    }

    /**
     * Interrupteur (case à cocher)
     *
     * @param string $strName nom (et identifiant par défaut)
     * @param boolean $booChecked coché
     * @param string $strLabel libellé
     * @param string $strHint explication
     * @param array $arrAttrs attributs supplémentaires de la case
     * @return string code html
     */

    public static function toggle($strName, $booChecked, $strLabel, $strHint = '', $arrAttrs = array())
    {
        $arrAttrs = array_merge(array('type' => 'checkbox', 'name' => $strName, 'id' => $strName, 'value' => '1', 'checked' => (bool) $booChecked), $arrAttrs);

        $strHtml = '<label class="p4-switch p4-toggle"><input'.self::attrs($arrAttrs).' /><span class="p4-track"></span>';
        $strHtml .= '<span class="p4-toggle-text"><span>'.self::e($strLabel).'</span>';
        if ($strHint != '') $strHtml .= '<small>'.$strHint.'</small>';

        return $strHtml.'</span></label>';
    }

    /**
     * Date saisie avec le sélecteur du navigateur, transmise au format local
     * (_PLOOPI_DATEFORMAT) dans un champ caché : les traitements existants la lisent
     * comme avant (ploopi\date::local2timestamp)
     *
     * @param string $strName nom du champ transmis
     * @param string $strTimestamp date au format YmdHis (ou vide)
     * @param array $arrAttrs attributs supplémentaires du sélecteur
     * @return string code html
     */

    public static function date($strName, $strTimestamp = '', $arrAttrs = array())
    {
        $strIso = '';
        $strLocal = '';

        if (!empty($strTimestamp) && preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})/', $strTimestamp, $arrMatches))
        {
            $strIso = "{$arrMatches[1]}-{$arrMatches[2]}-{$arrMatches[3]}";
            $arrLocal = ploopi\date::timestamp2local($strTimestamp);
            $strLocal = $arrLocal['date'];
        }

        $arrAttrs = array_merge(array('class' => 'p4-input', 'type' => 'date', 'id' => $strName, 'value' => $strIso, 'data-p4-date' => $strName, 'data-p4-date-format' => _PLOOPI_DATEFORMAT), $arrAttrs);

        return '<input'.self::attrs($arrAttrs).' /><input type="hidden" name="'.self::e($strName).'" value="'.self::e($strLocal).'" />';
    }

    /**
     * Valeur en lecture seule
     *
     * @param string $strValue valeur (vide : tiret)
     * @param boolean $booMultiline conserver les retours à la ligne
     * @return string code html
     */

    public static function readonly($strValue, $booMultiline = false)
    {
        if (trim((string) $strValue) === '') return '<div class="p4-readonly p4-muted">—</div>';

        return '<div class="p4-readonly">'.($booMultiline ? ploopi\str::nl2br(self::e($strValue)) : self::e($strValue)).'</div>';
    }

    /**
     * Barre d'actions d'un formulaire (en bas, fixe dans la zone qui défile)
     *
     * @param mixed $mixActions boutons (cf. button()) ou code html
     * @param string $strNote mention à gauche (code html)
     * @return string code html
     */

    public static function formactions($mixActions, $strNote = '')
    {
        return '<div class="p4-form-actions">'.($strNote == '' ? '' : '<span class="p4-grow p4-text-xs p4-muted">'.$strNote.'</span>').self::actions($mixActions).'</div>';
    }

    /**
     * État vide
     *
     * @param string $strIcon icône
     * @param string $strText texte
     * @param mixed $mixActions actions
     * @return string code html
     */

    public static function emptystate($strIcon, $strText, $mixActions = array())
    {
        $strHtml = '<div class="p4-empty">'.self::icon($strIcon).'<p>'.self::e($strText).'</p>';
        if (!empty($mixActions)) $strHtml .= '<div class="p4-row p4-empty-actions">'.self::actions($mixActions).'</div>';

        return $strHtml.'</div>';
    }

    /**
     * Encadré
     *
     * @param string $strType info, warning, danger, success
     * @param string $strHtml contenu (code html)
     * @param string $strIcon icône (par défaut selon le type)
     * @return string code html
     */

    public static function callout($strType, $strHtml, $strIcon = '')
    {
        $arrIcons = array('info' => 'info-circle', 'warning' => 'alert-triangle', 'danger' => 'alert-triangle', 'success' => 'circle-check');
        if ($strIcon == '') $strIcon = isset($arrIcons[$strType]) ? $arrIcons[$strType] : 'info-circle';

        return '<div class="p4-callout p4-callout-'.self::e($strType).' p4-ui" role="'.($strType == 'danger' ? 'alert' : 'status').'">'.self::icon($strIcon).'<div class="p4-callout-body">'.$strHtml.'</div></div>';
    }

    /**
     * Pastille
     *
     * @param string $strText texte
     * @param string $strType brand, success, warning, danger, info (vide : neutre)
     * @return string code html
     */

    public static function badge($strText, $strType = '')
    {
        return '<span class="p4-badge'.($strType == '' ? '' : ' p4-badge-'.self::e($strType)).'">'.self::e($strText).'</span>';
    }

    /**
     * Menu déroulant
     *
     * @param string $strId identifiant du menu
     * @param array $arrButton bouton d'ouverture (cf. button())
     * @param array $arrItems entrées : array(label, href, icon, class, confirm, desc) ; '-' : séparateur
     * @param boolean $booLeft menu aligné à gauche
     * @return string code html
     */

    public static function menu($strId, $arrButton, $arrItems, $booLeft = false)
    {
        $arrButton['attrs'] = array_merge(isset($arrButton['attrs']) ? $arrButton['attrs'] : array(), array('data-p4-menu' => $strId, 'aria-expanded' => 'false', 'aria-haspopup' => 'true'));

        $strHtml = '<div class="p4-menu-wrap">'.self::button($arrButton).'<div class="p4-menu'.($booLeft ? ' p4-menu-left' : '').'" id="'.self::e($strId).'">';

        foreach($arrItems as $mixItem)
        {
            if ($mixItem === '-')
            {
                $strHtml .= '<div class="p4-menu-sep"></div>';
                continue;
            }

            $strInner = (empty($mixItem['icon']) ? '' : self::icon($mixItem['icon'])).'<span>'.self::e($mixItem['label']).(empty($mixItem['desc']) ? '' : '<small>'.self::e($mixItem['desc']).'</small>').'</span>';
            $arrAttrs = array(
                'class' => 'p4-menu-item'.(empty($mixItem['class']) ? '' : " {$mixItem['class']}"),
                'data-p4-confirm' => empty($mixItem['confirm']) ? null : $mixItem['confirm']
            );

            if (!empty($mixItem['href'])) $strHtml .= '<a'.self::attrs(array_merge(array('href' => $mixItem['href']), $arrAttrs)).'>'.$strInner.'</a>';
            else $strHtml .= '<button'.self::attrs(array_merge(array('type' => 'button', 'disabled' => true), $arrAttrs)).'>'.$strInner.'</button>';
        }

        return $strHtml.'</div></div>';
    }

    /**
     * Ligne de chiffres (compteurs)
     *
     * @param array $arrStats array(libellé, valeur, icône)
     * @return string code html
     */

    public static function stats($arrStats)
    {
        $strHtml = '<div class="p4-stats p4-ui">';
        foreach($arrStats as $arrStat)
        {
            $strHtml .= '<div class="p4-stat">'.(empty($arrStat[2]) ? '' : self::icon($arrStat[2])).'<div><strong>'.self::e($arrStat[1]).'</strong><span>'.self::e($arrStat[0]).'</span></div></div>';
        }

        return $strHtml.'</div>';
    }

    /**
     * Contrôle segmenté de navigation (sous-écrans)
     *
     * @param array $arrItems clé => array(libellé, url chiffrée, icône)
     * @param string $strCurrent clé courante
     * @param string $strLabel nom accessible
     * @return string code html
     */

    public static function seg($arrItems, $strCurrent, $strLabel = '')
    {
        $strHtml = '<nav class="p4-seg p4-ui"'.($strLabel == '' ? '' : ' aria-label="'.self::e($strLabel).'"').'>';

        foreach($arrItems as $strKey => $arrItem)
        {
            $booCur = $strKey == $strCurrent;
            $strHtml .= '<a href="'.self::e($arrItem[1]).'"'.($booCur ? ' class="p4-active" aria-current="page"' : '').'>'.(empty($arrItem[2]) ? '' : self::icon($arrItem[2], 'p4-icon-sm')).self::e($arrItem[0]).'</a>';
        }

        return $strHtml.'</nav>';
    }

    /**
     * Tableau filtrable, triable et paginé à partir des structures de
     * ploopi\skin::display_array() (colonnes par groupe, valeurs déjà échappées) :
     * les écrans historiques passent à ce rendu sans changer leurs données.
     *
     * Ordre des colonnes : actions_left, left, auto, right (de droite à gauche, comme
     * l'affichage historique), actions_right.
     *
     * Options : sortable, orderby_default, sort_default (ASC, DESC), limit (lignes par
     * page), filter (texte d'aide du champ de filtre ; absent : pas de filtre), empty
     * (texte de l'état vide), class (classes du tableau), count (libellé du nombre de
     * lignes)
     *
     * @param array $arrColumns colonnes de display_array
     * @param array $arrValues lignes de display_array
     * @param array $arrOptions options
     * @return string code html
     */

    public static function table($arrColumns, $arrValues, $arrOptions = array())
    {
        $arrCols = array();
        foreach(array('actions_left', 'left', 'auto') as $strGroup)
        {
            if (!empty($arrColumns[$strGroup])) foreach($arrColumns[$strGroup] as $strKey => $arrCol) $arrCols[$strKey] = $arrCol + array('group' => $strGroup);
        }
        if (!empty($arrColumns['right'])) foreach(array_reverse($arrColumns['right'], true) as $strKey => $arrCol) $arrCols[$strKey] = $arrCol + array('group' => 'right');
        if (!empty($arrColumns['actions_right'])) foreach($arrColumns['actions_right'] as $strKey => $arrCol) $arrCols[$strKey] = $arrCol + array('group' => 'actions_right');

        $booSortable = !empty($arrOptions['sortable']);
        $intLimit = empty($arrOptions['limit']) ? 0 : (int) $arrOptions['limit'];
        $strDefault = empty($arrOptions['orderby_default']) ? '' : $arrOptions['orderby_default'];
        $booDesc = !empty($arrOptions['sort_default']) && strtoupper($arrOptions['sort_default']) == 'DESC';

        $strHtml = '<div class="p4-ui p4-dtable" data-p4-table="'.$intLimit.'">';

        if (isset($arrOptions['filter']) || isset($arrOptions['count']))
        {
            $strHtml .= '<div class="p4-table-tools">';
            if (isset($arrOptions['filter'])) $strHtml .= '<div class="p4-input-icon p4-list-search">'.self::icon('search', 'p4-icon-sm').'<input class="p4-input" type="search" data-p4-table-filter placeholder="'.self::e($arrOptions['filter']).'" aria-label="'.self::e($arrOptions['filter']).'" /></div>';
            if (isset($arrOptions['count'])) $strHtml .= '<span class="p4-text-xs p4-muted"><span data-p4-table-count>'.sizeof($arrValues).'</span> '.self::e($arrOptions['count']).'</span>';
            $strHtml .= '</div>';
        }

        if (empty($arrValues))
        {
            return $strHtml.self::emptystate('list-details', isset($arrOptions['empty']) ? $arrOptions['empty'] : '—').'</div>';
        }

        $strHtml .= '<div class="p4-table-wrap"><table class="p4-table'.(empty($arrOptions['class']) ? '' : ' '.self::e($arrOptions['class'])).'"><thead><tr>';

        foreach($arrCols as $strKey => $arrCol)
        {
            $strLabel = trim(html_entity_decode(strip_tags($arrCol['label']), ENT_QUOTES, 'UTF-8'));
            $booActions = strpos($arrCol['group'], 'actions') === 0;
            $strAttrs = $booActions ? ' class="p4-col-acts"' : '';
            if ($booSortable && $strKey == $strDefault) $strAttrs .= ' aria-sort="'.($booDesc ? 'descending' : 'ascending').'"';

            if ($booActions) $strHtml .= '<th'.$strAttrs.'><span class="p4-sr-only">'.self::e($strLabel == '' ? 'Actions' : $strLabel).'</span></th>';
            elseif ($booSortable && !empty($arrCol['options']['sort'])) $strHtml .= '<th'.$strAttrs.'><button type="button" class="p4-sort" data-p4-col>'.self::e($strLabel).self::icon('arrows-sort').'</button></th>';
            else $strHtml .= '<th'.$strAttrs.'>'.self::e($strLabel).'</th>';
        }

        $strHtml .= '</tr></thead><tbody>';

        foreach($arrValues as $arrRow)
        {
            $arrRowValues = isset($arrRow['values']) ? $arrRow['values'] : array();
            $strHtml .= '<tr'.(empty($arrRow['class']) ? '' : ' class="'.self::e($arrRow['class']).'"').(empty($arrRow['description']) ? '' : ' title="'.self::e(strip_tags($arrRow['description'])).'"').'>';

            $booFirst = true;
            foreach($arrCols as $strKey => $arrCol)
            {
                $arrCell = isset($arrRowValues[$strKey]) ? $arrRowValues[$strKey] : array('label' => '');
                $strLabel = isset($arrCell['label']) ? (string) $arrCell['label'] : '';
                $booActions = strpos($arrCol['group'], 'actions') === 0;

                // Lien de la ligne : sur la première cellule, si elle n'en porte pas déjà
                if ($booFirst && !empty($arrRow['link']) && stripos($strLabel, '<a ') === false) $strLabel = '<a class="p4-rowlink" href="'.self::e($arrRow['link']).'">'.$strLabel.'</a>';
                $booFirst = false;

                $arrAttrs = array(
                    'class' => $booActions ? 'p4-col-acts' : (empty($arrCell['class']) ? null : $arrCell['class']),
                    'data-p4-sort-value' => isset($arrCell['sort_label']) ? mb_strtolower(strip_tags((string) $arrCell['sort_label'])) : null,
                    'data-label' => $booActions ? null : trim(html_entity_decode(strip_tags($arrCol['label']), ENT_QUOTES, 'UTF-8'))
                );

                $strHtml .= '<td'.self::attrs($arrAttrs).'>'.($booActions ? '<div class="p4-acts">'.$strLabel.'</div>' : $strLabel).'</td>';
            }

            $strHtml .= '</tr>';
        }

        $strHtml .= '</tbody></table></div>';
        $strHtml .= '<div data-p4-table-empty hidden>'.self::emptystate('search', isset($arrOptions['empty']) ? $arrOptions['empty'] : '—').'</div>';
        if ($intLimit) $strHtml .= '<div class="p4-table-foot"><span></span><div class="p4-pager" data-p4-table-pager></div></div>';

        return $strHtml.'</div>';
    }

    /**
     * Compte rendu d'opérations (installation, mise à jour d'un module)
     *
     * @param array $arrReport lignes : array('operation' => libellé, 'detail' => détail, 'res' => réussite)
     * @return string code html
     */

    public static function report($arrReport)
    {
        $strHtml = '<ul class="p4-report p4-ui">';

        foreach($arrReport as $arrLine)
        {
            // Les détails historiques séparent leurs lignes par des <br />
            $strDetail = str_replace(array('<br />', '<br/>', '<br>'), "\n", (string) $arrLine['detail']);

            $strHtml .= '<li>'.self::icon(empty($arrLine['res']) ? 'circle-x' : 'circle-check', empty($arrLine['res']) ? 'p4-ko' : 'p4-ok')
                .'<strong>'.self::e(strip_tags($arrLine['operation'])).'</strong>'
                .'<span>'.ploopi\str::nl2br(self::e(strip_tags($strDetail))).'</span></li>';
        }

        return $strHtml.'</ul>';
    }

    /**
     * Interrupteur sous forme de lien (bascule d'un état par une opération)
     *
     * @param boolean $booOn état
     * @param string $strHref url chiffrée de la bascule (vide : lecture seule)
     * @param string $strLabel nom accessible
     * @return string code html
     */

    public static function switchlink($booOn, $strHref, $strLabel)
    {
        $strClass = 'p4-switch p4-switchlink'.($booOn ? ' p4-on' : '');

        if ($strHref == '') return '<span class="'.$strClass.' p4-switch-ro" role="img" aria-label="'.self::e($strLabel.' : '.($booOn ? 'oui' : 'non')).'"><span class="p4-track"></span></span>';

        return '<a class="'.$strClass.'" href="'.self::e($strHref).'" role="switch" aria-checked="'.($booOn ? 'true' : 'false').'" title="'.self::e($strLabel).'"><span class="p4-track"></span><span class="p4-sr-only">'.self::e($strLabel).'</span></a>';
    }

    /**
     * Attributs de validation : « check » (type de ploopi.validatefield) et « label »
     * (libellé du message) deviennent data-p4-check et data-p4-label
     *
     * @param array $arrAttrs attributs
     * @return array attributs
     */

    private static function _check($arrAttrs)
    {
        foreach(array('check' => 'data-p4-check', 'label' => 'data-p4-label') as $strKey => $strAttr)
        {
            if (isset($arrAttrs[$strKey]))
            {
                $arrAttrs[$strAttr] = $arrAttrs[$strKey];
                unset($arrAttrs[$strKey]);
            }
        }

        return $arrAttrs;
    }
}
