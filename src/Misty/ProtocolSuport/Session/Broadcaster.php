<?php

declare(strict_types=1);

/**
 * This file is part of Misty Network.
 * @author  NexD4v
 * @link    https:://github.com/NexD4v
 */

namespace Misty\ProtocolSuport\Session;

use Misty\ProtocolSuport\Protocol\AdvanceProtocolInterface;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\PacketBroadcaster;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\Singletontrait;
use React\Promise\Promise;
use function spl_object_id;

class Broadcaster implements PacketBroadcaster
{
    use Singletontrait;

    protected $server;

    public function __construct(Server $server)
    {
        self::setInstance($this);
        $this->server = $server;
    }

    public function broadcastPackets(array $recipients, array $packets): void
    {
        $buffers = [];
        $compressors = [];
        $targetMap = [];
        foreach ($recipients as $recipient) {
            $serializerContext = $recipient->getPacketSerializerContext();
            $bufferId = spl_object_id($serializerContext);
            if (! isset($buffers[$bufferId])) {
                $buffers[$bufferId] = PacketBatch::fromPackets($serializerContext, ...$packets);
            }

            //TODO: different compressors might be compatible, it might not be necessary to split them up by object
            $compressor = $recipient->getCompressor();
            $compressors[spl_object_id($compressor)] = $compressor;

            $targetMap[$bufferId][spl_object_id($compressor)][] = $recipient;
        }

        foreach ($targetMap as $bufferId => $compressorMap) {
            $buffer = $buffers[$bufferId];
            foreach ($compressorMap as $compressorId => $compressorTargets) {
                $compressor = $compressors[$compressorId];
                if (! $compressor->willCompress($buffer->getBuffer())) {
                    foreach ($compressorTargets as $target) {
                        foreach ($packets as $pk) {
                            $target->addToSendBuffer($pk);
                        }
                    }
                } else {
                    $this->readAndSendBatch($buffer, $compressor, $compressorTargets);
                }
            }
        }
    }

    protected function readAndSendBatch(PacketBatch $batch, Compressor $compressor, array $targets): PromiseInterface
    {
        return new Promise(function () {
            $compressedMap = [];
            foreach ($targets as $target) {
                if (isset($compressedMap[$target->getProtocol()->getVersion()])) {
                    $target->queueCompressed($compressedMap[$target->getProtocol()->getVersion()]);
                    continue;
                }

                if ($target->getProtocol()->isCurrent() || ! $target->getProtocol()->hasChanges()) {
                    $promise = $this->server->prepareBatch($batch, $compressor);
                    $compressorMap[$target->getProtocol()->getVersion()] = $promise;
                    $target->queueCompressed($promise);
                    continue;
                }

                $packets = [];
                foreach ($bath->getPackets($target->getProtocol()->getPacketPool(), $target->getPacketSerializerContext(), 500) as [$packet, $buffer]) {
                    $timings = Timings::getDecodeDataPacketTimimgs($packet);
                    $timings->startTiming();
                    $stream = PacketSerializer::decoder($buffer, 0, $target->getPacketSerializerContext());
                    $packet->decode($stream);

                    if ($target->getProtocol()->hasChanges($packet) && $target->getProtocol() instanceof AdvanceProtocolInterface) {
                        $newPacket = $target->getProtocol()->getPacket($packet);
                        $newPacket->formPacket($packet);
                        $packets[] = $newPacket;
                    } else {
                        $packets[] = $packet;
                    }

                    $timings->stopTiming();
                }

                $promise = $this->server->prepareBatch(PacketBatch::formPackets($target->getPacketSerializerContext(), $packets), $compresor);
                $compressedMap[$target->getProtocol()->getVersion()] = $promise;
                $target->queueCompressed($promise);
            }
        });
    }
}
