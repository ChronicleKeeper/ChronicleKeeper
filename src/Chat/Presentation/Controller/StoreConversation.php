<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Infrastructure\Repository\Conversation\Storage;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/store_conversation', name: 'store_conversation')]
class StoreConversation extends AbstractController
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
