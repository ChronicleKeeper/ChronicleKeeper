<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Query;

use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Image\Application\Query\GetImageQuery;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Database\DatabasePlatformMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(GetImage::class)]
#[CoversClass(GetImageQuery::class)]
#[Small]
class GetImageTest extends TestCase
{
    #[Test]
    public function queryIsCorrect(): void
    {
        $query = new GetImage('b22e89e8-54b0-4abd-b8d2-8a4c7f5c3150');

        self::assertSame(GetImageQuery::class, $query->getQueryClass());
        self::assertSame('b22e89e8-54b0-4abd-b8d2-8a4c7f5c3150', $query->id);
    }

    #[Test]
    public function theQueryIsExectuted(): void
    {
        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM images WHERE id = :id',
            ['id' => '0db8d4df-3da3-4781-b07f-2e42ac103820'],
            [['id' => '0db8d4df-3da3-4781-b07f-2e42ac103820']],
        );

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['id' => '0db8d4df-3da3-4781-b07f-2e42ac103820'], Image::class)
            ->willReturn($image = (new ImageBuilder())->build());

        $query = new GetImageQuery($databasePlatform, $denormalizer);

        self::assertSame(
            $image,
            $query->query(new GetImage('0db8d4df-3da3-4781-b07f-2e42ac103820')),
        );
    }
}
