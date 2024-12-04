<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Query;

use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Image\Application\Query\GetImageQuery;
use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Library\Domain\Entity\ImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

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
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->with('library.images', 'image-id.json')
            ->willReturn('{"id":"image-id"}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('deserialize')
            ->with('{"id":"image-id"}', Image::class, 'json')
            ->willReturn($image = (new ImageBuilder())->build());

        $query = new GetImageQuery($fileAccess, $serializer);

        self::assertSame(
            $image,
            $query->query(new GetImage('image-id')),
        );
    }
}
