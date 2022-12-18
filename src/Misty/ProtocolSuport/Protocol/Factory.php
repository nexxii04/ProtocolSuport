<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Protocol;

use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\utils\Singletontrait;

final class Factory
{
    use Singletontrait;

    private static $current_protocol;

    private static $protocols = [];

    public function __construct()
    {
        self::$current_protocol = new CurrentProtocol(PacketPool::getInstance());

        $this->registerProtocols();
    }

    public static function get($version): ProtocolInterface
    {
        return self::$protocols[$version] ?? self::$current_protocol;
    }

    public function register(ProtocolInterface $protocol): void
    {
        self::$protocols[$protocol->getVersion()] = $protocol;
    }

    public function getCurrent()
    {
        return self::$current_protocol;
    }

    public function setCurrent(ProtocolSuport $protocol): void
    {
        self::$current_protocol = $protocols;
    }

    private function registerProtocols()
    {
    }
}
