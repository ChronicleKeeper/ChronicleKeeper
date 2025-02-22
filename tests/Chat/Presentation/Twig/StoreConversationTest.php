<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Command\StoreConversation as StoreConversationCommand;
use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdQuery;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationQuery;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Presentation\Form\StoreConversationType;
use ChronicleKeeper\Chat\Presentation\Twig\StoreConversation;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(StoreConversation::class)]
#[CoversClass(FindConversationByIdParameters::class)]
#[CoversClass(FindConversationByIdQuery::class)]
#[CoversClass(GetTemporaryConversationParameters::class)]
#[CoversClass(GetTemporaryConversationQuery::class)]
#[CoversClass(StoreConversationType::class)]
#[CoversClass(StoreConversationCommand::class)]
#[CoversClass(StoreTemporaryConversation::class)]
#[Large]
final class StoreConversationTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    private Conversation $conversation;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->conversation = (new ConversationBuilder())
            ->withId('4864e4ad-04db-4757-91c2-4bd25232d461')
            ->withTitle('Mein Gespräch')
            ->build();

        $this->bus->dispatch(new StoreConversationCommand($this->conversation));
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->conversation);
    }

    #[Test]
    public function itRendersCorrectlyForAnExistingConversation(): void
    {
        $component = $this->createLiveComponent(
            name: StoreConversation::class,
            data: [
                'conversationId' => $this->conversation->getId(),
                'isTemporary'    => false,
            ],
        );

        $renderedComponent = $component->render();
        $crawler           = $renderedComponent->crawler();

        self::assertSame('Gespräch Speichern', $crawler->filter('h3.modal-title')->text());
        self::assertSame('Speichern', $crawler->filter('button.btn-primary')->text());

        $formFields = $crawler->filter('form input, form select')->each(static fn ($node) => $node->attr('id'));

        self::assertSame(
            [
                'store_conversation_title',
                'store_conversation_directory',
            ],
            $formFields,
        );

        $formValues = $crawler->filter('form input, form select')->each(static function ($node) {
            if ($node->nodeName() === 'select') {
                return $node->filter('option[selected]')->attr('value');
            }

            return $node->attr('value');
        });

        self::assertSame(
            [
                'Mein Gespräch',
                'caf93493-9072-44e2-a6db-4476985a849d',
            ],
            $formValues,
        );
    }

    #[Test]
    public function itRendersCorrectlyForATemporaryConversation(): void
    {
        $component = $this->createLiveComponent(
            name: StoreConversation::class,
            data: [
                'conversationId' => null,
                'isTemporary'    => true,
            ],
        );

        $renderedComponent = $component->render();
        $crawler           = $renderedComponent->crawler();

        self::assertSame('Gespräch Speichern', $crawler->filter('h3.modal-title')->text());
        self::assertSame('Speichern', $crawler->filter('button.btn-primary')->text());

        $formFields = $crawler->filter('form input, form select')->each(static fn ($node) => $node->attr('id'));

        self::assertSame(
            [
                'store_conversation_title',
                'store_conversation_directory',
            ],
            $formFields,
        );

        $formValues = $crawler->filter('form input, form select')->each(static function ($node) {
            if ($node->nodeName() === 'select') {
                return $node->filter('option[selected]')->attr('value');
            }

            return $node->attr('value');
        });

        self::assertSame(
            [
                'Unbekanntes Gespräch',
                'caf93493-9072-44e2-a6db-4476985a849d',
            ],
            $formValues,
        );
    }

    #[Test]
    public function itStoresAnExistingConversation(): void
    {
        $component = $this->createLiveComponent(
            name: StoreConversation::class,
            data: [
                'conversationId' => $this->conversation->getId(),
                'isTemporary'    => false,
            ],
        );

        $component->set('store_conversation_title', 'Mein neues Gespräch');
        $component->set('store_conversation_directory', 'caf93493-9072-44e2-a6db-4476985a849d');
        $component->call('store');

        self::assertTrue($component->response()->isRedirect('/chat/4864e4ad-04db-4757-91c2-4bd25232d461'));
    }

    #[Test]
    public function itStoresATemporaryConversation(): void
    {
        $component = $this->createLiveComponent(
            name: StoreConversation::class,
            data: [
                'conversationId' => null,
                'isTemporary'    => true,
            ],
        );

        $component->set('store_conversation_title', 'Mein neues Gespräch');
        $component->set('store_conversation_directory', 'caf93493-9072-44e2-a6db-4476985a849d');
        $component->call('store');

        self::assertTrue($component->response()->isRedirect());
    }
}
