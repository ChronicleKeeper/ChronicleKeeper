<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use DZunke\NovDoc\Domain\Conversation\Storage;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/load_conversation', name: 'load_conversation')]
class LoadConversation
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Storage $storage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->storage->load();
        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Die letzte Unterhaltung wurde aus dem Zwischenspeicher geladen.',
        );

        return new RedirectResponse('/');
    }
}
