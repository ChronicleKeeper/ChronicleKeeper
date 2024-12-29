<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\Event;

use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoadSystemPrompts::class)]
#[Small]
final class LoadSystemPromptsTest extends TestCase
{
    #[Test]
    public function itCreatesAnEmptyEvent(): void
    {
        $event = new LoadSystemPrompts();

        self::assertEmpty($event->getPrompts());
    }

    #[Test]
    public function itAddsAPrompt(): void
    {
        $event = new LoadSystemPrompts();
        $event->add($prompt = (new SystemPromptBuilder())->build());

        self::assertSame([$prompt->getId() => $prompt], $event->getPrompts());
    }

    #[Test]
    public function itAddsMultiplePrompts(): void
    {
        $event = new LoadSystemPrompts();
        $event->add($prompt1 = (new SystemPromptBuilder())->build());
        $event->add($prompt2 = (new SystemPromptBuilder())->build());

        self::assertSame([$prompt1->getId() => $prompt1, $prompt2->getId() => $prompt2], $event->getPrompts());
    }
}
