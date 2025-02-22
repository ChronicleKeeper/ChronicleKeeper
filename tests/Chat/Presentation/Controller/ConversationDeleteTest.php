<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Command\DeleteConversation;
use ChronicleKeeper\Chat\Application\Command\DeleteConversationHandler;
use ChronicleKeeper\Chat\Application\Command\StoreConversation as StoreConversationCommand;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Presentation\Controller\ConversationDelete;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(ConversationDelete::class)]
#[CoversClass(DeleteConversation::class)]
#[CoversClass(DeleteConversationHandler::class)]
#[Large]
final class ConversationDeleteTest extends WebTestCase
{
    private Conversation $fixturteConversation;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturteConversation = (new ConversationBuilder())
            ->withId('84e66e52-4e06-4f7d-b77e-ac03530deb10')
            ->build();

        $this->bus->dispatch(new StoreConversationCommand($this->fixturteConversation));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->fixturteConversation);
    }

    #[Test]
    public function itWillResponseWithRedirectForUnknownConversation(): void
    {
        $conversationId = '84e66e52-4e06-4f7d-b77e-ac03530deb11';

        $this->client->request(
            Request::METHOD_GET,
            '/chat-delete/' . $conversationId,
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function itWillRedirectToLibraryIfConfirmationIsNotSet(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/chat-delete/' . $this->fixturteConversation->getId(),
        );

        self::assertResponseRedirects('/library');
    }

    #[Test]
    public function itWillRedirectToLibraryAfterDeletion(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/chat-delete/' . $this->fixturteConversation->getId(),
            ['confirm' => 1],
        );

        self::assertResponseRedirects('/library');

        $conversation = $this->databasePlatform->createQueryBuilder()->createSelect()
            ->select('*')
            ->from('conversations')
            ->where('id', '=', $this->fixturteConversation->getId())
            ->fetchOneOrNull();

        self::assertNull($conversation);
    }
}
