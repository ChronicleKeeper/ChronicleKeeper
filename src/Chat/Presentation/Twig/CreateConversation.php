<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Application\Command\ResetTemporaryConversation;
use ChronicleKeeper\Chat\Presentation\Form\CreateConversationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Chat:CreateConversation', template: 'components/chat/create-conversation.html.twig')]
final class CreateConversation extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(CreateConversationType::class);
    }

    #[LiveAction]
    public function store(Request $request): Response
    {
        $this->submitForm();
        $settings = $this->getForm()->getData();
        $this->resetForm();

        $this->bus->dispatch(new ResetTemporaryConversation($settings['title'], $settings['utilize_prompt']));
        $request->getSession()->remove('last_conversation');

        return $this->redirectToRoute('chat');
    }
}
