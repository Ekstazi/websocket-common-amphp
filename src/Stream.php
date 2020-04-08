<?php

namespace ekstazi\websocket\common\amphp;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\OutputStream;
use Amp\Promise;
use Amp\Websocket\Client;

final class Stream implements InputStream, OutputStream
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader, Writer $writer)
    {
        $this->reader = $reader;
        $this->writer = $writer;
    }

    public function setMode(string $mode)
    {
        $this->writer->setMode($mode);
    }

    public function getMode(): string
    {
        return $this->writer->getMode();
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
    public function write(string $data): Promise
    {
        return $this->writer->write($data);
    }

    /**
     * @inheritDoc
     */
    public function end(string $finalData = ""): Promise
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
}
