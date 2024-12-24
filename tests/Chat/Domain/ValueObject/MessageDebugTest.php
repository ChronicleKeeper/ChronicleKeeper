<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\ValueObject;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageDebug::class)]
#[Small]
class MessageDebugTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $messageDebug = new MessageDebug();

        self::assertEmpty($messageDebug->functions);
    }

    #[Test]
    public function itCanBeConstructedWithFunctions(): void
    {
        $functionDebug = new FunctionDebug('tool', [], 'result');
        $messageDebug  = new MessageDebug([$functionDebug]);

        self::assertCount(1, $messageDebug->functions);
        self::assertSame($functionDebug, $messageDebug->functions[0]);
    }
}
