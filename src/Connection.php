<?php

namespace ekstazi\websocket\common\amphp;

use Amp\Promise;
use Amp\Websocket\Client;
use ekstazi\websocket\common\Connection as ConnectionInterface;
use ekstazi\websocket\common\Reader as ReaderInterface;
use ekstazi\websocket\common\Writer as WriterInterface;

final class Connection implements ConnectionInterface
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var \Amp\Socket\SocketAddress
     */
    private $remoteAddress;

    /**
     * @var int
     */
    private $id;

    public function __construct(ReaderInterface $reader, WriterInterface $writer, Client $client)
    {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->id = $client->getId();
        $this->remoteAddress = $client->getRemoteAddress();
    }

    public function setDefaultMode(string $defaultMode): void
    {
        $this->writer->setDefaultMode($defaultMode);
    }

    public function getDefaultMode(): string
    {
        return $this->writer->getDefaultMode();
    }
    /**
     * @inheritDoc
     */
    public function read(): Promise
    {
        return $this->reader->read();
    }

    /**
     * @inheritDoc
     */
    public function write(string $data, string $mode = null): Promise
    {
        return $this->writer->write($data);
    }

    /**
     * @inheritDoc
     */
    public function end(string $finalData = "", string $mode = null): Promise
    {
        return $this->writer->end($finalData);
    }

    /**
     * Create stream from client.
     * @param Client $client
     * @param string $defaultMode
     * @return static
     */
    public static function create(Client $client, string $defaultMode = Writer::MODE_BINARY): self
    {
        return new static(
            new Reader($client),
            new Writer($client, $defaultMode),
            $client
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRemoteAddress(): string
    {
        return $this->remoteAddress;
    }
}
