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

use PhpAmqpLib\Connection\AMQPConnectionConfig;
use PhpAmqpLib\Connection\AMQPConnectionFactory;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * Transport AMQP 0-9-1 : files quorum durables, confirmations et messages non routables refusés.
 * @package ploopi
 * @subpackage events
 * @copyright Ovensia
 * @license GNU General Public License (GPL)
 * @author Ovensia
 */
class rabbitmq
{
    private $objConnection;
    private $objChannel;
    private $booReturned = false;
    private $booNacked = false;
    private $strExchange;
    private $strQueue;

    public function __construct($strQueue = null, array $arrBindings = array('#'))
    {
        $this->strExchange = self::name(getenv('PLOOPI_RABBITMQ_EXCHANGE') ?: 'ploopi.events');
        $this->strQueue = self::name($strQueue ?? (getenv('PLOOPI_RABBITMQ_QUEUE') ?: 'ploopi.events'));
        $strUser = getenv('PLOOPI_RABBITMQ_USER');
        $strPasswordFile = getenv('PLOOPI_RABBITMQ_PASSWORD_FILE');
        if ($strPasswordFile && !is_readable($strPasswordFile)) throw new \RuntimeException('Fichier de secret RabbitMQ illisible.');
        $strPassword = $strPasswordFile ? rtrim(file_get_contents($strPasswordFile), "\r\n") : getenv('PLOOPI_RABBITMQ_PASSWORD');
        if (!$strUser || $strPassword === false || $strPassword === '') throw new \RuntimeException('Identifiants RabbitMQ non configurés.');
        $objConfig = new AMQPConnectionConfig();
        $objConfig->setHost(getenv('PLOOPI_RABBITMQ_HOST') ?: 'rabbitmq');
        $intPort = (int) (getenv('PLOOPI_RABBITMQ_PORT') ?: 5672);
        if ($intPort < 1 || $intPort > 65535) throw new \InvalidArgumentException('Port RabbitMQ invalide.');
        $objConfig->setPort($intPort);
        $objConfig->setUser($strUser);
        $objConfig->setPassword($strPassword);
        $objConfig->setVhost(getenv('PLOOPI_RABBITMQ_VHOST') ?: '/');
        $objConfig->setConnectionTimeout(3.0);
        $objConfig->setReadTimeout(35.0);
        $objConfig->setWriteTimeout(10.0);
        $objConfig->setChannelRPCTimeout(10.0);
        $objConfig->setHeartbeat(15);
        $booTls = in_array(strtolower((string) getenv('PLOOPI_RABBITMQ_TLS')), array('1', 'true', 'yes'), true);
        $objConfig->setIsSecure($booTls);
        if ($booTls)
        {
            $objConfig->setSslVerify(true);
            $objConfig->setSslVerifyName(true);
            if (getenv('PLOOPI_RABBITMQ_CA_FILE')) $objConfig->setSslCaCert(getenv('PLOOPI_RABBITMQ_CA_FILE'));
        }
        $this->objConnection = AMQPConnectionFactory::create($objConfig);
        try
        {
            $this->objChannel = $this->objConnection->channel();
            $this->topology($arrBindings);
            $this->objChannel->confirm_select();
            $this->objChannel->set_return_listener(function () { $this->booReturned = true; });
            $this->objChannel->set_nack_handler(function () { $this->booNacked = true; });
        }
        catch (\Throwable $objError) { $this->close(); throw $objError; }
    }

    public static function name($strName)
    {
        if (!is_string($strName) || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,119}$/D', $strName) || strpos($strName, 'amq.') === 0) throw new \InvalidArgumentException('Nom AMQP invalide.');
        return $strName;
    }

