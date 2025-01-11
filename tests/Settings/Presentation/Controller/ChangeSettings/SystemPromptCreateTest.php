<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings\SystemPromptCreate;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

use function assert;
use function json_decode;
use function reset;

use const JSON_THROW_ON_ERROR;

#[CoversClass(SystemPromptCreate::class)]
#[Large]
class SystemPromptCreateTest extends WebTestCase
{
    #[Test]
    public function itIsShowingTheEmptyForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/chatbot_system_prompts/create');

        self::assertResponseIsSuccessful();

        $content = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString(
            'Standard (Nur Nutzer)',
            $content,
        );
    }

    #[Test]
    public function itIsCreatingASystemPrompt(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/chatbot_system_prompts/create');

        $this->client->submitForm('Speichern', [
            'system_prompt[name]' => 'Test System Prompt',
            'system_prompt[purpose]' => Purpose::DOCUMENT_OPTIMIZER->value,
            'system_prompt[content]' => 'Test Content',
        ]);

        self::assertResponseRedirects('/settings/chatbot_system_prompts');

        // Check the new System Prompt is stored
        $fileAccess = $this->client->getContainer()->get(FileAccess::class);
        assert($fileAccess instanceof FileAccessDouble);

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
