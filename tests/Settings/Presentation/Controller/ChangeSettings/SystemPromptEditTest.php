<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings\SystemPromptEdit;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function assert;
use function json_decode;
use function json_encode;
use function reset;

use const JSON_THROW_ON_ERROR;

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

    #[Test]
    public function itIsEditingASystemPrompt(): void
    {
        $fixturePrompt = (new SystemPromptBuilder())->asDefault()->build();

        $client = static::createClient();

        // Check the new System Prompt is stored
        $fileAccess = $client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

        $fileAccess->write(
            'storage',
            'system_prompts.json',
            json_encode([$fixturePrompt->getId() => $fixturePrompt], JSON_THROW_ON_ERROR),
        );

        $client->request(
            Request::METHOD_POST,
            '/settings/chatbot_system_prompts/' . $fixturePrompt->getId(),
            [
                'system_prompt' => [
                    'name' => 'Test System Prompt',
                    'purpose' => Purpose::DOCUMENT_OPTIMIZER->value,
                    'content' => 'Test Content',
                ],
            ],
        );
        self::assertResponseRedirects('/settings/chatbot_system_prompts');

        $files = $fileAccess->allOfType('storage');
        self::assertCount(1, $files);

        $systemPrompt = reset($files);
        $systemPrompt = json_decode($systemPrompt, true, 512, JSON_THROW_ON_ERROR);
        $systemPrompt = reset($systemPrompt);

        self::assertSame('Test System Prompt', $systemPrompt['name']);
        self::assertSame('Test Content', $systemPrompt['content']);
        self::assertSame(Purpose::DOCUMENT_OPTIMIZER->value, $systemPrompt['purpose']);
        self::assertFalse($systemPrompt['isDefault']);
        self::assertFalse($systemPrompt['isSystem']);
    }
}
