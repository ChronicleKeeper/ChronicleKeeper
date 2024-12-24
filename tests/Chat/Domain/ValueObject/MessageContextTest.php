<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\ValueObject;

use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageContext::class)]
#[Small]
class MessageContextTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $messageContext = new MessageContext();

        self::assertEmpty($messageContext->documents);
        self::assertEmpty($messageContext->images);
    }

    #[Test]
    public function itCanBeConstructedWithDocuments(): void
    {
        $messageContext = new MessageContext([new Reference('document', 'document', 'document')]);

        self::assertCount(1, $messageContext->documents);
        self::assertEmpty($messageContext->images);
    }

    #[Test]
    public function itCanBeConstructedWithImages(): void
    {
        $messageContext = new MessageContext([], [new Reference('image', 'image', 'image')]);

        self::assertEmpty($messageContext->documents);
        self::assertCount(1, $messageContext->images);
    }
}
