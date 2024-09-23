<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Chat\Presentation\Controller;

use DZunke\NovDoc\Chat\Presentation\Controller\Chat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(Chat::class)]
#[Large]
class ChatTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Schnack mit Rostbart');
    }
}
