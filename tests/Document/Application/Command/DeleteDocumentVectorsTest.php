<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectorsHandler;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeleteDocumentVectors::class)]
#[CoversClass(DeleteDocumentVectorsHandler::class)]
#[Small]
class DeleteDocumentVectorsTest extends TestCase
{
    #[Test]
    public function commandIsInstantiatable(): void
    {
        $command = new DeleteDocumentVectors('document-id');

        self::assertSame('document-id', $command->id);
    }

    #[Test]
    public function documentIsFullyDeleted(): void
    {
        $vectorDocument = (new VectorDocumentBuilder())->build();

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('delete')
            ->with('vector.documents', $vectorDocument->id . '.json');

        $handler = new DeleteDocumentVectorsHandler($fileAccess);
        $handler(new DeleteDocumentVectors($vectorDocument->id));
    }
}
