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

interface ProtocolInterface
{
    public function getVersion(): int;

    public function isCurrent(): bool;

    public function hasChanges(ClientBoundPacket $packet): bool;

    public function getPacketPool(): PacketPool;
}
