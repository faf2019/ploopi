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
 * Événements métier et outbox ; aucune connexion réseau pendant une écriture métier.
 * @package ploopi
 * @subpackage events
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
abstract class events
{
    public static function enabled()
    {
        return in_array(strtolower((string) getenv('PLOOPI_EVENTS_ENABLED')), array('1', 'true', 'yes'), true);
    }

    /** Insère dans la transaction SQL courante ; l'appelant doit inclure son écriture métier dans db::transaction(). */
    public static function record($strType, array $arrData, array $arrContext = array())
    {
        if (!self::enabled()) return null;
        if (!preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z0-9_]+)*\.v[1-9][0-9]*$/D', $strType) || strlen($strType) > 190) throw new \InvalidArgumentException('Type d’événement invalide.');
        $strId = bin2hex(random_bytes(16));
        $strSource = getenv('PLOOPI_EVENTS_SOURCE') ?: 'urn:ploopi';
        if (!preg_match('/^[a-z][a-z0-9+.-]*:[^\s]+$/iD', $strSource)) throw new \InvalidArgumentException('Source CloudEvents invalide.');
        $arrEvent = array('specversion' => '1.0', 'id' => $strId, 'source' => $strSource, 'type' => $strType,
            'time' => gmdate('Y-m-d\TH:i:s\Z'), 'datacontenttype' => 'application/json', 'data' => $arrData,
            'ploopimodule' => (int) ($arrContext['module'] ?? ($_SESSION['ploopi']['moduleid'] ?? 0)),
            'ploopiworkspace' => (int) ($arrContext['workspace'] ?? ($_SESSION['ploopi']['workspaceid'] ?? 0)),
            'ploopiuser' => (int) ($arrContext['user'] ?? ($_SESSION['ploopi']['userid'] ?? 0)));
        $strPayload = json_encode($arrEvent, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (strlen($strPayload) > 1048576) throw new \InvalidArgumentException('Événement trop volumineux (1 Mio maximum).');
        $objDb = db::get();
        if (is_null($objDb->query("INSERT INTO ploopi_event_outbox (id, type, payload, created_at) VALUES ('{$strId}', '".$objDb->addslashes($strType)."', '".$objDb->addslashes($strPayload)."', ".time().')'))) throw new \RuntimeException('Enregistrement de l’événement impossible.');
        return $strId;
    }

    /** Fournisseurs locaux ; noms de modules issus des types installés, jamais du message reçu. */
    public static function listeners()
    {
        $arrListeners = array();
        $resTypes = db::get()->query('SELECT label FROM ploopi_module_type ORDER BY label');
        while ($arrType = db::get()->fetchrow($resTypes))
        {
            if (!preg_match('/^[a-z0-9_]+$/D', $arrType['label'])) continue;
            $strFile = './modules/'.$arrType['label'].'/include/events.php';
            if (!is_file($strFile)) continue;
            $arrModule = require $strFile;
            foreach ((array) $arrModule as $strType => $fnHandler)
            {
                if (!is_callable($fnHandler)) throw new \LogicException('Gestionnaire d’événement invalide.');
                $arrListeners[$strType][] = $fnHandler;
            }
        }
        return $arrListeners;
    }

    public static function retry($strId)
    {
        if (!preg_match('/^[a-f0-9]{32}$/D', $strId)) throw new \InvalidArgumentException('Identifiant invalide.');
        db::get()->query("UPDATE ploopi_event_outbox SET status = 'pending', attempts = 0, next_attempt_at = 0, locked_by = '', locked_until = 0, last_error = '' WHERE id = '{$strId}' AND status = 'failed'");
        return db::get()->affectedrows() === 1;
    }
}
