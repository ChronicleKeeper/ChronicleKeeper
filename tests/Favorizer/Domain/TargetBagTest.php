<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain;

use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TargetBag::class)]
#[Small]
class TargetBagTest extends TestCase
{
    #[Test]
    public function checkForExistingEntrty(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag($target);

        self::assertTrue($targetBag->exists(clone $target));
    }

    #[Test]
    public function removeExistingEntry(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag($target);
        $targetBag->remove(clone $target);

        self::assertFalse($targetBag->exists(clone $target));
    }

    #[Test]
    public function removeNonExistingEntry(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag($target);
        $targetBag->remove(clone $target);

        self::assertFalse($targetBag->exists(clone $target));
    }

    #[Test]
    public function appendEntry(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag();
        $targetBag->append(clone $target);

        self::assertTrue($targetBag->exists(clone $target));
    }

    #[Test]
    public function itIsSerializable(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag($target);

        self::assertSame(
            [$target],
            $targetBag->jsonSerialize(),
        );
    }

    #[Test]
    public function replaceExistingEntry(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $updatedTarget = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Updated Chat Conversation',
        );

        $targetBag = new TargetBag($target);
        $targetBag->replace($updatedTarget);

        self::assertTrue($targetBag->exists($updatedTarget));
        self::assertSame(
            [$updatedTarget],
            $targetBag->jsonSerialize(),
        );
    }

    #[Test]
    public function replaceNonExistingEntry(): void
    {
        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag();
        $targetBag->replace($target);

        self::assertTrue($targetBag->exists($target));
        self::assertSame(
            [$target],
            $targetBag->jsonSerialize(),
        );
    }
}
