<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings\SystemPromptEdit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SystemPromptEdit::class)]
#[Large]
class SystemPromptEditTest extends WebTestCase
{
    #[Test]
    public function itShowsTheReadonlyViewOfASystemPrompt(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/settings/chatbot_system_prompts/309ec7dd-7c18-4f18-99e3-b39ba36383b7');

        self::assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();

        // Check if one of the system default prompts are shown, so it is clear the listing is visible
        self::assertStringContainsString(
            'Gespräche - Chat Standard Prompt',
            $content,
        );

        // There is no save button on the readonly view
        self::assertStringNotContainsString('Speichern', $content);
    }
}
