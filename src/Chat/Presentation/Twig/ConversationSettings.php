<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Chat\Application\ValueObject\Settings;
use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use ChronicleKeeper\Chat\Presentation\Form\ConversationSettingsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

use function assert;

#[AsLiveComponent('Chat:ConversationSettings', template: 'components/chat/conversation-settings.html.twig')]
class ConversationSettings extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    public function __construct(
        private readonly ConversationFileStorage $storage,
    ) {
    }

    #[LiveProp(writable: true, useSerializerForHydration: true)]
    public Conversation $conversation;

    #[LiveProp(writable: true)]
    public bool $isTemporary = false;

    #[PostMount]
    public function loadTemporaryConversation(): void
    {
        if (isset($this->conversation)) {
            return;
        }

        $this->conversation = $this->storage->loadTemporary();
        $this->isTemporary  = true;
    }

    protected function instantiateForm(): FormInterface
    {
        if (! isset($this->conversation)) {
            $this->loadTemporaryConversation();
        }

        return $this->createForm(
            ConversationSettingsType::class,
            $this->conversation->settings,
        );
    }

    #[LiveListener('conversation_updated')]
    public function updateConversation(
        #[LiveArg]
        string $conversationId,
    ): void {
        $conversation = $this->storage->load($conversationId);
        if ($conversation === null) {
            $conversation = $this->storage->loadTemporary();
        }

        $this->conversation = $conversation;
        $this->getForm()->setData($this->conversation);
    }

    #[LiveAction]
    public function store(): Response
    {
        $this->submitForm();
        $settings = $this->getForm()->getData();
        $this->resetForm();

        assert($settings instanceof Settings);

        $this->conversation->settings = $settings;

        if ($this->isTemporary === true) {
            $this->storage->saveTemporary($this->conversation);

            return $this->redirectToRoute('chat');
        }

        $this->storage->store($this->conversation);

        return $this->redirectToRoute('chat', ['conversation' => $this->conversation->id]);
    }
}
