<?php

namespace ekstazi\websocket\common\amphp\test;

use Amp\ByteStream\InputStream;
use Amp\ByteStream\OutputStream;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use DG\BypassFinals;
use ekstazi\websocket\common\amphp\Reader;
use ekstazi\websocket\common\amphp\Stream;
use ekstazi\websocket\common\amphp\Writer;

class StreamTest extends AsyncTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        BypassFinals::enable();
    }

    public function testConstruct()
    {
        $reader = $this->createStub(Reader::class);
        $writer = $this->createStub(Writer::class);

        $stream = new Stream($reader, $writer);

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

        $stream = new Stream($reader, $writer);
        yield $stream->end('test');
    }

    public function testRead()
    {
        $reader = $this->createMock(Reader::class);
        $reader->expects(self::once())
            ->method('read')
            ->willReturn(new Success('test'));

        $writer = $this->createStub(Writer::class);
        $stream = new Stream($reader, $writer);
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

        $stream = new Stream($reader, $writer);
        yield $stream->write('test');
    }

    public function testSetMode()
    {
        $reader = $this->createStub(Reader::class);

        $writer = $this->createMock(Writer::class);
        $writer->expects(self::once())
            ->method('setMode')
            ->with(Writer::MODE_TEXT);

        $stream = new Stream($reader, $writer);
        $stream->setMode(Writer::MODE_TEXT);
    }

    public function testGetMode()
    {
        $reader = $this->createStub(Reader::class);

        $writer = $this->createMock(Writer::class);
        $writer->expects(self::once())
            ->method('getMode')
            ->willReturn(Writer::MODE_TEXT);

        $stream = new Stream($reader, $writer);
        self::assertEquals(Writer::MODE_TEXT, $stream->getMode());
    }
}
