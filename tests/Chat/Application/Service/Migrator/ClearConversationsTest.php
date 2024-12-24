<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Service\Migrator;

use ChronicleKeeper\Chat\Application\Service\Migrator\ClearConversations;
use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClearConversations::class)]
#[Small]
class ClearConversationsTest extends TestCase
{
    #[Test]
    public function itDoesNotSupportNewerVersions(): void
    {
        $clearConversations = new ClearConversations(self::createStub(FileAccess::class));

        self::assertTrue($clearConversations->isSupporting(FileType::CHAT_CONVERSATION, '0.6'));
    }

    #[Test]
    public function itDoesSupportOlderVersions(): void
    {
        $clearConversations = new ClearConversations(self::createStub(FileAccess::class));

        self::assertTrue($clearConversations->isSupporting(FileType::CHAT_CONVERSATION, '0.5'));
    }

    #[Test]
    public function itDowsNotSupportADifferentFileType(): void
    {
        $clearConversations = new ClearConversations(self::createStub(FileAccess::class));

        self::assertFalse($clearConversations->isSupporting(FileType::VECTOR_STORAGE_DOCUMENT, '0.5'));
    }

    #[Test]
    public function itDeletesTheConversation(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('library.conversations', 'conversation.json');

        $clearConversations = new ClearConversations($fileAccess);
        $clearConversations->migrate('conversation.json', FileType::CHAT_CONVERSATION);
    }
}
