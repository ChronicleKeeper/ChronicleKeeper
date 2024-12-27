<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Event;

use ChronicleKeeper\Document\Application\Event\RegisterDocumentOptimizerPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function reset;

#[CoversClass(RegisterDocumentOptimizerPrompt::class)]
#[Small]
final class RegisterDocumentOptimizerPromptTest extends TestCase
{
    #[Test]
    public function itRegistersAChatPrompt(): void
    {
        $event = new LoadSystemPrompts();
        (new RegisterDocumentOptimizerPrompt())($event);

        self::assertCount(1, $event->getPrompts());

        $prompts = $event->getPrompts();
        $prompt  = reset($prompts);
        self::assertSame('b1e1eb26-9460-4722-9704-8e7b068a8b5a', $prompt->getId());
        self::assertSame(Purpose::DOCUMENT_OPTIMIZER, $prompt->getPurpose());
        self::assertSame('Bibliothek - Optimierung von Dokumenten', $prompt->getName());
        self::assertNotEmpty($prompt->getContent());
    }
}
