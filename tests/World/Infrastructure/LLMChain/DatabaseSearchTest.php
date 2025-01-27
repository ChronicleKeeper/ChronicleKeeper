<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\World\Domain\Entity\ItemBuilder;
use ChronicleKeeper\World\Application\Query\FindRelationsOfItem;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Domain\ValueObject\Relation;
use ChronicleKeeper\World\Infrastructure\LLMChain\DatabaseSearch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(DatabaseSearch::class)]
#[Small]
class DatabaseSearchTest extends TestCase
{
    #[Test]
    public function itIsAbleToSearchForWorldItems(): void
    {
        $item       = (new ItemBuilder())->withType(ItemType::PERSON)->build();
        $foundItems = [$item];

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(static function (QueryParameters $query) use ($foundItems) {
                if ($query instanceof SearchWorldItems) {
                    self::assertSame('Far Far Away', $query->search);

                    return $foundItems;
                }

                if ($query instanceof FindRelationsOfItem) {
                    self::assertSame($foundItems[0]->getId(), $query->itemid);

                    return [
                        new Relation(
                            (new ItemBuilder())->withType(ItemType::WEAPON)->build(),
                            'related',
                        ),
                    ];
                }

                throw new RuntimeException('Unexpected query');
            });

        $runtimeCollector = $this->createMock(RuntimeCollector::class);
        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(self::isInstanceOf(FunctionDebug::class));

        $databaseSearch = new DatabaseSearch($queryService, $runtimeCollector);

        $response = $databaseSearch('Far Far Away');

        self::assertStringContainsString(
            'I have found the following items that are associated to the question:',
            $response,
        );
        self::assertStringContainsString('Name: ' . $item->getName(), $response);
        self::assertStringContainsString('Type: ' . $item->getType()->value, $response);
        self::assertStringContainsString('Beziehung zu', $response);
    }

    #[Test]
    public function itIsAbleToSearchForWorldItemsWithoutRelations(): void
    {
        $item       = (new ItemBuilder())->withType(ItemType::PERSON)->build();
        $foundItems = [$item];

        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(static function (QueryParameters $query) use ($foundItems) {
                if ($query instanceof SearchWorldItems) {
                    self::assertSame('Far Far Away', $query->search);

                    return $foundItems;
                }

                if ($query instanceof FindRelationsOfItem) {
                    self::assertSame($foundItems[0]->getId(), $query->itemid);

                    return [];
                }

                throw new RuntimeException('Unexpected query');
            });

        $runtimeCollector = $this->createMock(RuntimeCollector::class);
        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(self::isInstanceOf(FunctionDebug::class));

        $databaseSearch = new DatabaseSearch($queryService, $runtimeCollector);

        $response = $databaseSearch('Far Far Away');

        self::assertStringContainsString(
            'I have found the following items that are associated to the question:',
            $response,
        );
        self::assertStringContainsString('Name: ' . $item->getName(), $response);
        self::assertStringNotContainsString('Relations:', $response);
    }

    #[Test]
    public function itIsLoggingAnEmptyResult(): void
    {
        $queryService = $this->createMock(QueryService::class);
        $queryService->expects($this->once())
            ->method('query')
            ->with(self::isInstanceOf(SearchWorldItems::class))
            ->willReturn([]);

        $runtimeCollector = $this->createMock(RuntimeCollector::class);
        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(self::isInstanceOf(FunctionDebug::class));

        $databaseSearch = new DatabaseSearch($queryService, $runtimeCollector);

        $response = $databaseSearch('Nonexistent Item');

        self::assertSame('No results from the item search for the following labels: Nonexistent Item', $response);
    }
}
