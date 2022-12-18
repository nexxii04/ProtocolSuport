<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Protocol;

use pocketmine\network\mcpe\protocol\ClientBoundPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ProtocolInfo;

final class CurrentProtocol implements ProtocolInterface
{
    private $packetPool;

    public function __construct(PacketPool $packetPool)
    {
        $this->packetPool = $packetPool;
    }

    public function getVersion(): int
    {
        return ProtocolInfo::CURRENT_PROTOCOL;
    }

    public function isCurrent(): bool
    {
        return true;
    }

    public function getPacketPool(): PacketPool
    {
        return $this->packetPool;
    }

    public function hasChanges(ClientBoundPacket $packet): bool
    {
        return false;
    }
}
