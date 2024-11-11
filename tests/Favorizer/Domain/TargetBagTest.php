<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Favorizer\Domain;

use ChronicleKeeper\Favorizer\Domain\Exception\MaxTargetsInBag;
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
    public function appendEntryToFullBagUtilizingAppendMethod(): void
    {
        $this->expectException(MaxTargetsInBag::class);
        $this->expectExceptionMessage('The target bag reached the maximum number of targets (10).');

        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag();
        for ($i = 0; $i < 10; $i++) {
            $targetBag->append(clone $target);
        }

        self::assertCount(10, $targetBag);

        $targetBag->append(clone $target);
    }

    #[Test]
    public function appendEntryToFullBagUtilizingOffsetSetMethod(): void
    {
        $this->expectException(MaxTargetsInBag::class);
        $this->expectExceptionMessage('The target bag reached the maximum number of targets (10).');

        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag();
        for ($i = 0; $i < 10; $i++) {
            $targetBag->offsetSet(null, clone $target);
        }

        self::assertCount(10, $targetBag);

        $targetBag->offsetSet(null, clone $target);
    }

    #[Test]
    public function appendEntryToFullBagUtilizingArrayAccess(): void
    {
        $this->expectException(MaxTargetsInBag::class);
        $this->expectExceptionMessage('The target bag reached the maximum number of targets (10).');

        $target = new ChatConversationTarget(
            'f3ce2cce-888d-4812-8470-72cdd96faf4c',
            'Chat Conversation',
        );

        $targetBag = new TargetBag();
        for ($i = 0; $i < 10; $i++) {
            $targetBag[] = clone $target;
        }

        self::assertCount(10, $targetBag);

        $targetBag[] = clone $target;
    }
}
