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
 * Traitements locaux : ACK après commit, inbox atomique et reprises bornées vers une file d'échec.
 * @package ploopi
 * @subpackage events
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
class event_consumer
{
    private $objTransport;
    private $arrListeners;

    public function __construct(rabbitmq $objTransport, array $arrListeners)
    {
        $this->objTransport = $objTransport;
        $this->arrListeners = $arrListeners;
    }

    /** Un message au plus ; false si la file est vide. */
    public function once()
    {
        if (db::get()->in_transaction()) throw new \LogicException('Consommation interdite dans une transaction englobante.');
        $objMessage = $this->objTransport->get();
        if (!$objMessage) return false;
        try
        {
            if (strlen($objMessage->getBody()) > 1048576) throw new \InvalidArgumentException('Taille invalide.');
            $arrEvent = json_decode($objMessage->getBody(), true, 64, JSON_THROW_ON_ERROR);
            if (!is_array($arrEvent) || ($arrEvent['specversion'] ?? '') !== '1.0' || !is_string($arrEvent['id'] ?? null)
                || !preg_match('/^[A-Za-z0-9._:-]{1,128}$/D', $arrEvent['id']) || !is_string($arrEvent['type'] ?? null)
                || !preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z0-9_]+)*\.v[1-9][0-9]*$/D', $arrEvent['type']) || strlen($arrEvent['type']) > 190
                || !is_string($arrEvent['source'] ?? null) || !preg_match('/^[a-z][a-z0-9+.-]*:[^\s]+$/iD', $arrEvent['source']) || !is_array($arrEvent['data'] ?? null)) throw new \InvalidArgumentException('CloudEvent invalide.');
        }
        catch (\Throwable $objError)
        {
            $this->objTransport->reject($objMessage);
            return true;
        }
        $arrHandlers = array_merge($this->arrListeners[$arrEvent['type']] ?? array(), $this->arrListeners['*'] ?? array());
        if (!$arrHandlers)
        {
            $this->objTransport->reject($objMessage);
            return true;
        }
        try
        {
            db::get()->transaction(function () use ($arrEvent, $arrHandlers)
            {
                $objDb = db::get();
                $strConsumer = $objDb->addslashes($this->objTransport->queue());
                $strId = $objDb->addslashes($arrEvent['id']);
                $strSourceHash = hash('sha256', $arrEvent['source']);
                $strType = $objDb->addslashes($arrEvent['type']);
                $resInsert = $objDb->query("INSERT IGNORE INTO ploopi_event_inbox (consumer, source_hash, event_id, type, processed_at) VALUES ('{$strConsumer}', '{$strSourceHash}', '{$strId}', '{$strType}', ".time().')');
                if (is_null($resInsert)) throw new \RuntimeException('Écriture de l’inbox impossible.');
                if ($objDb->affectedrows() === 0) return; // livraison déjà validée, y compris par un autre worker
                foreach ($arrHandlers as $fnHandler) $fnHandler($arrEvent);
            });
        }
        catch (\Throwable $objError)
        {
            $arrHeaders = $objMessage->has('application_headers') ? $objMessage->get('application_headers')->getNativeData() : array();
            $intAttempt = max(0, (int) ($arrHeaders['x-ploopi-attempt'] ?? 0));
            if ($intAttempt >= 3) $this->objTransport->reject($objMessage);
            else $this->objTransport->retry($objMessage, $arrEvent, $intAttempt + 1);
            return true;
        }
        // Un ACK perdu déclenche une relivraison ; l'inbox évite alors de refaire les écritures SQL.
        $this->objTransport->acknowledge($objMessage);
        return true;
    }
}
