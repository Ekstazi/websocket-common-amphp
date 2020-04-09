<?php

namespace ekstazi\websocket\common\amphp\test;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\OutputStream;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Socket\SocketAddress;
use Amp\Success;
use Amp\Websocket\Client;
use ekstazi\websocket\common\amphp\Connection;
use ekstazi\websocket\common\Reader;
use ekstazi\websocket\common\Writer;

class ConnectionTest extends AsyncTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public function testCreate()
    {
        $client = $this->createClient();
        $stream = Connection::create($client, Writer::MODE_BINARY);
        self::assertInstanceOf(Connection::class, $stream);
        self::assertEquals(Writer::MODE_BINARY, $stream->getDefaultMode());
    }

    public function testConstruct()
    {
        $reader = $this->createStub(Reader::class);
        $writer = $this->createStub(Writer::class);
        $client = $this->createClient();
        $stream = new Connection($reader, $writer, $client);

        self::assertInstanceOf(InputStream::class, $stream);
        self::assertInstanceOf(OutputStream::class, $stream);
    }

    public function testEnd()
    {
        $reader = $this->createStub(Reader::class);

        $writer = $this->createMock(Writer::class);
        $writer->expects(self::once())
            ->method('end')
            ->with('test')
            ->willReturn(new Success());
        $client = $this->createClient();

        $stream = new Connection($reader, $writer, $client);
        yield $stream->end('test');
    }

    public function testRead()
    {
        $reader = $this->createMock(Reader::class);
        $reader->expects(self::once())
            ->method('read')
            ->willReturn(new Success('test'));

        $writer = $this->createStub(Writer::class);
        $client = $this->createClient();

        $stream = new Connection($reader, $writer, $client);
        $data = yield $stream->read();

        self::assertEquals('test', $data);
    }

    public function testWrite()
    {
        $reader = $this->createStub(Reader::class);

        $writer = $this->createMock(Writer::class);
        $writer->expects(self::once())
            ->method('write')
            ->with('test')
            ->willReturn(new Success());

        $client = $this->createClient();

        $stream = new Connection($reader, $writer, $client);
        yield $stream->write('test');
    }

    public function testSetDefaultMode()
    {
        $reader = $this->createStub(Reader::class);

        $writer = $this->createMock(Writer::class);
        $writer->expects(self::once())
            ->method('setDefaultMode')
            ->with(Writer::MODE_TEXT);

        $client = $this->createClient();

        $stream = new Connection($reader, $writer, $client);
        $stream->setDefaultMode(Writer::MODE_TEXT);
    }

    public function testGetDefaultMode()
    {
        $reader = $this->createStub(Reader::class);

        $writer = $this->createMock(Writer::class);
        $writer->expects(self::once())
            ->method('getDefaultMode')
            ->willReturn(Writer::MODE_TEXT);

        $client = $this->createClient();

        $stream = new Connection($reader, $writer, $client);
        self::assertEquals(Writer::MODE_TEXT, $stream->getDefaultMode());
    }

    public function testGetRemoteAddress()
    {
        $reader = $this->createStub(Reader::class);
        $writer = $this->createStub(Writer::class);
        $client = $this->createClient();

        $stream = new Connection($reader, $writer, $client);
        self::assertEquals('127.0.0.1:8000', $stream->getRemoteAddress());
    }

    public function testGetId()
    {
        $reader = $this->createStub(Reader::class);
        $writer = $this->createStub(Writer::class);
        $client = $this->createClient();

        $stream = new Connection($reader, $writer, $client);
        self::assertEquals('1', $stream->getId());
    }

    /**
     * @return Client
     */
    private function createClient(): Client
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('getRemoteAddress')
            ->willReturn(new SocketAddress('127.0.0.1', '8000'));

        $client->expects(self::once())
            ->method('getId')
            ->willReturn(1);

        return $client;
    }
}
