<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain\ValueObject;

use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(LibraryImageTarget::class)]
#[Small]
class LibraryImageTargetTest extends TestCase
{
    #[Test]
    public function instantiateWithValidData(): void
    {
        $target = new LibraryImageTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Image Title',
        );

        self::assertSame('f3ce2cce-888d-4812-8470-72cdd96faf4c', $target->getId());
        self::assertSame('Image Title', $target->getTitle());
    }

    #[Test]
    public function instantiateWithInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid" is not a valid UUID.');

        new LibraryImageTarget('invalid', 'Image Title');
    }

    #[Test]
    public function instantiateWithEmptyTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-empty value. Got: ""');

        new LibraryImageTarget('f3ce2cce-888d-4812-8470-72cdd96faf4c', '');
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $target = new LibraryImageTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Image Title',
        );

        self::assertSame(
            [
                'type' => 'LibraryImageTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Image Title',
            ],
            $target->jsonSerialize(),
        );
    }
}
