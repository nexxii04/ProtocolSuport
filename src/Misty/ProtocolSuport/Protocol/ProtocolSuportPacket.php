<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Protocol;

use pocketmine\network\mcpe\protocol\ClientBoundPacket;

interface ProtocolSuportPacket
{
    public function fromPacket(ClientBoundPacket $packet): ClientBoundPacket;
}
