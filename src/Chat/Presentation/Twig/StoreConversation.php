<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Command\StoreConversation as StoreConversationCommand;
use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Presentation\Form\StoreConversationType;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
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
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[LiveProp(writable: true)]
    public string|null $conversationId = null;
    #[LiveProp]
    public bool $isTemporary           = true;

    private Conversation $conversation;

    #[PostMount]
    public function loadConversation(): void
    {
        if (isset($this->conversation)) {
            return;
        }

        if ($this->conversationId !== null) {
            $this->isTemporary  = false;
            $this->conversation = $this->queryService->query(new FindConversationByIdParameters($this->conversationId));

            return;
        }

        // Load temporary when there is no identifier for an existing conversation
        $this->isTemporary  = true;
        $this->conversation = $this->queryService->query(new GetTemporaryConversationParameters());
    }

    protected function instantiateForm(): FormInterface
    {
        if (! isset($this->conversation)) {
            $this->loadConversation();
        }

        return $this->createForm(StoreConversationType::class, $this->conversation);
    }

    #[LiveAction]
    public function store(Request $request): Response
    {
        $this->isTemporary = $this->conversationId === null;

        $this->submitForm();
        $conversationData = $this->getForm()->getData();
        $this->resetForm();

        assert($conversationData instanceof Conversation);

        if ($this->isTemporary === true) {
            $conversationData = Conversation::createFromConversation($conversationData);
            $this->bus->dispatch(new StoreConversationCommand($conversationData));

            $tempConversation = Conversation::createFromConversation($conversationData);
            $tempConversation->getMessages()->reset();
            $this->bus->dispatch(new StoreTemporaryConversation($tempConversation));
        } else {
            $this->bus->dispatch(new StoreConversationCommand($conversationData));
        }

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            $this->isTemporary
                ? 'Die Unterhaltung wurde erfolgreich in der Bibliothek gespeichert.'
                : 'Die Unterhaltung wurde erfolgreich aktualisiert.',
        );

        return $this->redirectToRoute('chat', ['conversation' => $conversationData->getId()]);
    }
}
