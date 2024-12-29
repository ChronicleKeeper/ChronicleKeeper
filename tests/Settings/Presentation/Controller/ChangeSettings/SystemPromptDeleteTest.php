<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings\SystemPromptDelete;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use ChronicleKeeper\Test\Shared\Infrastructure\Persistence\Filesystem\FileAccessDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function assert;
use function json_encode;
use function reset;

use const JSON_THROW_ON_ERROR;

#[CoversClass(SystemPromptDelete::class)]
#[Large]
class SystemPromptDeleteTest extends WebTestCase
{
    #[Test]
    public function itWillRedirectToSystemPromptsIfNoConfirmation(): void
    {
        $client = static::createClient();
        $client->request(
            Request::METHOD_GET,
            '/settings/chatbot_system_prompts/8328a2ab-1924-47b2-9c38-bf5018fdcd18/delete',
        );

        self::assertResponseRedirects('/settings/chatbot_system_prompts');
    }

    #[Test]
    public function itWillDeleteSystemPrompt(): void
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
            Request::METHOD_GET,
            '/settings/chatbot_system_prompts/' . $fixturePrompt->getId() . '/delete?confirm=true',
        );

        self::assertResponseRedirects('/settings/chatbot_system_prompts');

        $files = $fileAccess->allOfType('storage');
        self::assertCount(1, $files);

        $systemPrompt = reset($files);

        self::assertSame('[]', $systemPrompt);
    }
}
