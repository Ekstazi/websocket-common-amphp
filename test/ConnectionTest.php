<?php

namespace ekstazi\websocket\common\amphp\test;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\OutputStream;
use Amp\PHPUnit\AsyncTestCase;
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
        $client = $this->createStub(Client::class);
        $stream = Connection::create($client, Writer::MODE_BINARY);
        self::assertInstanceOf(Connection::class, $stream);
        self::assertEquals(Writer::MODE_BINARY, $stream->getDefaultMode());
    }

    public function testConstruct()
    {
        $reader = $this->createStub(Reader::class);
        $writer = $this->createStub(Writer::class);

        $stream = new Connection($reader, $writer);

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

        $stream = new Connection($reader, $writer);
        yield $stream->end('test');
    }

    public function testRead()
    {
        $reader = $this->createMock(Reader::class);
        $reader->expects(self::once())
            ->method('read')
            ->willReturn(new Success('test'));

        $writer = $this->createStub(Writer::class);
        $stream = new Connection($reader, $writer);
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

        $stream = new Connection($reader, $writer);
        yield $stream->write('test');
    }

    public function testSetDefaultMode()
    {
        $reader = $this->createStub(Reader::class);

        $writer = $this->createMock(Writer::class);
        $writer->expects(self::once())
            ->method('setDefaultMode')
            ->with(Writer::MODE_TEXT);

        $stream = new Connection($reader, $writer);
        $stream->setDefaultMode(Writer::MODE_TEXT);
    }

    public function testGetDefaultMode()
    {
        $reader = $this->createStub(Reader::class);

        $writer = $this->createMock(Writer::class);
        $writer->expects(self::once())
            ->method('getDefaultMode')
            ->willReturn(Writer::MODE_TEXT);

        $stream = new Connection($reader, $writer);
        self::assertEquals(Writer::MODE_TEXT, $stream->getDefaultMode());
    }
}
