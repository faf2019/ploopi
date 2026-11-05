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

/**
 * Clés API : secret aléatoire affiché une fois, empreinte SHA-256 seule en base.
 * @package ploopi
 * @subpackage api
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
abstract class api_key
{
    /** Émet une clé liée à un utilisateur et un seul espace ; ne donne aucun droit supplémentaire. */
    public static function issue($intUser, $intWorkspace, $strLabel, $arrScopes, $intDays = 90)
    {
        $objUser = new user();
        if (!$objUser->open((int) $intUser) || !empty($objUser->fields['disabled'])) throw new \InvalidArgumentException('Utilisateur indisponible.');
        $arrWorkspaces = $objUser->getworkspaces();
        if (empty($arrWorkspaces[(int) $intWorkspace]['adminlevel'])) throw new \InvalidArgumentException('Utilisateur non rattaché à cet espace.');
        $strLabel = trim($strLabel);
        if ($strLabel === '' || mb_strlen($strLabel) > 100 || !$arrScopes || array_diff($arrScopes, array_keys(api::scopes()))) throw new \InvalidArgumentException('Libellé ou permissions invalides.');
        if ($intDays < 1 || $intDays > 365) throw new \InvalidArgumentException('Durée comprise entre 1 et 365 jours.');
        $strToken = 'plp_'.bin2hex(random_bytes(32));
        $objKey = new data_object('ploopi_api_key');
        $objKey->init_description();
        $objKey->fields = array_merge($objKey->fields, array(
            'label' => $strLabel, 'id_user' => (int) $intUser, 'id_workspace' => (int) $intWorkspace,
            'token_hash' => hash('sha256', $strToken), 'prefix' => substr($strToken, 0, 12),
            'scopes' => implode(' ', array_unique($arrScopes)), 'created_at' => time(), 'expires_at' => time() + $intDays * 86400
        ));
        if ($objKey->save() === false) throw new \RuntimeException('Impossible de créer la clé.');
        return array('id' => (int) $objKey->fields['id'], 'token' => $strToken);
    }

    /** Révocation immédiate, sans conservation du secret. */
    public static function revoke($intId)
    {
        return !is_null(db::get()->query('UPDATE ploopi_api_key SET revoked_at = '.time().' WHERE id = '.(int) $intId.' AND revoked_at = 0'));
    }
}
