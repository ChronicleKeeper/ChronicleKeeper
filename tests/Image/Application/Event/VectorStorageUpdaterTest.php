<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Event;

use ChronicleKeeper\Image\Application\Event\VectorStorageUpdater;
use ChronicleKeeper\Image\Domain\Event\ImageCreated;
use ChronicleKeeper\Image\Domain\Event\ImageDescriptionUpdated;
use ChronicleKeeper\Image\Infrastructure\VectorStorage\LibraryImageUpdater;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
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
        $image = (new ImageBuilder())->build();

        $libraryImageUpdater = $this->createMock(LibraryImageUpdater::class);
        $libraryImageUpdater->expects($this->once())
            ->method('updateOrCreateVectorsForImage')
            ->with($image);

        $updater = new VectorStorageUpdater($libraryImageUpdater);

        $updater->updateOnImageChangedDescription(new ImageDescriptionUpdated($image, 'old content'));
    }

    #[Test]
    public function itCreatesVectorsOnDocumentCreated(): void
    {
        $image = (new ImageBuilder())->build();

        $libraryImageUpdater = $this->createMock(LibraryImageUpdater::class);
        $libraryImageUpdater->expects($this->once())
            ->method('updateOrCreateVectorsForImage')
            ->with($image);

        $updater = new VectorStorageUpdater($libraryImageUpdater);

        $updater->createOnImageCreation(new ImageCreated($image));
    }
}
