<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Messenger;

use ChronicleKeeper\Shared\Infrastructure\Messenger\DomainEventDispatcherMiddleware;
use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[CoversClass(DomainEventDispatcherMiddleware::class)]
#[Small]
final class DomainEventDispatcherMiddlewareTest extends TestCase
{
    #[Test]
    public function itStopsIfThereIsNoHandledStamp(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $envelope = new Envelope(new stdClass());
        $stack    = new StackMiddleware();

        $middleware = new DomainEventDispatcherMiddleware($eventDispatcher);
        $middleware->handle($envelope, $stack);
    }

    #[Test]
    public function itStopsIfTheResultIsNotAMessageEventResult(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->never())->method('dispatch');

        $envelope = new Envelope(new stdClass());
        $envelope->with(new HandledStamp(new stdClass(), 'my-handler'));

        $stack = new StackMiddleware();

        $middleware = new DomainEventDispatcherMiddleware($eventDispatcher);
        $middleware->handle($envelope, $stack);
    }

    #[Test]
    public function itDispatchesTheEvents(): void
    {
        $result = new MessageEventResult([$event = new stdClass()]);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())->method('dispatch')->with(self::isObject());

        $envelope = new Envelope(new stdClass(), [new HandledStamp($result, 'my-handler')]);
        $stack    = new StackMiddleware();

        $middleware = new DomainEventDispatcherMiddleware($eventDispatcher);
        $middleware->handle($envelope, $stack);
    }
}
