<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Infrastructure\ValueResolver;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Query\GetWorldItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Infrastructure\ValueResolver\WorldItemResolver;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(WorldItemResolver::class)]
#[Small]
class WorldItemResolverTest extends TestCase
{
    #[Test]
    public function itReturnsNothingOnNullArgumentType(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $resolver = new WorldItemResolver($queryService);
        $result   = $resolver->resolve(Request::create('/'), $this->buildArgumentMetadata(null));

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsNothingOnInvalidArgumentType(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $resolver = new WorldItemResolver($queryService);
        $result   = $resolver->resolve(Request::create('/'), $this->buildArgumentMetadata('foo'));

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsNothingOnInvalidItemIdentifier(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $resolver = new WorldItemResolver($queryService);
        $result   = $resolver->resolve(
            Request::create('/', Request::METHOD_GET, ['item' => 'invalid']),
            $this->buildArgumentMetadata(Item::class),
        );

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsNothingOnNonStringItemIdentifier(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->never())->method('query');

        $resolver = new WorldItemResolver($queryService);
        $result   = $resolver->resolve(
            Request::create('/', Request::METHOD_GET, ['item' => 123]),
            $this->buildArgumentMetadata(Item::class),
        );

        self::assertSame([], $result);
    }

    #[Test]
    public function itReturnsItemOnValidItemIdentifier(): void
    {
        $item = (new ItemBuilder())->withId('bd197c47-cad9-4e9a-b900-f3d79f64f272')->build();

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willReturnCallback(
                static function (GetWorldItem $query) use ($item): Item {
                    self::assertSame($item->getId(), $query->id);

                    return $item;
                },
            );

        $resolver = new WorldItemResolver($queryService);
        $result   = $resolver->resolve(
            Request::create('/', Request::METHOD_GET, ['item' => $item->getId()]),
            $this->buildArgumentMetadata(Item::class),
        );

        self::assertSame([$item], $result);
    }

    #[Test]
    public function itThrowsNotFoundHttpExceptionOnItemNotFound(): void
    {
        $itemId = 'bd197c47-cad9-4e9a-b900-f3d79f64f272';

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Item "' . $itemId . '" not found.');

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->willThrowException(new Exception('Item not found'));

        $resolver = new WorldItemResolver($queryService);

        $resolver->resolve(
            Request::create('/', Request::METHOD_GET, ['item' => $itemId]),
            $this->buildArgumentMetadata(Item::class),
        );
    }

    private function buildArgumentMetadata(string|null $type): ArgumentMetadata
    {
        return new ArgumentMetadata('item', $type, false, false, null);
    }
}
