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
 * Relais outbox : bail court, confirmations AMQP, reprises exponentielles et échecs visibles.
 * @package ploopi
 * @subpackage events
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
class event_publisher
{
    private $strOwner;
    private $objTransport = null;

    public function __construct() { $this->strOwner = bin2hex(random_bytes(16)); }

    private function claim()
    {
        return db::get()->transaction(function ()
        {
            $objDb = db::get();
            $intNow = time();
            $resRow = $objDb->query("SELECT * FROM ploopi_event_outbox WHERE status IN ('pending', 'publishing') AND next_attempt_at <= {$intNow} AND locked_until <= {$intNow} ORDER BY created_at, id LIMIT 1 FOR UPDATE");
            if (!$resRow) throw new \RuntimeException('Lecture de l’outbox impossible.');
            $arrRow = $objDb->fetchrow($resRow);
            if (!$arrRow) return null;
            if ((int) $arrRow['attempts'] >= 10)
            {
                $objDb->query("UPDATE ploopi_event_outbox SET status = 'failed', locked_by = '', locked_until = 0, last_error = 'Dix tentatives atteintes.' WHERE id = '{$arrRow['id']}'");
                return array('exhausted' => true);
            }
            if (is_null($objDb->query("UPDATE ploopi_event_outbox SET status = 'publishing', attempts = attempts + 1, locked_by = '{$this->strOwner}', locked_until = ".($intNow + 120)." WHERE id = '{$arrRow['id']}'"))) throw new \RuntimeException('Réservation de l’événement impossible.');
            $arrRow['attempts']++;
            return $arrRow;
        });
    }

    /** Traite un lot fini ; aucune transaction SQL n'est gardée ouverte pendant l'appel au broker. */
    public function flush($intLimit = 100)
    {
        if (db::get()->in_transaction()) throw new \LogicException('Publication interdite dans une transaction métier.');
        if ($intLimit < 1 || $intLimit > 1000) throw new \InvalidArgumentException('Taille de lot invalide.');
        $arrStats = array('published' => 0, 'failed' => 0);
        try
        {
            for ($intIndex = 0; $intIndex < $intLimit; $intIndex++)
            {
                $arrRow = $this->claim();
                if ($arrRow === null) break;
                if (isset($arrRow['exhausted'])) { $arrStats['failed']++; continue; }
                try
                {
                    if (!$this->objTransport) $this->objTransport = new rabbitmq();
                    $this->objTransport->publish($arrRow['payload'], $arrRow['type'], $arrRow['id']);
                    $resResult = db::get()->query("UPDATE ploopi_event_outbox SET status = 'published', published_at = ".time().", locked_by = '', locked_until = 0, last_error = '' WHERE id = '{$arrRow['id']}' AND locked_by = '{$this->strOwner}' AND status = 'publishing'");
                    if (is_null($resResult) || db::get()->affectedrows() !== 1) throw new \RuntimeException('Confirmation SQL de publication impossible.');
                    $arrStats['published']++;
                }
                catch (\Throwable $objError)
                {
                    if ($this->objTransport) $this->objTransport->close();
                    $this->objTransport = null;
                    $strStatus = $arrRow['attempts'] >= 10 ? 'failed' : 'pending';
                    $intNext = time() + min(3600, 5 * (2 ** min(10, $arrRow['attempts'] - 1)));
                    // Les exceptions AMQP peuvent contenir des informations de connexion : ne conserver que leur classe.
                    $strError = db::get()->addslashes('Publication non confirmée ('.get_class($objError).').');
                    if (is_null(db::get()->query("UPDATE ploopi_event_outbox SET status = '{$strStatus}', next_attempt_at = {$intNext}, locked_by = '', locked_until = 0, last_error = '{$strError}' WHERE id = '{$arrRow['id']}' AND locked_by = '{$this->strOwner}'"))) throw new \RuntimeException('Conservation de l’échec impossible.');
                    $arrStats['failed']++;
                }
            }
        }
        finally
        {
            if ($this->objTransport) $this->objTransport->close();
            $this->objTransport = null;
        }
        return $arrStats;
    }
}
