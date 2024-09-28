<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use ChronicleKeeper\Chat\Presentation\Form\StoreConversationType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

use function assert;

#[AsLiveComponent('Chat:StoreConversation', template: 'components/chat/store-conversation.html.twig')]
class StoreConversation extends AbstractController
{
    use HandleFlashMessages;
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function __construct(
        private readonly ConversationFileStorage $storage,
    ) {
    }

    #[LiveProp(writable: true, useSerializerForHydration: true)]
    public Conversation $conversation;

    #[PostMount]
    public function loadTemporaryConversation(): void
    {
        if (isset($this->conversation)) {
            return;
        }

        $this->conversation = $this->storage->loadTemporary();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(StoreConversationType::class, $this->conversation ?? Conversation::createEmpty());
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
    public function store(Request $request): Response
    {
        $this->submitForm();
        $conversationData = $this->getForm()->getData();
        $this->resetForm();

        assert($conversationData instanceof Conversation);

        $this->storage->store($conversationData);
        $this->storage->resetTemporary();

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Die Unterhaltung wurde erfolgreich in der Bibliothek gespeichert.',
        );

        return $this->redirectToRoute('chat', ['conversationId' => $this->conversation->id]);
    }
}
