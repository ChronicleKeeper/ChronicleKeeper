<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain\Exception;

use ChronicleKeeper\Favorizer\Domain\Exception\UnknownMedium;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnknownMedium::class)]
#[Small]
class UnknownMediumTest extends TestCase
{
    #[Test]
    public function forTypeContentIsCorrect(): void
    {
        $exception = UnknownMedium::forType('foo');

        self::assertSame('Unknown target medium of type "foo"', $exception->getMessage());
    }

    #[Test]
    public function notFoundContentIsCorrect(): void
    {
        $exception = UnknownMedium::notFound('foo', 'bar');

        self::assertSame('Target mediumg of type "bar" with id "foo" not found.', $exception->getMessage());
    }
}
