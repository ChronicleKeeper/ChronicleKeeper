<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Event;

use ChronicleKeeper\Document\Application\Event\VectorStorageUpdater;
use ChronicleKeeper\Document\Domain\Event\DocumentChangedContent;
use ChronicleKeeper\Document\Domain\Event\DocumentCreated;
use ChronicleKeeper\Document\Infrastructure\VectorStorage\LibraryDocumentUpdater;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(VectorStorageUpdater::class)]
#[Small]
final class VectorStorageUpdaterTest extends TestCase
{
    #[Test]
    public function itUpdatesVectorsOnDocumentContentChanged(): void
    {
        $document = (new DocumentBuilder())->build();

        $libraryDocumentUpdater = $this->createMock(LibraryDocumentUpdater::class);
        $libraryDocumentUpdater->expects($this->once())
            ->method('updateOrCreateVectorsForDocument')
            ->with($document);

        $updater = new VectorStorageUpdater($libraryDocumentUpdater);

        $updater->updateOnDocumentContentChanged(new DocumentChangedContent($document, 'old content'));
    }

    #[Test]
    public function itCreatesVectorsOnDocumentCreated(): void
    {
        $document = (new DocumentBuilder())->build();

        $libraryDocumentUpdater = $this->createMock(LibraryDocumentUpdater::class);
        $libraryDocumentUpdater->expects($this->once())
            ->method('updateOrCreateVectorsForDocument')
            ->with($document);

        $updater = new VectorStorageUpdater($libraryDocumentUpdater);

        $updater->createOnDocumentCreated(new DocumentCreated($document));
    }
}
