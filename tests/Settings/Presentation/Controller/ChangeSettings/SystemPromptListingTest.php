<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings\SystemPromptListing;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SystemPromptListing::class)]
#[Large]
class SystemPromptListingTest extends WebTestCase
{
    #[Test]
    public function itCanShowTheListingPage(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/settings/chatbot_system_prompts');

        self::assertResponseIsSuccessful();

        // Check if one of the system default prompts are shown, so it is clear the listing is visible
        self::assertStringContainsString(
            'Bibliothek - Optimierung von Dokumenten',
            (string) $client->getResponse()->getContent(),
        );
    }
}
