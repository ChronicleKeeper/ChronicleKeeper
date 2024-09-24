<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Infrastructure\Repository\Conversation\Storage;
use ChronicleKeeper\Chat\Presentation\Controller\LoadConversation;
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
