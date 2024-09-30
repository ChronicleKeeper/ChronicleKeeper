<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Chat\Application\Service\ChatMessageExecution;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use ChronicleKeeper\Chat\Presentation\Twig\Chat\ExtendedMessageBagToViewConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('Chat:Chat', template: 'components/chat/chat.html.twig')]
class Chat extends AbstractController
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true, useSerializerForHydration: true)]
    public Conversation $conversation;

    #[LiveProp]
    public string $message = '';

    #[LiveProp(writable: true)]
    public bool $isTemporary = false;

    public function __construct(
        private readonly ConversationFileStorage $storage,
        private readonly ExtendedMessageBagToViewConverter $messageBagToViewConverter,
        private readonly ChatMessageExecution $chatMessageExecution,
    ) {
    }

    #[PostMount]
    public function loadTemporaryConversation(): void
    {
        if (isset($this->conversation)) {
            return;
        }

        $this->conversation = $this->storage->loadTemporary();
        $this->isTemporary  = true;
    }

    /** @return list<array{role: string, message: string, extended: ExtendedMessage}> */
    public function getMessages(): array
    {
        return $this->messageBagToViewConverter->convert($this->conversation->messages);
    }

    #[LiveAction]
    public function submit(
        #[LiveArg]
        string $message,
    ): void {
        $this->chatMessageExecution->execute($message, $this->conversation);

        if ($this->isTemporary === true) {
            $this->storage->saveTemporary($this->conversation);

            $this->emit('conversation_updated', ['conversationId' => $this->conversation->id]);

            return;
        }

        $this->storage->store($this->conversation);

        $this->emit('conversation_updated', ['conversationId' => $this->conversation->id]);
    }
}
