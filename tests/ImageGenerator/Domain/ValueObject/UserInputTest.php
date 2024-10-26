<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Domain\ValueObject;

use ChronicleKeeper\ImageGenerator\Domain\ValueObject\UserInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserInput::class)]
#[Small]
class UserInputTest extends TestCase
{
    #[Test]
    public function objectIsCreatable(): void
    {
        $userInput = new UserInput('foo');

        self::assertSame('foo', $userInput->prompt);
    }
}
