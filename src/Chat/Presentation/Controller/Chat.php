<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/',
    name: 'home',
    defaults: ['conversation' => null],
)]
#[Route(
    '/chat/{conversation}',
    name: 'chat',
    defaults: ['conversation' => null],
)]
class Chat extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(Request $request, string|null $conversation): Response
    {
        if ($conversation === null) {
            // THere is no active conversation, so load the temporary conversation
            return $this->render(
                'chat/chat.html.twig',
                [
                    'conversation' => $this->queryService->query(new GetTemporaryConversationParameters()),
                    'isTemporary' => true,
                ],
            );
        }

        $conversation = $this->queryService->query(new FindConversationByIdParameters($conversation));
        if ($conversation === null) {
            $request->getSession()->set('last_conversation', null);
            $this->addFlashMessage(
                $request,
                Alert::DANGER,
                'Dein letztes Gespräch scheint aus dem Gedächtnis verbannt worden zu sein. Entschuldige.',
            );

            return $this->redirectToRoute('home');
        }

        $request->getSession()->set('last_conversation', $conversation->getId());

        return $this->render(
            'chat/chat.html.twig',
            ['conversation' => $conversation, 'isTemporary' => false],
        );
    }
}
