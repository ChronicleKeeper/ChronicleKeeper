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
        $query = new GetImage('image-id');

        self::assertSame(GetImageQuery::class, $query->getQueryClass());
        self::assertSame('image-id', $query->id);
    }

    #[Test]
    public function theQueryIsExectuted(): void
    {
        $databasePlatform = new DatabasePlatformMock();
        $databasePlatform->expectFetch(
            'SELECT * FROM images WHERE id = :id',
            ['id' => 'image-id'],
            [['id' => 'image-id']],
        );

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['id' => 'image-id'], Image::class)
            ->willReturn($image = (new ImageBuilder())->build());

        $query = new GetImageQuery($databasePlatform, $denormalizer);

        self::assertSame(
            $image,
            $query->query(new GetImage('image-id')),
        );
    }
}
