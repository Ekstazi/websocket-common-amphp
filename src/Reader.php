<?php

namespace ekstazi\websocket\common\amphp;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\IteratorStream;
use Amp\Iterator;
use Amp\Producer;
use Amp\Promise;
use Amp\Websocket\Client;
use Amp\Websocket\Message;

final class Reader implements InputStream
{

    /**
     * @var IteratorStream
     */
    private $stream;

    public function __construct(Client $client)
    {
        $this->stream = new IteratorStream($this->createReader($client));
    }

    /**
     * @return Promise
     */
    public function read(): Promise
    {
        return $this->stream->read();
    }

    private function createReader(Client $client): Iterator
    {
        return new Producer(function ($emit) use ($client) {
            /** @var Message $message */
            while ($message = yield $client->receive()) {
                while (null !== $chunk = yield $message->read()) {
                    yield $emit($chunk);
                }
            }
        });
    }
}
