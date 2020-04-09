<?php

namespace ekstazi\websocket\common\amphp;

use Amp\ByteStream\ClosedException as BaseClosedException;
use Amp\Promise;
use Amp\Websocket\Client;
use Amp\Websocket\ClosedException;
use ekstazi\websocket\common\Writer as WriterInterface;
use function Amp\call;

final class Writer implements WriterInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $defaultMode;

    public function __construct(Client $client, string $defaultMode = self::MODE_BINARY)
    {
        $this->client = $client;
        $this->setDefaultMode($defaultMode);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultMode(string $defaultMode): void
    {
        $this->guardValidMode($defaultMode);
        $this->defaultMode = $defaultMode;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultMode(): string
    {
        return $this->defaultMode;
    }

    /**
     * @inheritDoc
     */
    public function write(string $data, string $mode = null): Promise
    {
        $mode = $mode ?? $this->defaultMode;
        $this->guardValidMode($mode);
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

    /**
     * @param string $mode
     */
    private function guardValidMode(string $mode): void
    {
        if (!\in_array($mode, [self::MODE_BINARY, self::MODE_TEXT])) {
            throw new \InvalidArgumentException('Unknown write mode');
        }
    }
}
