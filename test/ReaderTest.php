<?php

namespace ekstazi\websocket\common\amphp\test;

use Amp\ByteStream\ClosedException as BaseClosedException;
use Amp\ByteStream\InMemoryStream;
use Amp\ByteStream\InputStream;
use Amp\ByteStream\Payload;
use Amp\Failure;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\ClosedException;
use Amp\Websocket\Code;
use ekstazi\websocket\common\amphp\Reader;

class ReaderTest extends AsyncTestCase
{
    public function testConstruct()
    {
        $client = $this->createStub(Client::class);
        $reader = new Reader($client);
        self::assertInstanceOf(InputStream::class, $reader);
    }

    /**
     * @param Promise $data
     * @return Client
     */
    private function stubRead(Promise $data = null): Client
    {
        $connection = $this->createMock(Client::class);
        $connection
            ->expects(self::atLeastOnce())
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                $data,
                new Success(null)
            );
        return $connection;
    }

    /**
     * Test that data readed from websocket client.
     * @return \Generator
     * @throws
     */
    public function testReadSuccess()
    {
        $connection = $this->stubRead(
            new Success(
                new Payload(
                    new InMemoryStream('test')
                )
            )
        );

        $connection = new Reader($connection);
        $data = yield $connection->read();
        self::assertEquals('test', $data);
    }

    /**
     * Test that null returned when websocket client was closed.
     * @return \Generator
     * @throws
     */
    public function testReadClose()
    {
        $connection = $this->stubRead(new Success(null));

        $connection = new Reader($connection);
        $data = yield $connection->read();
        self::assertNull($data);
    }

    /**
     * Test that null returned when websocket client was closed.
     * @return \Generator
     * @throws
     */
    public function testReadCloseError()
    {
        $connection = $this->stubRead(new Failure(new ClosedException('test', 1000, 'test')));

        $connection = new Reader($connection);
        $this->expectException(BaseClosedException::class);
        $data = yield $connection->read();
    }

    /**
     * Test that null returned when websocket client was closed.
     * @return \Generator
     * @throws
     */
    public function testReadNormalCloseError()
    {
        $connection = $this->stubRead(new Success(null));
        $connection->expects(self::atLeastOnce())
            ->method('getCloseCode')
            ->willReturn(Code::UNEXPECTED_SERVER_ERROR);

        $connection->expects(self::once())
            ->method('getCloseReason')
            ->willReturn('test');

        $connection = new Reader($connection);
        $this->expectException(BaseClosedException::class);
        $data = yield $connection->read();
    }
}
