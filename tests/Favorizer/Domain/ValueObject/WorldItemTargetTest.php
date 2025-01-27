<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain\ValueObject;

use ChronicleKeeper\Favorizer\Domain\ValueObject\WorldItemTarget;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(WorldItemTarget::class)]
#[Small]
class WorldItemTargetTest extends TestCase
{
    #[Test]
    public function instantiateWithValidData(): void
    {
        $target = new WorldItemTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Example Item',
        );

        self::assertSame('f3ce2cce-888d-4812-8470-72cdd96faf4c', $target->getId());
        self::assertSame('Example Item', $target->getTitle());
    }

    #[Test]
    public function instantiateWithInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid" is not a valid UUID.');

        new WorldItemTarget('invalid', 'Image Title');
    }

    #[Test]
    public function instantiateWithEmptyTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-empty value. Got: ""');

        new WorldItemTarget('f3ce2cce-888d-4812-8470-72cdd96faf4c', '');
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $target = new WorldItemTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Example Item',
        );

        self::assertSame(
            [
                'type' => 'WorldItemTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Example Item',
            ],
            $target->jsonSerialize(),
        );
    }
}
