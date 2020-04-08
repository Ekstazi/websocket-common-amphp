<?php

namespace ekstazi\websocket\common\amphp;

use Amp\ByteStream\OutputStream;
use Amp\Promise;
use Amp\Websocket\Client;
use function Amp\call;

final class Writer implements OutputStream
{
    const MODE_BINARY = "binary";

    const MODE_TEXT = 'text';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $mode;

    public function __construct(Client $client, string $mode = self::MODE_BINARY)
    {
        $this->client = $client;
        $this->setMode($mode);
    }

    public function setMode(string $mode)
    {
        if (!\in_array($mode, [self::MODE_BINARY, self::MODE_TEXT])) {
            throw new \InvalidArgumentException('Unknown write mode');
        }
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $data
     * @return Promise
     * @throws
     */
    public function write(string $data): Promise
    {
        switch ($this->mode) {
            case self::MODE_TEXT:
                return $this->client->send($data);

            case self::MODE_BINARY:
                return $this->client->sendBinary($data);
        }
    }

    /**
     * @param string $finalData
     * @return Promise
     */
    public function end(string $finalData = ""): Promise
    {
        return call(function () use ($finalData) {
            if ($finalData) {
                yield $this->write($finalData);
            }
            return $this->client->close();
        });
    }
}
