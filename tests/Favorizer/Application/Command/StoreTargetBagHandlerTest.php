<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Command;

use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBagHandler;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StoreTargetBag::class)]
#[CoversClass(StoreTargetBagHandler::class)]
#[Small]
class StoreTargetBagHandlerTest extends TestCase
{
    #[Test]
    public function storeTargetBag(): void
    {
        $targetBag = new TargetBag();
        $targetBag->append(new LibraryDocumentTarget('4c0ad0b6-772d-4ef2-8fd6-8120c90e6e45', 'Title 1'));
        $targetBag->append(new LibraryImageTarget('c0773b2c-0479-4a5b-91b9-2b52b10fcde8', 'Title 2'));

        $databasePlatform = new DatabasePlatformMock();

        $handler = new StoreTargetBagHandler($databasePlatform);
        $handler(new StoreTargetBag($targetBag));

        $databasePlatform->assertExecutedQuery('DELETE FROM favorites');
        $databasePlatform->assertExecutedInsertsCount(2);
    }
}
