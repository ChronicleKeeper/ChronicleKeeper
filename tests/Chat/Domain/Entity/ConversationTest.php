<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
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
    }

    #[Test]
    public function createFromSettings(): void
    {
        $appSettings  = (new SettingsBuilder())->build();
        $conversation = Conversation::createFromSettings($appSettings);

        self::assertNotEmpty($conversation->id);
        self::assertSame('Ungespeichert', $conversation->title);
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
        $conversation   = new Conversation(
            $id         = Uuid::v4()->toString(),
            $title      = 'Test Title',
            $directory  = RootDirectory::get(),
            $settings   = new Settings(),
            $messageBag = new ExtendedMessageBag(),
        );

        $json = $conversation->jsonSerialize();

        self::assertSame(
            [
                'id' => $id,
                'title' => $title,
                'directory' => $directory->id,
                'settings' => $settings,
                'messages' => $messageBag,
            ],
            $json,
        );
    }
}
