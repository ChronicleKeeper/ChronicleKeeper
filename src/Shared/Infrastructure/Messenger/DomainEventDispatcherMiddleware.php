<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Messenger;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class DomainEventDispatcherMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $envelope = $stack->next()->handle($envelope, $stack);
        $stamp    = $envelope->last(HandledStamp::class);

        if (! $stamp instanceof HandledStamp) {
            return $envelope;
        }

        $result = $stamp->getResult();

        if (! $result instanceof MessageEventResult) {
            return $envelope;
        }

        foreach ($result->getEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $envelope;
    }
}
