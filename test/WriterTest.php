<?php

namespace ekstazi\websocket\common\amphp\test;

use Amp\ByteStream\OutputStream;
use Amp\Failure;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\ClosedException;
use ekstazi\websocket\common\amphp\Writer;

class WriterTest extends AsyncTestCase
{

    /**
     * @dataProvider writeProvider
     * @param string $mode
     */
    public function testConstruct(string $mode)
    {
        $client = $this->createStub(Client::class);
        $writer = new Writer($client, $mode);
        self::assertInstanceOf(OutputStream::class, $writer);
        self::assertEquals($mode, $writer->getDefaultMode());
    }


    private function stubClientWrite(string $data, string $mode, $returnValue = null): Client
    {
        $returnValue = $returnValue ?? new Success();
        switch ($mode) {
            case Writer::MODE_BINARY:
                $mainMethod = 'sendBinary';
                $unusedMethod = 'send';
                break;
            case Writer::MODE_TEXT:
            default:
                $mainMethod = 'send';
                $unusedMethod = 'sendBinary';

        }
        $connection = $this->createMock(Client::class);
        $connection
            ->expects(self::atLeastOnce())
            ->method('isConnected')
            ->willReturn(true);
        $connection
            ->expects(self::once())
            ->method($mainMethod)
            ->with($this->equalTo($data))
            ->willReturn($returnValue);

        $connection
            ->expects(self::never())
            ->method($unusedMethod);

        return $connection;
    }

    /**
     * Test write method with data and different modes.
     * @param string $mode
     * @dataProvider writeProvider
     * @return \Generator
     * @throws
     */
    public function testWriteSuccess(string $mode)
    {
        $client = $this->stubClientWrite('test', $mode);
        $connection = new Writer($client);
        yield $connection->write('test', $mode);
    }

    /**
     * Test write method with data and different modes.
     * @param string $mode
     * @dataProvider writeProvider
     * @return \Generator
     * @throws
     */
    public function testWriteError(string $mode)
    {
        $client = $this->stubClientWrite('test', $mode, new Failure(new ClosedException('test', 1000, 'test reason')));
        $connection = new Writer($client);
        $this->expectException(\Amp\ByteStream\ClosedException::class);
        yield $connection->write('test', $mode);
    }

    /**
     * Test write method with data and different modes.
     * @param string $mode
     * @dataProvider writeProvider
     * @return \Generator
     * @throws
     */
    public function testWriteNormalClosedError(string $mode)
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('isConnected')
            ->willReturn(false);


        $connection = new Writer($client);
        $this->expectException(\Amp\ByteStream\ClosedException::class);
        yield $connection->write('test', $mode);
    }

    /**
     * @param string $mode
     * @dataProvider writeProvider
     * @return \Generator
     * @throws
     */
    public function testEndWithData(string $mode)
    {
        $client = $this->stubClientWrite('test', $mode);
        $client->expects(self::once())
            ->method('close')
            ->willReturn(new Success());

        $writer = new Writer($client);
        $promise = $writer->end('test', $mode);
        self::assertInstanceOf(Promise::class, $promise);
    }

    /**
     * @return \Generator
     * @throws
     */
    public function testEndWithoutData()
    {
        $client = $this->createMock(Client::class);

        $client->expects(self::never())
            ->method('send');

        $client->expects(self::never())
            ->method('sendBinary');

        $client->expects(self::once())
            ->method('close')
            ->willReturn(new Success());

        $writer = new Writer($client);
        yield $writer->end();
    }

    public function writeProvider()
    {
        return [
            'binary mode' => [Writer::MODE_BINARY],
            'text mode' => [Writer::MODE_TEXT],
        ];
    }
}
