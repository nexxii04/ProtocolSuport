<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Session;

use Misty\ProtocolSuport\Protocol\ProtocolInterface;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientBoundPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\PacketHandlingException;
use pocketmine\timings\Timings;

class Session extends NetworkSession
{
    
    final protected $protocol;
    
    public function getProtocol(): ProtocolInterface
    {
        return $this->protocol;
    }

    public function setProtocol(ProtocolInterface $protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @throws PacketHandlingException
     */
    public function handleEncoded(string $payload): void
    {
        if (! $this->connected) {
            return;
        }

        if ($this->cipher !== null) {
            Timings::$playerNetworkReceiveDecrypt->startTiming();
            try {
                $payload = $this->cipher->decrypt($payload);
            } catch (DecryptionException $e) {
                $this->logger->debug('Encrypted packet: ' . base64_encode($payload));
                throw PacketHandlingException::wrap($e, 'Packet decryption error');
            } finally {
                Timings::$playerNetworkReceiveDecrypt->stopTiming();
            }
        }

        if ($this->enableCompression) {
            Timings::$playerNetworkReceiveDecompress->startTiming();
            try {
                $decompressed = $this->compressor->decompress($payload);
            } catch (DecompressionException $e) {
                $this->logger->debug('Failed to decompress packet: ' . base64_encode($payload));
                throw PacketHandlingException::wrap($e, 'Compressed packet batch decode error');
            } finally {
                Timings::$playerNetworkReceiveDecompress->stopTiming();
            }
        } else {
            $decompressed = $payload;
        }

        try {
            foreach ((new PacketBatch($decompressed))->getPackets($this->protocol->getPacketPool(), $this->packetSerializerContext, 500) as [$packet, $buffer]) {
                if ($packet === null) {
                    $this->logger->debug('Unknown packet: ' . base64_encode($buffer));
                    throw new PacketHandlingException('Unknown packet received');
                }
                try {
                    $this->handleDataPacket($packet, $buffer);
                } catch (PacketHandlingException $e) {
                    $this->logger->debug($packet->getName() . ': ' . base64_encode($buffer));
                    throw PacketHandlingException::wrap($e, 'Error processing ' . $packet->getName());
                }
            }
        } catch (PacketDecodeException $e) {
            $this->logger->logException($e);
            throw PacketHandlingException::wrap($e, 'Packet batch decode error');
        }
    }

    /**
     * @internal
     */
    public function addToSendBuffer(ClientBoundPacket $packet): void
    {
        if (! $this->protocol->hasChanges($packet)) {
            parent::addToSendBuffer($packet);
            return;
        }

        if (! $this->protocol instanceof AdvanceProtocolInterface) {
            parent::addToSendBuffer($packet);
            return;
        }

        $newPacket = $this->protocol->getPacket($packet);
        parent::addToSendBuffer($newPacket->fromPacket($packet));
    }
}
