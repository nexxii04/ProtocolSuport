<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Protocol\Generic;

final class Changes
{
    private $changes = [];

    public function exists(int $pid): bool
    {
        return isset($this->changes[$pid]);
    }

    public function register(int $pid): void
    {
        array_push($this->changes, $pid);
    }
}
