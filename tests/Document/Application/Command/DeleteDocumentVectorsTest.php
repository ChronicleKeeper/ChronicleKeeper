<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectors;
use ChronicleKeeper\Document\Application\Command\DeleteDocumentVectorsHandler;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
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

        self::assertSame('document-id', $command->documentId);
    }

    #[Test]
    public function documentIsFullyDeleted(): void
    {
        $databasePlatform = new DatabasePlatformMock();
        $handler          = new DeleteDocumentVectorsHandler($databasePlatform);
        $handler(new DeleteDocumentVectors('9f86d75f-fc93-4522-b01d-3059aa887971'));

        $databasePlatform->assertExecutedQuery('DELETE FROM documents_vectors WHERE document_id = :id', ['id' => '9f86d75f-fc93-4522-b01d-3059aa887971']);
    }
}
