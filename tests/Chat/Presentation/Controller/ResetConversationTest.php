<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Chat\Presentation\Controller;

use DZunke\NovDoc\Chat\Presentation\Controller\ResetConversation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(ResetConversation::class)]
#[Large]
class ResetConversationTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset_conversation');

        self::assertResponseRedirects('/');
    }
}
