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

    public function __construct(ReaderInterface $reader, WriterInterface $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
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
     * @param string $mode
     * @return static
     */
    public static function create(Client $client, string $mode = Writer::MODE_BINARY): self
    {
        return new static(
            new Reader($client),
            new Writer($client, $mode)
        );
    }

    public function getId(): int
    {
        // TODO: Implement getId() method.
    }

    public function getRemoteAddress(): string
    {
        // TODO: Implement getRemoteAddress() method.
    }
}
