<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\ValueObject;

use ChronicleKeeper\Chat\Domain\ValueObject\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ValueError;

#[CoversClass(Role::class)]
#[Small]
class RoleTest extends TestCase
{
    #[Test]
    public function itCanBeCreatedFromValidString(): void
    {
        // Test that from() method works without throwing
        Role::from('system');
        Role::from('user');
        Role::from('assistant');

        // If we reach here, all Role::from() calls succeeded
        $this->addToAssertionCount(3);
    }

    #[Test]
    public function itThrowsExceptionForInvalidString(): void
    {
        $this->expectException(ValueError::class);
        Role::from('invalid');
    }

    #[Test]
    public function itListsAllCases(): void
    {
        $cases = Role::cases();

        self::assertContains(Role::SYSTEM, $cases);
        self::assertContains(Role::USER, $cases);
        self::assertContains(Role::ASSISTANT, $cases);
    }
}
