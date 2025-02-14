<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Infrastructure\ValueResolver;

use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Infrastructure\ValueResolver\GeneratorRequestValueResolver;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use ChronicleKeeper\Test\ImageGenerator\Domain\Entity\GeneratorRequestBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(GeneratorRequestValueResolver::class)]
#[Small]
class GeneratorRequestValueResolverTest extends TestCase
{
    #[Test]
    public function resolveWithNullType(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService
            ->expects($this->never())
            ->method('query');

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(null);

        $valueResolver = new GeneratorRequestValueResolver($queryService);
        $resolvedValue = $valueResolver->resolve(Request::create('/'), $argument);

        self::assertEmpty($resolvedValue);
    }

    #[Test]
    public function reolveWithUnsupportType(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService
            ->expects($this->never())
            ->method('query');

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(stdClass::class);

        $valueResolver = new GeneratorRequestValueResolver($queryService);
        $resolvedValue = $valueResolver->resolve(Request::create('/'), $argument);

        self::assertEmpty($resolvedValue);
    }

    #[Test]
    public function resolveWithNonStringIdentifier(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService
            ->expects($this->never())
            ->method('query');

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(GeneratorRequest::class);
        $argument
            ->expects($this->once())
            ->method('getName')
            ->willReturn('id');

        $request = Request::create('/');
        $request->attributes->add(['id' => 123]);

        $valueResolver = new GeneratorRequestValueResolver($queryService);
        $resolvedValue = $valueResolver->resolve($request, $argument);

        self::assertEmpty($resolvedValue);
    }

    #[Test]
    public function resolveWithNonUuidIdentifier(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService
            ->expects($this->never())
            ->method('query');

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(GeneratorRequest::class);
        $argument
            ->expects($this->once())
            ->method('getName')
            ->willReturn('id');

        $request = Request::create('/');
        $request->attributes->add(['id' => 'foo']);

        $valueResolver = new GeneratorRequestValueResolver($queryService);
        $resolvedValue = $valueResolver->resolve($request, $argument);

        self::assertEmpty($resolvedValue);
    }

    #[Test]
    public function resultsInNotFoundExceptionWhenQueryReturnsNull(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $queryService = $this->createMock(QueryService::class);
        $queryService
            ->expects($this->once())
            ->method('query')
            ->willReturnCallback(static function (GetGeneratorRequest $query): GeneratorRequest|null {
                self::assertSame('7bbb72b2-4701-4036-b977-2b7a530e3aea', $query->id);

                return null;
            });

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(GeneratorRequest::class);
        $argument
            ->expects($this->once())
            ->method('getName')
            ->willReturn('id');

        $request = Request::create('/');
        $request->attributes->add(['id' => '7bbb72b2-4701-4036-b977-2b7a530e3aea']);

        $valueResolver = new GeneratorRequestValueResolver($queryService);
        $resolvedValue = $valueResolver->resolve($request, $argument);

        self::assertEmpty($resolvedValue);
    }

    #[Test]
    public function resultsInNotFoundExceptionWhenFileIsNotReadable(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $queryService = $this->createMock(QueryService::class);
        $queryService
            ->expects($this->once())
            ->method('query')
            ->with(self::callback(static function (GetGeneratorRequest $query): bool {
                self::assertSame('7bbb72b2-4701-4036-b977-2b7a530e3aea', $query->id);

                return true;
            }))
            ->willThrowException(self::createStub(UnableToReadFile::class));

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(GeneratorRequest::class);
        $argument
            ->expects($this->once())
            ->method('getName')
            ->willReturn('id');

        $request = Request::create('/');
        $request->attributes->add(['id' => '7bbb72b2-4701-4036-b977-2b7a530e3aea']);

        $valueResolver = new GeneratorRequestValueResolver($queryService);
        $resolvedValue = $valueResolver->resolve($request, $argument);

        self::assertEmpty($resolvedValue);
    }

    #[Test]
    public function resultsInFoundGeneratorResult(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService
            ->expects($this->once())
            ->method('query')
            ->willReturnCallback(static function (GetGeneratorRequest $query): GeneratorRequest {
                self::assertSame('7bbb72b2-4701-4036-b977-2b7a530e3aea', $query->id);

                return (new GeneratorRequestBuilder())->build();
            });

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument
            ->expects($this->once())
            ->method('getType')
            ->willReturn(GeneratorRequest::class);
        $argument
            ->expects($this->once())
            ->method('getName')
            ->willReturn('id');

        $request = Request::create('/');
        $request->attributes->add(['id' => '7bbb72b2-4701-4036-b977-2b7a530e3aea']);

        $valueResolver = new GeneratorRequestValueResolver($queryService);
        $resolvedValue = $valueResolver->resolve($request, $argument);

        self::assertCount(1, $resolvedValue);
    }
}
