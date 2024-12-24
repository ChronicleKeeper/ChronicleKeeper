<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\Messenger;

use ChronicleKeeper\Shared\Infrastructure\Messenger\MessageEventResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(MessageEventResult::class)]
#[Small]
final class MessageEventResultTest extends TestCase
{
    #[Test]
    public function itReturnsTheEvents(): void
    {
        $events = [new stdClass()];

        $messageEventResult = new MessageEventResult($events);

        self::assertEquals($events, $messageEventResult->getEvents());
    }
}
