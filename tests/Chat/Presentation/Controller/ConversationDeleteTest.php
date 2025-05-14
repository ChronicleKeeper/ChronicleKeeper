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
    private Conversation $fixtureConversation;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureConversation = (new ConversationBuilder())
            ->withId('84e66e52-4e06-4f7d-b77e-ac03530deb10')
            ->build();

        $this->bus->dispatch(new StoreConversationCommand($this->fixtureConversation));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->fixtureConversation);
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
            '/chat-delete/' . $this->fixtureConversation->getId(),
        );

        self::assertResponseRedirects('/library');
    }

    #[Test]
    public function itWillRedirectToLibraryAfterDeletion(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/chat-delete/' . $this->fixtureConversation->getId(),
            ['confirm' => 1],
        );

        self::assertResponseRedirects('/library');

        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('conversations')
            ->where('id = :id')
            ->setParameter('id', $this->fixtureConversation->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertFalse($result);
    }
}
