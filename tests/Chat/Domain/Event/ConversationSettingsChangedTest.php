<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Event;

use ChronicleKeeper\Chat\Domain\Event\ConversationSettingsChanged;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversationSettingsChanged::class)]
#[Small]
class ConversationSettingsChangedTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $conversation = (new ConversationBuilder())->build();
        $oldSettings  = new Settings();
        $event        = new ConversationSettingsChanged($conversation, $oldSettings);

        self::assertSame($conversation, $event->conversation);
        self::assertSame($oldSettings, $event->oldSettings);
    }
}
