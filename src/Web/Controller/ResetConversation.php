<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use DZunke\NovDoc\Domain\Chat;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reset_conversation', name: 'reset_conversation')]
class ResetConversation
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Chat $chat,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->chat->reset();
        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Ein neuer Krug Bier wurde herbeigebracht so dass das Gespräch weitergehen kann!',
        );

        return new RedirectResponse('/');
    }
}
