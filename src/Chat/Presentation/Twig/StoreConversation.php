<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Command\ResetTemporaryConversation;
use ChronicleKeeper\Chat\Application\Command\StoreConversation as StoreConversationCommand;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
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
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[LiveProp(
        writable: true,
        useSerializerForHydration: true,
        serializationContext: [
            ExtendedMessageDenormalizer::WITH_CONTEXT_DOCUMENTS => true,
            ExtendedMessageDenormalizer::WITH_CONTEXT_IMAGES => true,
            ExtendedMessageDenormalizer::WITH_DEBUG_FUNCTIONS => true,
        ],
    )]
    public Conversation $conversation;

    #[PostMount]
    public function loadTemporaryConversation(): void
    {
        if (isset($this->conversation)) {
            return;
        }

        $this->conversation = $this->queryService->query(new GetTemporaryConversationParameters());
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(StoreConversationType::class, $this->conversation ?? Conversation::createEmpty());
    }

    #[LiveListener('conversation_updated')]
    public function updateConversation(
        #[LiveArg]
        string $conversationId,
    ): Response {
        $conversation = $this->queryService->query(new FindConversationByIdParameters($conversationId));
        $temporary    = false;
        if ($conversation === null) {
            $conversation = $this->queryService->query(new GetTemporaryConversationParameters());
            $temporary    = true;
        }

        $this->conversation = $conversation;
        $this->getForm()->setData($this->conversation);

        return $this->redirectToRoute('chat', ['conversation' => $temporary ? null : $conversation->id]);
    }

    #[LiveAction]
    public function store(Request $request): Response
    {
        $this->submitForm();
        $conversationData = $this->getForm()->getData();
        $this->resetForm();

        assert($conversationData instanceof Conversation);

        $this->bus->dispatch(new StoreConversationCommand($conversationData));
        $this->bus->dispatch(new ResetTemporaryConversation());

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Die Unterhaltung wurde erfolgreich in der Bibliothek gespeichert.',
        );

        return $this->redirectToRoute('chat', ['conversation' => $this->conversation->id]);
    }
}
