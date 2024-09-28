<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/chat-delete/{conversation}',
    name: 'chat_delete',
    requirements: ['conversation' => Requirement::UUID],
)]
class ConversationDelete extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly ConversationFileStorage $conversationFileStorage,
    ) {
    }

    public function __invoke(Request $request, Conversation $conversation): Response
    {
        if ($request->get('confirm', 0) === 0) {
            $this->addFlashMessage(
                $request,
                Alert::WARNING,
                'Das Löschen des Gespräches "' . $conversation->title . '" muss erst bestätigt werden!',
            );

            return $this->redirectToRoute('library', ['directory' => $conversation->directory->id]);
        }

        $this->conversationFileStorage->delete($conversation);

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Das Gespräch "' . $conversation->title . '" wurde erfolgreich gelöscht.',
        );

        return $this->redirectToRoute('library', ['directory' => $conversation->directory->id]);
    }
}
