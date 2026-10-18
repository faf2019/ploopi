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

namespace ploopi;

use ploopi;

/**
 * Choix de groupes et d'utilisateurs (partages, validateurs) : rendu de ploopi4.
 *
 * share::selectusers() et validation::selectusers(), et leurs opérations de sélection,
 * gardent leur balisage historique sous ploopi2/ploopi3. Sous ploopi4, ils délèguent
 * ici : un champ de recherche avec son bouton, la zone des résultats, puis la sélection
 * en pastilles qu'un clic retire. Les identifiants des éléments, les opérations et les
 * attributs data-ploopi-* sont inchangés.
 *
 * @package ploopi
 * @subpackage userselect
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */

abstract class userselect
{
    /**
     * Rendu de ploopi4 ?
     *
     * @return boolean
     */
    public static function modern()
    {
        return skin::get()->version() >= 4;
    }

    /**
     * Bloc de choix : titre, recherche, résultats, sélection.
     *
     * @param string $strKind 'share' ou 'validation'
     * @param string $strId identifiant du bloc
     * @param string $strTitle titre
     * @param string $strField identifiant du champ de recherche
     * @param string $strSearchQuery requête chiffrée de la recherche
     * @param string $strLoadQuery requête chiffrée de la sélection, '' si vide
     * @param string $strEmpty libellé d'une sélection vide
     */
    public static function form($strKind, $strId, $strTitle, $strField, $strSearchQuery, $strLoadQuery, $strEmpty)
    {
        $objSkin = skin::get();
        $strButton = "ploopi_{$strKind}_search_{$strId}";
        ?>
        <div class="p4-usel p4-usel-<?php echo $strKind; ?>">
            <a class="p4-usel-title" href="#" data-ploopi-switchdisplay="ploopi_<?php echo $strKind; ?>_<?php echo $strId; ?>">
                <?php echo $objSkin->icon($strKind == 'share' ? 'users' : 'user-check'); ?><span><?php echo $strTitle; ?></span>
            </a>
            <div id="ploopi_<?php echo $strKind; ?>_<?php echo $strId; ?>" class="p4-usel-body">
                <div class="p4-usel-search">
                    <label for="<?php echo $strField; ?>">Ajouter un groupe ou un utilisateur</label>
                    <div class="p4-usel-searchrow">
                        <input type="search" id="<?php echo $strField; ?>" class="text" placeholder="Nom, prénom ou identifiant…" autocomplete="off" data-ploopi-enterclick="<?php echo $strButton; ?>">
                        <button type="button" id="<?php echo $strButton; ?>" class="p4-usel-go" title="Rechercher" data-ploopi-search="div_<?php echo $strKind; ?>_search_result_<?php echo $strId; ?>" data-ploopi-query="<?php echo str::htmlentities($strSearchQuery); ?>" data-ploopi-field="<?php echo $strField; ?>" data-ploopi-param="ploopi_<?php echo $strKind; ?>_userfilter"><?php echo $objSkin->icon('search'); ?><span>Rechercher</span></button>
                    </div>
                </div>
                <div class="p4-usel-results" id="div_<?php echo $strKind; ?>_search_result_<?php echo $strId; ?>"></div>
                <div class="p4-usel-label">Sélection actuelle</div>
                <div class="p4-usel-chips" id="div_<?php echo $strKind; ?>_users_selected_<?php echo $strId; ?>"<?php if ($strLoadQuery !== '') echo ' data-ploopi-load="'.str::htmlentities($strLoadQuery).'"'; ?>>
                    <?php if ($strLoadQuery === '') echo '<span class="p4-usel-empty">'.str::htmlentities($strEmpty).'</span>'; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Sélection en pastilles (réponse de l'opération <kind>_select_user).
     *
     * @param string $strKind 'share' ou 'validation'
     * @param string $strId identifiant du bloc
     * @param array $arrGroups identifiants des groupes choisis
     * @param array $arrUsers identifiants des utilisateurs choisis
     * @param string $strEmpty libellé d'une sélection vide
     */
    public static function chips($strKind, $strId, $arrGroups, $arrUsers, $strEmpty)
    {
        $objSkin = skin::get();
        $strDiv = "div_{$strKind}_users_selected_{$strId}";
        $intNb = 0;

        $arrItems = array();
        foreach ($arrGroups as $intGroup)
        {
            $objGroup = new group();
            if ($objGroup->open($intGroup)) $arrItems[] = array('remove_group_id', $objGroup->fields['id'], $objGroup->fields['label'], 'users-group', 'Groupe');
        }
        foreach ($arrUsers as $intUser)
        {
            $objUser = new user();
            if ($objUser->open($intUser)) $arrItems[] = array('remove_user_id', $objUser->fields['id'], trim("{$objUser->fields['lastname']} {$objUser->fields['firstname']}"), 'user', 'Utilisateur');
        }

        foreach ($arrItems as $arrItem)
        {
            $strQuery = crypt::queryencode("ploopi_op={$strKind}_select_user&{$strKind}_id=".urlencode($strId)."&{$arrItem[0]}={$arrItem[1]}");
            ?>
            <a class="p4-usel-chip<?php if ($arrItem[3] == 'users-group') echo ' is-group'; ?>" href="#" title="<?php echo str::htmlentities("{$arrItem[4]} « {$arrItem[2]} » : cliquer pour retirer"); ?>" data-ploopi-todiv="<?php echo str::htmlentities($strDiv); ?>" data-ploopi-query="<?php echo str::htmlentities($strQuery); ?>">
                <?php echo $objSkin->icon($arrItem[3]); ?><span><?php echo str::htmlentities($arrItem[2]); ?></span><span class="p4-usel-x" aria-label="Retirer"><?php echo $objSkin->icon('x'); ?></span>
            </a>
            <?php
            $intNb++;
        }

        if (!$intNb) echo '<span class="p4-usel-empty">'.str::htmlentities($strEmpty).'</span>';
    }
}
