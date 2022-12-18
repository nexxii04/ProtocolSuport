<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Network;

use Misty\ProtocolSuport\Protocol\Factory;
use Misty\ProtocolSuport\Session\Broadcaster;
use Misty\ProtocolSuport\Session\Session;
use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\mcpe\raklib\RakLibPacketSender;

class MultiprotocolInterface extends RakLibInterface
{
    public function start(): void
    {
        new Broadcaster($this->server);
        parent::start();
    }

    public function onClientConnect(int $sessionId, string $address, int $port, int $clientID): void
    {
        $session = new Session(
            $this->server,
            $this->network->getSessionManager(),
            PacketPool::getInstance(),
            new RakLibPacketSender($sessionId, $this),
            Broadcaster::getInstance(),
            ZlibCompressor::getInstance(), //TODO: this shouldn't be hardcoded, but we might need the RakNet protocol version to select it
            $address,
            $port
        );

        $session->setProtocol(Factory::getInstance()->getCurrent());
        $this->sessions[$sessionId] = $session;
    }
}
