<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;
use ChronicleKeeper\Chat\Domain\Event\ConversationMovedToDirectory;
use ChronicleKeeper\Chat\Domain\Event\ConversationRenamed;
use ChronicleKeeper\Chat\Domain\Event\ConversationSettingsChanged;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
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

        self::assertNotEmpty($conversation->getId());
        self::assertSame('Unbekanntes Gespräch', $conversation->getTitle());
    }

    #[Test]
    public function createFromSettings(): void
    {
        $appSettings  = (new SettingsBuilder())->build();
        $conversation = Conversation::createFromSettings($appSettings);

        self::assertNotEmpty($conversation->getId());
        self::assertSame('Unbekanntes Gespräch', $conversation->getTitle());
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
                'directory' => $directory->getId(),
                'settings' => $settings,
                'messages' => $messageBag,
            ],
            $json,
        );
    }

    #[Test]
    public function itCanBeDuplicated(): void
    {
        $conversation = (new ConversationBuilder())
            ->withMessages((new ExtendedMessageBagBuilder())
                ->withMessages((new ExtendedMessageBuilder())->build(), (new ExtendedMessageBuilder())->build())
                ->build())
            ->build();

        $duplicatedConversation = Conversation::createFromConversation($conversation);

        // Check base data
        self::assertNotSame($conversation->getId(), $duplicatedConversation->getId());
        self::assertSame($conversation->getTitle(), $duplicatedConversation->getTitle());
        self::assertSame($conversation->getDirectory(), $duplicatedConversation->getDirectory());

        // Check settings
        self::assertSame(
            $conversation->getSettings()->version,
            $duplicatedConversation->getSettings()->version,
        );
        self::assertSame(
            $conversation->getSettings()->temperature,
            $duplicatedConversation->getSettings()->temperature,
        );
        self::assertSame(
            $conversation->getSettings()->imagesMaxDistance,
            $duplicatedConversation->getSettings()->imagesMaxDistance,
        );
        self::assertSame(
            $conversation->getSettings()->documentsMaxDistance,
            $duplicatedConversation->getSettings()->documentsMaxDistance,
        );

        // Check messages
        self::assertCount(2, $duplicatedConversation->getMessages());
        self::assertSame(
            $conversation->getMessages()->getArrayCopy(),
            $duplicatedConversation->getMessages()->getArrayCopy(),
        );
    }

    #[Test]
    public function itRecordsEventWhenRenamed(): void
    {
        $conversation = (new ConversationBuilder())->withTitle('Old Title')->build();
        $conversation->rename('New Title');

        $events = $conversation->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ConversationRenamed::class, $events[0]);
        self::assertSame('Old Title', $events[0]->oldTitle);
        self::assertSame('New Title', $conversation->getTitle());
    }

    #[Test]
    public function itDoesNotRecordEventWhenRenamedToSameTitle(): void
    {
        $conversation = (new ConversationBuilder())->withTitle('Same Title')->build();
        $conversation->rename('Same Title');

        $events = $conversation->flushEvents();
        self::assertEmpty($events);
    }

    #[Test]
    public function itRecordsEventWhenMovedToDirectory(): void
    {
        $oldDirectory = (new DirectoryBuilder())->build();
        $newDirectory = (new DirectoryBuilder())->build();
        $conversation = (new ConversationBuilder())->withDirectory($oldDirectory)->build();

        $conversation->moveToDirectory($newDirectory);

        $events = $conversation->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ConversationMovedToDirectory::class, $events[0]);
        self::assertSame($oldDirectory, $events[0]->oldDirectory);
        self::assertSame($newDirectory, $conversation->getDirectory());
    }

    #[Test]
    public function itDoesNotRecordEventWhenMovedToSameDirectory(): void
    {
        $directory    = (new DirectoryBuilder())->build();
        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();

        $conversation->moveToDirectory($directory);

        $events = $conversation->flushEvents();
        self::assertEmpty($events);
    }

    #[Test]
    public function itRecordsEventWhenSettingsChanged(): void
    {
        $conversation = (new ConversationBuilder())
            ->withSettings(new Settings(temperature: 0.2))
            ->build();

        $settings = new Settings();
        $conversation->changeSettings($settings);

        $events = $conversation->flushEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ConversationSettingsChanged::class, $events[0]);
        self::assertNotSame($settings, $events[0]->oldSettings);
        self::assertSame($settings, $conversation->getSettings());
    }

    #[Test]
    public function itDoesNotRecordEventWhenSettingsNotChanged(): void
    {
        $conversation = (new ConversationBuilder())
            ->withSettings(new Settings(temperature: 0.2))
            ->build();

        $newSettings = new Settings(temperature: 0.2);

        $conversation->changeSettings($newSettings);

        $events = $conversation->flushEvents();
        self::assertEmpty($events);
    }
}
