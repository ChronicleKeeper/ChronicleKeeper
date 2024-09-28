<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig;

use ChronicleKeeper\Chat\Infrastructure\Repository\Conversation\Storage;
use ChronicleKeeper\Chat\Presentation\Form\StoreConversationType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Chat:StoreConversation', template: 'components/chat/store-conversation.html.twig')]
class StoreConversation extends AbstractController
{
    use HandleFlashMessages;
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(StoreConversationType::class);
    }

    #[LiveAction]
    public function store(Request $request, Storage $storage): Response
    {
        $this->submitForm();
        $conversationData = $this->getForm()->getData();
        $this->resetForm();

        $storage->store();

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Die Unterhaltung wurde erfolgreich in der Bibliothek hinterlegt.',
        );

        return $this->redirectToRoute('chat');
    }
}
