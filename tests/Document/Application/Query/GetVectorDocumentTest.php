<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\GetVectorDocument;
use ChronicleKeeper\Document\Application\Query\GetVectorDocumentQuery;
use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Document\Domain\Entity\VectorDocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(GetVectorDocument::class)]
#[CoversClass(GetVectorDocumentQuery::class)]
#[Small]
class GetVectorDocumentTest extends TestCase
{
    #[Test]
    public function queryIsCorrect(): void
    {
        $query = new GetVectorDocument('document-id');

        self::assertSame(GetVectorDocumentQuery::class, $query->getQueryClass());
        self::assertSame('document-id', $query->id);
    }

    #[Test]
    public function theQueryIsExectuted(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->with('vector.documents', 'document-id.json')
            ->willReturn('{"id":"document-id"}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('deserialize')
            ->with('{"id":"document-id"}', VectorDocument::class, 'json')
            ->willReturn($document = (new VectorDocumentBuilder())->build());

        $query = new GetVectorDocumentQuery($fileAccess, $serializer);

        self::assertSame(
            $document,
            $query->query(new GetVectorDocument('document-id')),
        );
    }
}
