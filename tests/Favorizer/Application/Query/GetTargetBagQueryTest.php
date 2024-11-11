<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Query;

use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBagQuery;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

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
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->with('storage', 'favorites.json')
            ->willThrowException(new UnableToReadFile('favorites.json'));

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->never())->method('deserialize');

        $query     = new GetTargetBagQuery($fileAccess, $serializer);
        $targetBag = $query->query(new GetTargetBag());

        self::assertCount(0, $targetBag);
    }

    #[Test]
    public function targetBagFromFilesystemIsLoaded(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->with('storage', 'favorites.json')
            ->willReturn('{"targetBag":[]}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('deserialize')
            ->with('{"targetBag":[]}', 'ChronicleKeeper\Favorizer\Domain\ValueObject\Target[]', 'json')
            ->willReturn([self::createStub(Target::class)]);

        $query     = new GetTargetBagQuery($fileAccess, $serializer);
        $targetBag = $query->query(new GetTargetBag());

        self::assertCount(1, $targetBag);
    }
}
