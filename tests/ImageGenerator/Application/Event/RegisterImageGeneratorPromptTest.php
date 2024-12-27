<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Event;

use ChronicleKeeper\ImageGenerator\Application\Event\RegisterImageGeneratorPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function reset;

#[CoversClass(RegisterImageGeneratorPrompt::class)]
#[Small]
final class RegisterImageGeneratorPromptTest extends TestCase
{
    #[Test]
    public function itRegistersAChatPrompt(): void
    {
        $event = new LoadSystemPrompts();
        (new RegisterImageGeneratorPrompt())($event);

        self::assertCount(1, $event->getPrompts());

        $prompts = $event->getPrompts();
        $prompt  = reset($prompts);
        self::assertSame('a570542f-a914-4338-a6ca-26b169c5560c', $prompt->getId());
        self::assertSame(Purpose::IMAGE_GENERATOR_OPTIMIZER, $prompt->getPurpose());
        self::assertSame('Bildgenerator - Informationssuche', $prompt->getName());
        self::assertNotEmpty($prompt->getContent());
    }
}
