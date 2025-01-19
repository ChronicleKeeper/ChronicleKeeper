<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Query;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Application\Query\GetDocumentQuery;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(GetDocument::class)]
#[CoversClass(GetDocumentQuery::class)]
#[Small]
class GetDocumentTest extends TestCase
{
    #[Test]
    public function queryIsCorrect(): void
    {
        $query = new GetDocument('fef8517b-6ffe-4102-b4e5-5f685764f2be');

        self::assertSame(GetDocumentQuery::class, $query->getQueryClass());
        self::assertSame('fef8517b-6ffe-4102-b4e5-5f685764f2be', $query->id);
    }

    #[Test]
    public function theQueryIsExectuted(): void
    {
        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['title' => 'foo'], Document::class)
            ->willReturn($document = (new DocumentBuilder())->build());

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM documents WHERE id = :id',
            ['id' => $document->getId()],
            [['title' => 'foo']],
        );

        $query = new GetDocumentQuery($denormalizer, $databasePlatform);

        self::assertSame(
            $document,
            $query->query(new GetDocument($document->getId())),
        );
    }
}
