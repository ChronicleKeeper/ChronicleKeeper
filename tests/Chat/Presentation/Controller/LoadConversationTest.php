<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Chat\Presentation\Controller;

use DZunke\NovDoc\Chat\Infrastructure\Repository\Conversation\Storage;
use DZunke\NovDoc\Chat\Presentation\Controller\LoadConversation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use function assert;

#[CoversClass(LoadConversation::class)]
#[Large]
class LoadConversationTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $client = static::createClient();

        // Reset the storage ... just in case
        $storage = $client->getContainer()->get(Storage::class);
        assert($storage instanceof Storage);
        $storage->reset();

        $client->request('GET', '/load_conversation');

        self::assertResponseRedirects('/');
    }
}
