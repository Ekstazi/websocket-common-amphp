# websocket-common-amphp
`ekstazi/websocket-common-amphp` is `amphp/websocket` adapter to amphp streams. This package is internally used in `ekstazi/websocket-stream-client-amphp`
# Installation
This package can be installed as a Composer dependency.

`composer require ekstazi/websocket-common-amphp`
# Requirements
PHP 7.2+
# Usage

```php
<?php

use ekstazi\websocket\common\amphp\Reader;
use ekstazi\websocket\common\amphp\Connection;
use ekstazi\websocket\common\amphp\Writer;

/** @var \Amp\Websocket\Client $client */
$stream = new Connection(new Reader($client), new Writer($client));
$stream->setDefaultMode(Writer::MODE_TEXT);
// alternative way to create stream
// $stream = Connection::create($client, Writer::MODE_TEXT);
yield $stream->read();
yield $stream->write('test');
```
