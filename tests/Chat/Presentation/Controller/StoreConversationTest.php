<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Chat\Presentation\Controller;

use DZunke\NovDoc\Chat\Presentation\Controller\StoreConversation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(StoreConversation::class)]
#[Large]
class StoreConversationTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/store_conversation');

        self::assertResponseRedirects('/');
    }
}
