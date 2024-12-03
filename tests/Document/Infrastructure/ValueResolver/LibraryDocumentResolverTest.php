<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Infrastructure\ValueResolver;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Infrastructure\ValueResolver\LibraryDocumentResolver;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(LibraryDocumentResolver::class)]
#[Small]
class LibraryDocumentResolverTest extends TestCase
{
    #[Test]
    public function itReturnsNothingOnNullArgumentType(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $resolver = new LibraryDocumentResolver($queryService);
        $result   = $resolver->resolve(Request::create('/'), $this->buildArgumentMetadata(null));

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsNothingOnInvalidArgumentType(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $resolver = new LibraryDocumentResolver($queryService);
        $result   = $resolver->resolve(Request::create('/'), $this->buildArgumentMetadata('foo'));

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsNothingOnInvalidDocumentIdentifier(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $resolver = new LibraryDocumentResolver($queryService);
        $result   = $resolver->resolve(
            Request::create('/', 'GET', ['document' => 'invalid']),
            $this->buildArgumentMetadata(Document::class),
        );

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsNothingOnNonStringDocumentIdentifier(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $resolver = new LibraryDocumentResolver($queryService);
        $result   = $resolver->resolve(
            Request::create('/', 'GET', ['document' => 123]),
            $this->buildArgumentMetadata(Document::class),
        );

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsDocumentOnValidDocumentIdentifier(): void
    {
        $document = (new DocumentBuilder())->withId('58995dfd-0d69-4471-ac65-1d202e81069d')->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (GetDocument $query) use ($document): Document {
                    self::assertSame($document->id, $query->id);

                    return $document;
                },
            );

        $resolver = new LibraryDocumentResolver($queryService);
        $result   = $resolver->resolve(
            Request::create('/', 'GET', ['document' => $document->id]),
            $this->buildArgumentMetadata(Document::class),
        );

        self::assertSame([$document], $result);
    }

    #[Test]
    public function itThrowsNotFoundHttpExceptionOnUnableToReadFile(): void
    {
        $document = (new DocumentBuilder())->withId('58995dfd-0d69-4471-ac65-1d202e81069d')->build();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Document "' . $document->id . '" not found.');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willThrowException(new UnableToReadFile('/'));

        $resolver = new LibraryDocumentResolver($queryService);

        $resolver->resolve(
            Request::create('/', 'GET', ['document' => $document->id]),
            $this->buildArgumentMetadata(Document::class),
        );
    }

    private function buildArgumentMetadata(string|null $type): ArgumentMetadata
    {
        return new ArgumentMetadata('document', $type, false, false, null);
    }
}
