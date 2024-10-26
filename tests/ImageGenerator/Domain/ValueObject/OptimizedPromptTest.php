<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Domain\ValueObject;

use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OptimizedPrompt::class)]
#[Small]
class OptimizedPromptTest extends TestCase
{
    #[Test]
    public function objectIsCreatable(): void
    {
        $optimizedPrompt = new OptimizedPrompt('foo');

        self::assertSame('foo', $optimizedPrompt->prompt);
    }
}