    private function topology(array $arrBindings)
    {
        $strExchange = $this->strExchange;
        $strQueue = $this->strQueue;
        $this->objChannel->exchange_declare($strExchange, 'topic', false, true, false);
        $this->objChannel->exchange_declare($strExchange.'.dead', 'direct', false, true, false);
        $this->objChannel->exchange_declare($strExchange.'.retry', 'direct', false, true, false);
        $this->objChannel->queue_declare($strQueue.'.dead', false, true, false, false, false, new AMQPTable(array('x-queue-type' => 'quorum')));
        $this->objChannel->queue_bind($strQueue.'.dead', $strExchange.'.dead', $strQueue.'.dead');
        $this->objChannel->queue_declare($strQueue, false, true, false, false, false, new AMQPTable(array(
            'x-queue-type' => 'quorum', 'x-dead-letter-exchange' => $strExchange.'.dead', 'x-dead-letter-routing-key' => $strQueue.'.dead',
            'x-dead-letter-strategy' => 'at-least-once', 'x-overflow' => 'reject-publish')));
        foreach ($arrBindings as $strBinding)
        {
            if (!is_string($strBinding) || !preg_match('/^[a-z0-9_.*#-]{1,190}$/D', $strBinding)) throw new \InvalidArgumentException('Filtre AMQP invalide.');
            $this->objChannel->queue_bind($strQueue, $strExchange, $strBinding);
        }
        // File de reprise dédiée au consommateur ; la DLX interne ramène vers sa file, sans diffusion aux autres abonnés.
        $this->objChannel->queue_declare($strQueue.'.retry', false, true, false, false, false, new AMQPTable(array(
            'x-queue-type' => 'quorum', 'x-message-ttl' => 5000, 'x-dead-letter-exchange' => '', 'x-dead-letter-routing-key' => $strQueue,
            'x-dead-letter-strategy' => 'at-least-once', 'x-overflow' => 'reject-publish')));
        $this->objChannel->queue_bind($strQueue.'.retry', $strExchange.'.retry', $strQueue.'.retry');
    }

    /** Le succès exige un ACK du broker ET l'absence de basic.return/nack. */
    public function publish($strPayload, $strType, $strId, $strExchange = null, $strRoutingKey = null, array $arrHeaders = array())
    {
        $this->booReturned = false;
        $this->booNacked = false;
        $objMessage = new AMQPMessage($strPayload, array('content_type' => 'application/cloudevents+json', 'delivery_mode' => 2,
            'message_id' => $strId, 'type' => $strType, 'timestamp' => time(), 'application_headers' => new AMQPTable($arrHeaders)));
        $this->objChannel->basic_publish($objMessage, $strExchange ?? $this->strExchange, $strRoutingKey ?? $strType, true);
        $this->objChannel->wait_for_pending_acks_returns(5.0);
        if ($this->booReturned || $this->booNacked) throw new \RuntimeException('RabbitMQ n’a pas confirmé le routage du message.');
    }

    public function get()
    {
        return $this->objChannel->basic_get($this->strQueue, false);
    }

    public function acknowledge(AMQPMessage $objMessage)
    {
        $this->objChannel->basic_ack($objMessage->getDeliveryTag());
    }

    public function reject(AMQPMessage $objMessage)
    {
        $this->objChannel->basic_reject($objMessage->getDeliveryTag(), false);
    }

    public function retry(AMQPMessage $objMessage, array $arrEvent, $intAttempt)
    {
        $this->publish($objMessage->getBody(), $arrEvent['type'], $arrEvent['id'], $this->strExchange.'.retry', $this->strQueue.'.retry', array('x-ploopi-attempt' => $intAttempt));
        $this->acknowledge($objMessage);
    }

    public function queue()
    {
        return $this->strQueue;
    }

    public function channel()
    {
        return $this->objChannel;
    }

    public function close()
    {
        try { if ($this->objChannel && $this->objChannel->is_open()) $this->objChannel->close(); } catch (\Throwable $objError) { }
        try { if ($this->objConnection && $this->objConnection->isConnected()) $this->objConnection->close(); } catch (\Throwable $objError) { }
        $this->objChannel = null;
        $this->objConnection = null;
    }

    public function __destruct() { $this->close(); }
}
