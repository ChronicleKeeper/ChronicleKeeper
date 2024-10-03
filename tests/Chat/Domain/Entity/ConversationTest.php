<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Conversation::class)]
#[Small]
class ConversationTest extends TestCase
{
    #[Test]
    public function createEmpty(): void
    {
        $conversation = Conversation::createEmpty();

        self::assertNotEmpty($conversation->id);
        self::assertSame('Ungespeichert', $conversation->title);
        self::assertInstanceOf(Directory::class, $conversation->directory);
        self::assertInstanceOf(Settings::class, $conversation->settings);
        self::assertInstanceOf(ExtendedMessageBag::class, $conversation->messages);
    }

    #[Test]
    public function createFromSettings(): void
    {
        $appSettings  = (new SettingsBuilder())->build();
        $conversation = Conversation::createFromSettings($appSettings);

        self::assertNotEmpty($conversation->id);
        self::assertSame('Ungespeichert', $conversation->title);
        self::assertInstanceOf(Directory::class, $conversation->directory);
        self::assertInstanceOf(Settings::class, $conversation->settings);
        self::assertInstanceOf(ExtendedMessageBag::class, $conversation->messages);
    }

    #[Test]
    public function getSlug(): void
    {
        $conversation = new Conversation(
            Uuid::v4()->toString(),
            'Test Title',
            RootDirectory::get(),
            new Settings(),
            new ExtendedMessageBag(),
        );

        self::assertSame('Test-Title', $conversation->getSlug());
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $conversation = new Conversation(
            Uuid::v4()->toString(),
            'Test Title',
            RootDirectory::get(),
            new Settings(),
            new ExtendedMessageBag(),
        );

        $json = $conversation->jsonSerialize();

        self::assertArrayHasKey('id', $json);
        self::assertArrayHasKey('title', $json);
        self::assertArrayHasKey('directory', $json);
        self::assertArrayHasKey('settings', $json);
        self::assertArrayHasKey('messages', $json);
    }
}
