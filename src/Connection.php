<?php

namespace ekstazi\websocket\common\amphp;

use Amp\Websocket\Client;
use ekstazi\websocket\common\Connection as ConnectionInterface;
use ekstazi\websocket\common\internal\Connection as BaseConnection;

final class Connection extends BaseConnection implements ConnectionInterface
{

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
            new Writer($client, $defaultMode)
        );
    }
}
