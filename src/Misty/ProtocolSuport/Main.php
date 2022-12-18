<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport;

use Exception;
use Misty\ProtocolSuport\Network\MultiprotocolInterface;
use Misty\ProtocolSuport\Protocol\Factory;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\NetworkInterfaceRegisterEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\query\DedicatedQueryNetworkInterface;
use pocketmine\plugin\PluginBase;

final class Main extends PluginBase implements Listener
{
    private $interfaces = [];

    private $timer;

    public function onLoad(): void
    {
        Factory::getInstance();
    }

    public function onEnable(): void
    {
        $boostrap = dirname(__DIR__, 3) . '/vendor/autoload.php';
        if (! is_file($boostrap)) {
            throw new Exception('instale las dependencias de composer');
        }

        require_once $boostrap;

        $this->run();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onRegister(NetworkInterfaceRegisterEvent $event)
    {
        if (! $event->getInterface() instanceof MultiprotocolInterface && $event->getInterface() instanceof RakLibInterface) {
            $event->cancel();
        }

        if ($event->getInterface() instanceof DedicatedQueryNetworkInterface) {
            $event->cancel();
        }
    }

    public function onLogin(DataPacketReceiveEvent $event)
    {
        if (! $event->getPacket() instanceof LoginPacket) {
            return;
        }

        $protocol = Factory::getInstance()->get($event->getPacket()->protocol);
        if ($protocol->getVersion() !== (int) $event->getPacket()->protocol) {
            $event->cancel();
            $event->getOrigin()->disconnect('incompatible version', true);
            return;
        }

        $event->getOrigin()->setProtocol($protocol);
        $event->getPacket()->protocol = Factory::getInstance()->getCurrent()->getVersion();
    }

    private function run()
    {
        $server = $this->getServer();
        $network = $server->getNetwork();
        $ipV6 = $server->getConfigGroup()->getConfigBool('enable-ipv6', true);
        foreach ($this->interfaces as $interface) {
            $interface->shotdown();
            $network->unregisterInterface($interface);
        }

        $network->registerInterface(new MultiprotocolInterface($server, $server->getIp(), $server->getPort(), false));
        if ($ipV6) {
            $network->registerInterface(new MultiprotocolInterface($server, $server->getIpV6(), $server->getPortV6(), true));
        }
    }
}
