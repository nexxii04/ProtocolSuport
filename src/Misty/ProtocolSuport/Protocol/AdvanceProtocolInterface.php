<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Protocol;

use pocketmine\network\mcpe\protocol\ClientBoundPacket;

interface AdvanceProtocolInterface extends ProtocolInterface
{
    public function getPacket(ClientBoundPacket $packet): ProtocolSuportPacket;
}
