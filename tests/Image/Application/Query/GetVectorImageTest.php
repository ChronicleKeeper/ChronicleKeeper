<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Application\Query;

use ChronicleKeeper\Image\Application\Query\GetVectorImage;
use ChronicleKeeper\Image\Application\Query\GetVectorImageQuery;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Image\Domain\Entity\VectorImageBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(GetVectorImage::class)]
#[CoversClass(GetVectorImageQuery::class)]
#[Small]
class GetVectorImageTest extends TestCase
{
    #[Test]
    public function queryIsCorrect(): void
    {
        $query = new GetVectorImage('image-id');

        self::assertSame(GetVectorImageQuery::class, $query->getQueryClass());
        self::assertSame('image-id', $query->id);
    }

    #[Test]
    public function theQueryIsExectuted(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('read')
            ->with('vector.images', 'image-id.json')
            ->willReturn('{"id":"image-id"}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('deserialize')
            ->with('{"id":"image-id"}', VectorImage::class, 'json')
            ->willReturn($vectorImage = (new VectorImageBuilder())->build());

        $query = new GetVectorImageQuery($fileAccess, $serializer);

        self::assertSame(
            $vectorImage,
            $query->query(new GetVectorImage('image-id')),
        );
    }
}
