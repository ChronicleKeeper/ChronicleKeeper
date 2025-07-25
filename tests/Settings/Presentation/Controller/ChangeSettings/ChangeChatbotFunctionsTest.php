<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\ChangeSettings;

use ChronicleKeeper\Settings\Presentation\Controller\ChangeSettings\ChangeChatbotFunctions;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(ChangeChatbotFunctions::class)]
#[Large]
class ChangeChatbotFunctionsTest extends WebTestCase
{
    #[Test]
    public function itIsShowingTheForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/chatbot_functions');

        self::assertResponseIsSuccessful();

        $content = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString('calendar_date_calculator', $content);
        self::assertStringContainsString('library_documents', $content);
        self::assertStringContainsString('library_images', $content);
        self::assertStringContainsString('world_items', $content);
    }
}
