<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Presentation\Controller\Chat;
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
        self::assertSelectorTextContains('h2', 'Schnack mit Chronicle Keeper');
    }
}
