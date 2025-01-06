<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Query;

use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBagQuery;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(GetTargetBagQuery::class)]
#[CoversClass(GetTargetBag::class)]
#[Small]
class GetTargetBagQueryTest extends TestCase
{
    #[Test]
    public function correctQueryClassIsSet(): void
    {
        $query = new GetTargetBag();

        self::assertSame(GetTargetBagQuery::class, $query->getQueryClass());
    }

    #[Test]
    public function emptyTargetBagIsDelivered(): void
    {
        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch('SELECT * FROM favorites', [], []);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->never())->method('denormalize');

        $query     = new GetTargetBagQuery($denormalizer, $databasePlatform);
        $targetBag = $query->query(new GetTargetBag());

        self::assertCount(0, $targetBag);
    }

    #[Test]
    public function targetBagFromFilesystemIsLoaded(): void
    {
        $dbResult = ['id' => '4c0ad0b6-772d-4ef2-8fd6-8120c90e6e45', 'title' => 'Title 1'];

        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM favorites',
            [],
            [$dbResult],
        );

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with([$dbResult], 'ChronicleKeeper\Favorizer\Domain\ValueObject\Target[]')
            ->willReturn([self::createStub(Target::class)]);

        $query     = new GetTargetBagQuery($denormalizer, $databasePlatform);
        $targetBag = $query->query(new GetTargetBag());

        self::assertCount(1, $targetBag);
    }
}
