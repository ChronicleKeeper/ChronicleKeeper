<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Application\Query\GetDocumentQuery;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(GetDocument::class)]
#[CoversClass(GetDocumentQuery::class)]
#[Small]
class GetDocumentTest extends TestCase
{
    #[Test]
    public function queryIsCorrect(): void
    {
        $query = new GetDocument('document-id');

        self::assertSame(GetDocumentQuery::class, $query->getQueryClass());
        self::assertSame('document-id', $query->id);
    }

    #[Test]
    public function theQueryIsExectuted(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->with('library.documents', 'document-id.json')
            ->willReturn('{"id":"document-id"}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('deserialize')
            ->with('{"id":"document-id"}', Document::class, 'json')
            ->willReturn($document = (new DocumentBuilder())->build());

        $query = new GetDocumentQuery($fileAccess, $serializer);

        self::assertSame(
            $document,
            $query->query(new GetDocument('document-id')),
        );
    }
}
