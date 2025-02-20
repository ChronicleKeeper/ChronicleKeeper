<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Command\StoreConversation as StoreConversationCommand;
use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Chat\Infrastructure\Serializer\ExtendedMessageDenormalizer;
use ChronicleKeeper\Chat\Presentation\Form\ConversationSettingsType;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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

    #[LiveProp(writable: true)]
    public bool $isTemporary = false;

    #[PostMount]
    public function loadTemporaryConversation(): void
    {
        if (isset($this->conversation)) {
            return;
        }

        $this->conversation = $this->queryService->query(new GetTemporaryConversationParameters());
        $this->isTemporary  = true;
    }

    protected function instantiateForm(): FormInterface
    {
        if (! isset($this->conversation)) {
            $this->loadTemporaryConversation();
        }

        return $this->createForm(
            ConversationSettingsType::class,
            $this->conversation->getSettings(),
        );
    }

    #[LiveListener('conversation_updated')]
    public function updateConversation(
        #[LiveArg]
        string $conversationId,
    ): void {
        $conversation = $this->queryService->query(new FindConversationByIdParameters($conversationId));
        if ($conversation === null) {
            $conversation = $this->queryService->query(new GetTemporaryConversationParameters());
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

        $this->conversation->changeSettings($settings);

        if ($this->isTemporary === true) {
            $this->bus->dispatch(new StoreTemporaryConversation($this->conversation));

            return $this->redirectToRoute('chat');
        }

        $this->bus->dispatch(new StoreConversationCommand($this->conversation));

        return $this->redirectToRoute('chat', ['conversationId' => $this->conversation->getId()]);
    }
}
