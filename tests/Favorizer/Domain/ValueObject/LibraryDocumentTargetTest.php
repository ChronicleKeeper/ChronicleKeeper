<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain\ValueObject;

use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(LibraryDocumentTarget::class)]
#[Small]
class LibraryDocumentTargetTest extends TestCase
{
    #[Test]
    public function instantiateWithValidData(): void
    {
        $target = new LibraryDocumentTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Document Title',
        );

        self::assertSame('f3ce2cce-888d-4812-8470-72cdd96faf4c', $target->getId());
        self::assertSame('Document Title', $target->getTitle());
    }

    #[Test]
    public function instantiateWithInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value "invalid" is not a valid UUID.');

        new LibraryDocumentTarget('invalid', 'Document Title');
    }

    #[Test]
    public function instantiateWithEmptyTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-empty value. Got: ""');

        new LibraryDocumentTarget('f3ce2cce-888d-4812-8470-72cdd96faf4c', '');
    }

    #[Test]
    public function jsonSerialize(): void
    {
        $target = new LibraryDocumentTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Document Title',
        );

        self::assertSame(
            [
                'type' => 'LibraryDocumentTarget',
                'id' => 'f3ce2cce-888d-4812-8470-72cdd96faf4c',
                'title' => 'Document Title',
            ],
            $target->jsonSerialize(),
        );
    }
}
