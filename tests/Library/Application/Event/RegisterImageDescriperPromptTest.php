<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Event;

use ChronicleKeeper\Library\Application\Event\RegisterImageDescriperPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function reset;

#[CoversClass(RegisterImageDescriperPrompt::class)]
#[Small]
final class RegisterImageDescriperPromptTest extends TestCase
{
    #[Test]
    public function itRegistersAChatPrompt(): void
    {
        $event = new LoadSystemPrompts();
        (new RegisterImageDescriperPrompt())($event);

        self::assertCount(1, $event->getPrompts());

        $prompts = $event->getPrompts();
        $prompt  = reset($prompts);
        self::assertSame('e0642877-c910-4d33-9011-ba7d1affba1b', $prompt->getId());
        self::assertSame(Purpose::IMAGE_UPLOAD, $prompt->getPurpose());
        self::assertSame('Bibliothek - Bildbeschreibung beim Hochladen', $prompt->getName());
        self::assertNotEmpty($prompt->getContent());
    }
}
