<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Event;

use ChronicleKeeper\Chat\Application\Event\RegisterChatPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function reset;

#[CoversClass(RegisterChatPrompt::class)]
#[Small]
final class RegisterChatPromptTest extends TestCase
{
    #[Test]
    public function itRegistersAChatPrompt(): void
    {
        $event = new LoadSystemPrompts();
        (new RegisterChatPrompt())($event);

        self::assertCount(1, $event->getPrompts());

        $prompts = $event->getPrompts();
        $prompt  = reset($prompts);
        self::assertSame('309ec7dd-7c18-4f18-99e3-b39ba36383b7', $prompt->getId());
        self::assertSame(Purpose::CONVERSATION, $prompt->getPurpose());
        self::assertSame('Gespräche - Chat Standard Prompt', $prompt->getName());
        self::assertNotEmpty($prompt->getContent());
    }
}
