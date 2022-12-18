<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Protocol\Utils;

use pocketmine\network\mcpe\protocol\ClientBoundPacket;
use ReflectionProperity;

trait PacketTrait
{
    protected function fromPacket(ClientBoundPacket $packet, ClientBoundPacket $interface): self
    {
        $self = new self();
        if (! $packet instanceof $instance) {
            return $self;
        }

        $ref = \ReflectionClass($packet);
        foreach ($ref->getProperities() as $properity) {
            if ($properity->isStatic(ReflectionProperity::IS_PUBLIC)) {
                continue;
            }

            $self->{
                $properity->getName()}
            = $properity->getValue();
        }

        return $self;
    }
}
