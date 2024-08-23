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

#[Route('/store_conversation', name: 'store_conversation')]
class StoreConversation
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Storage $storage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->storage->store();
        $this->addFlashMessage($request, Alert::SUCCESS, 'Die Unterhaltung wurde in den Zwischenspeicher gesichert.');

        return new RedirectResponse('/');
    }
}
