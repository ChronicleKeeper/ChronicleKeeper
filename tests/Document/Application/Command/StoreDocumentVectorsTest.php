<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Command;

use ChronicleKeeper\Document\Application\Command\StoreDocumentVectors;
use ChronicleKeeper\Document\Application\Command\StoreDocumentVectorsHandler;
use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(StoreDocumentVectors::class)]
#[CoversClass(StoreDocumentVectorsHandler::class)]
#[Small]
class StoreDocumentVectorsTest extends TestCase
{
    #[Test]
    public function commandIsInstantiatable(): void
    {
        $vectorDocument = self::createStub(VectorDocument::class);
        $command        = new StoreDocumentVectors($vectorDocument);

        self::assertSame($vectorDocument, $command->vectorDocument);
    }

    #[Test]
    public function documentIsStored(): void
    {
        $vectorDocument = (new VectorDocumentBuilder())->build();

        $handler        = new StoreDocumentVectorsHandler(
            $fileAccess = $this->createMock(FileAccess::class),
            $serializer = self::createStub(SerializerInterface::class),
        );

        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'vector.documents',
                $vectorDocument->id . '.json',
                $serializer->serialize($vectorDocument, 'json'),
            );

        $handler(new StoreDocumentVectors($vectorDocument));
    }
}
