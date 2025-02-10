<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Command;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Library\Application\Command\DeleteDirectory;
use ChronicleKeeper\Library\Application\Command\DeleteDirectoryHandler;
use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabaseTestCase;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;

use function assert;

#[CoversClass(DeleteDirectory::class)]
#[CoversClass(DeleteDirectoryHandler::class)]
#[Large]
final class DeleteDirectoryTest extends DatabaseTestCase
{
    private DeleteDirectoryHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $handler = self::getContainer()->get(DeleteDirectoryHandler::class);
        assert($handler instanceof DeleteDirectoryHandler);

        $this->handler = $handler;
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handler);
    }

    #[Test]
    public function itFailsWhenGivingRootDirectoryToDeletion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The root directory can not be deleted.');

        new DeleteDirectory(RootDirectory::get());
    }

    #[Test]
    public function itIgnoresNonExistentDirectorySilently(): void
    {
        ($this->handler)(new DeleteDirectory((new DirectoryBuilder())->build()));

        $this->assertTableIsEmpty('directories');
    }

    #[Test]
    public function itIsSimplyDeletingADirectoryWithoutContent(): void
    {
        // ------------------- The test setup -------------------

        $directory = (new DirectoryBuilder())->withId('30ff17cb-f098-4f6f-81ec-348e85d35395')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // ------------------- The test scenario -------------------

        ($this->handler)(new DeleteDirectory($directory));

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('directories');
    }

    #[Test]
    public function itIsDeletingADirectoryWithContent(): void
    {
        // ------------------- The test setup -------------------

        $directory = (new DirectoryBuilder())->withId('3c3b715e-a199-4cdf-97e6-bd98d1b815ec')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        $subDirectory = (new DirectoryBuilder())
            ->withId('eb5e84c0-6632-46ef-96a0-a4174300eae0')
            ->withParent($directory)
            ->build();
        $this->bus->dispatch(new StoreDirectory($subDirectory));

        $image = (new ImageBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreImage($image));
        $image = (new ImageBuilder())->withDirectory($subDirectory)->build();
        $this->bus->dispatch(new StoreImage($image));

        $document = (new DocumentBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreDocument($document));

        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreConversation($conversation));

        // ------------------- The test scenario -------------------

        ($this->handler)(new DeleteDirectory($directory));

        // ------------------- The test assertions -------------------

        $this->assertTableIsEmpty('directories');
        $this->assertTableIsEmpty('images');
        $this->assertTableIsEmpty('documents');
        $this->assertTableIsEmpty('conversations');
    }
}
