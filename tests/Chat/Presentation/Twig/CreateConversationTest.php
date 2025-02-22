<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Command\ResetTemporaryConversation;
use ChronicleKeeper\Chat\Application\Command\ResetTemporaryConversationHandler;
use ChronicleKeeper\Chat\Presentation\Twig\CreateConversation;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;

#[CoversClass(CreateConversation::class)]
#[CoversClass(ResetTemporaryConversation::class)]
#[CoversClass(ResetTemporaryConversationHandler::class)]
#[Large]
final class CreateConversationTest extends WebTestCase
{
    use InteractsWithLiveComponents;

    #[Test]
    public function itRendersCorrectly(): void
    {
        $component = $this->createLiveComponent(CreateConversation::class);

        $renderedComponent = $component->render();
        $crawler           = $renderedComponent->crawler();

        self::assertSame('Neues Gespräch', $crawler->filter('h3.modal-title')->text());
        self::assertSame('Speichern', $crawler->filter('button.btn-primary')->text());

        $formFields = $crawler->filter('form input, form select')->each(static fn ($node) => $node->attr('id'));

        self::assertSame(
            [
                'create_conversation_title',
                'create_conversation_utilize_prompt',
            ],
            $formFields,
        );

        $formValues = $crawler->filter('form input, form select')->each(static function ($node) {
            if ($node->nodeName() === 'select') {
                return $node->filter('option[selected]')->attr('value');
            }

            return $node->attr('value');
        });

        self::assertSame(['Neues Gespräch', '309ec7dd-7c18-4f18-99e3-b39ba36383b7'], $formValues);
    }

    #[Test]
    public function itResetsTheConversation(): void
    {
        $component = $this->createLiveComponent(CreateConversation::class);
        $component->set('create_conversation_title', 'Test Conversation');
        $component->set('create_conversation_utilize_prompt', '309ec7dd-7c18-4f18-99e3-b39ba36383b7');
        $component->call('store');

        self::assertTrue($component->response()->isRedirect('/chat'));
    }
}
