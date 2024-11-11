<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Application\Command;

use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBagHandler;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

#[CoversClass(StoreTargetBag::class)]
#[CoversClass(StoreTargetBagHandler::class)]
#[Small]
class StoreTargetBagHandlerTest extends TestCase
{
    #[Test]
    public function storeTargetBag(): void
    {
        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('write')
            ->with(
                'storage',
                'favorites.json',
                '{"targetBag":"targetBag"}',
            );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(
                self::isInstanceOf(TargetBag::class),
                'json',
                ['json_encode_options' => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT],
            )
            ->willReturn('{"targetBag":"targetBag"}');

        $handler = new StoreTargetBagHandler($fileAccess, $serializer);
        $handler(new StoreTargetBag(new TargetBag()));
    }
}
