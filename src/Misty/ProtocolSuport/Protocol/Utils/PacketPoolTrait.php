<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Protocol\Utils;

use Misty\ProtocolSuport\Protocol\Generic\Changes;

trait PacketPoolTrait
{
    public function registerChanges(Changes $changes): void
    {
        if (! isset($this->changes) || ! is_array($this->changes) || empty($this->changes)) {
            return;
        }

        foreach ($this->changes as $packet) {
            $this->registerPacket($packet);
            $changes->register($packet->pid());
        }
    }
}
