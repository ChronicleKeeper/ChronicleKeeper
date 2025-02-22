<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Command\StoreConversation as StoreConversationCommand;
use ChronicleKeeper\Chat\Application\Command\StoreConversationHandler;
use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversationHandler;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdQuery;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationQuery;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Chat\Presentation\Form\ConversationSettingsType;
use ChronicleKeeper\Chat\Presentation\Twig\ConversationSettings;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(ConversationSettings::class)]
#[CoversClass(GetTemporaryConversationParameters::class)]
#[CoversClass(GetTemporaryConversationQuery::class)]
#[CoversClass(ConversationSettingsType::class)]
#[CoversClass(FindConversationByIdParameters::class)]
#[CoversClass(FindConversationByIdQuery::class)]
#[CoversClass(StoreTemporaryConversation::class)]
#[CoversClass(StoreTemporaryConversationHandler::class)]
#[CoversClass(StoreConversationCommand::class)]
#[CoversClass(StoreConversationHandler::class)]
#[Large]
final class ConversationSettingsTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    private Conversation $conversation;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->conversation = (new ConversationBuilder())
            ->withId('4864e4ad-04db-4757-91c2-4bd25232d461')
            ->withSettings(new Settings('gpt-4o', 0.1, 0.2, 0.3))
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
    public function itRendersCorrectlyForTemporaryConversation(): void
    {
        $component = $this->createLiveComponent(
            name: ConversationSettings::class,
            data: ['isTemporary' => true],
        );

        $renderedComponent = $component->render();
        $crawler           = $renderedComponent->crawler();

        self::assertSame('Einstellungen', $crawler->filter('h3.modal-title')->text());
        self::assertSame('Speichern', $crawler->filter('button.btn-primary')->text());

        $formFields = $crawler->filter('form input, form select')->each(static fn ($node) => $node->attr('id'));

        self::assertSame(
            [
                'conversation_settings_version',
                'conversation_settings_temperature',
                'conversation_settings_imagesMaxDistance',
                'conversation_settings_documentsMaxDistance',
            ],
            $formFields,
        );

        $formValues = $crawler->filter('form input, form select')->each(static function ($node) {
            if ($node->nodeName() === 'select') {
                return $node->filter('option[selected]')->attr('value');
            }

            return $node->attr('value');
        });

        self::assertSame(['gpt-4o-mini', '0,70', '0,85', '0,85'], $formValues);
    }

    #[Test]
    public function itRendersCorrectlyForExistingConversation(): void
    {
        $component = $this->createLiveComponent(
            name: ConversationSettings::class,
            data: ['conversation' => $this->conversation],
        );

        $renderedComponent = $component->render();
        $crawler           = $renderedComponent->crawler();

        self::assertSame('Einstellungen', $crawler->filter('h3.modal-title')->text());
        self::assertSame('Speichern', $crawler->filter('button.btn-primary')->text());

        $formFields = $crawler->filter('form input, form select')->each(static fn ($node) => $node->attr('id'));

        self::assertSame(
            [
                'conversation_settings_version',
                'conversation_settings_temperature',
                'conversation_settings_imagesMaxDistance',
                'conversation_settings_documentsMaxDistance',
            ],
            $formFields,
        );

        $formValues = $crawler->filter('form input, form select')->each(static function ($node) {
            if ($node->nodeName() === 'select') {
                return $node->filter('option[selected]')->attr('value');
            }

            return $node->attr('value');
        });

        self::assertSame(['gpt-4o', '0,10', '0,20', '0,30'], $formValues);
    }

    #[Test]
    public function itCanSaveTemporaryConversationSettings(): void
    {
        $component = $this->createLiveComponent(
            name: ConversationSettings::class,
            data: ['isTemporary' => true],
        );

        $component->submitForm([
            'conversation_settings' => [
                'version' => 'gpt-4o',
                'temperature' => '0.1',
                'imagesMaxDistance' => '0.2',
                'documentsMaxDistance' => '0.3',
            ],
        ]);

        $component->call('store');

        self::assertTrue($component->response()->isRedirect('/chat'));
    }

    #[Test]
    public function itCanSaveExistingConversationSettings(): void
    {
        $component = $this->createLiveComponent(
            name: ConversationSettings::class,
            data: ['conversation' => $this->conversation],
        );

        $component->submitForm([
            'conversation_settings' => [
                'version' => 'gpt-4o',
                'temperature' => '0.1',
                'imagesMaxDistance' => '0.2',
                'documentsMaxDistance' => '0.3',
            ],
        ]);

        $component->call('store');

        self::assertTrue($component->response()->isRedirect('/chat/' . $this->conversation->getId()));
    }
}
