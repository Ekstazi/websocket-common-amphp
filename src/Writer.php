<?php

namespace ekstazi\websocket\common\amphp;

use Amp\ByteStream\ClosedException as BaseClosedException;
use Amp\Promise;
use Amp\Websocket\Client;
use Amp\Websocket\ClosedException;
use ekstazi\websocket\common\internal\SetModeTrait;
use ekstazi\websocket\common\Writer as WriterInterface;
use function Amp\call;

final class Writer implements WriterInterface
{
    use SetModeTrait;
    /**
     * @var Client
     */
    private $client;


    public function __construct(Client $client, string $defaultMode = self::MODE_BINARY)
    {
        $this->client = $client;
        $this->setDefaultMode($defaultMode);
    }

    /**
     * @inheritDoc
     */
    public function write(string $data, string $mode = null): Promise
    {
        $mode = $mode ?? $this->defaultMode;
        $this->guardValidMode($mode);
        if (!$this->client->isConnected()) {
            throw new BaseClosedException("Connection closed with reason: " . $this->client->getCloseReason(), $this->client->getCloseCode());
        }
        return call(function () use ($data, $mode) {
            try {
                switch ($mode) {
                    case self::MODE_TEXT:
                        return yield $this->client->send($data);

                    case self::MODE_BINARY:
                        return yield $this->client->sendBinary($data);
                }
            } catch (ClosedException $exception) {
                throw new BaseClosedException($exception->getMessage(), $exception->getCode(), $exception);
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function end(string $finalData = "", string $mode = null): Promise
    {
        return call(function () use ($finalData, $mode) {
            if ($finalData) {
                yield $this->write($finalData, $mode);
            }
            return $this->client->close();
        });
    }
}
