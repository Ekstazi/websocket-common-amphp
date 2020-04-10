<?php

namespace ekstazi\websocket\common\amphp;

use Amp\ByteStream\ClosedException as BaseClosedException;
use Amp\ByteStream\IteratorStream;
use Amp\Iterator;
use Amp\Producer;
use Amp\Promise;
use Amp\Websocket\Client;
use Amp\Websocket\ClosedException;
use Amp\Websocket\Code;
use Amp\Websocket\Message;
use ekstazi\websocket\common\Reader as ReaderInterface;

final class Reader implements ReaderInterface
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
     * @inheritDoc
     */
    public function read(): Promise
    {
        return $this->stream->read();
    }

    private function createReader(Client $client): Iterator
    {
        return new Producer(function ($emit) use ($client) {
            try {
                /** @var Message $message */
                while ($message = yield $client->receive()) {
                    while (null !== $chunk = yield $message->read()) {
                        yield $emit($chunk);
                    }
                }
                if ($client->getCloseCode() != Code::NORMAL_CLOSE) {
                    throw new BaseClosedException($client->getCloseReason(), $client->getCloseCode());
                }
            } catch (ClosedException $exception) {
                throw new BaseClosedException($exception->getMessage(), $exception->getCode(), $exception);
            }
        });
    }
}
