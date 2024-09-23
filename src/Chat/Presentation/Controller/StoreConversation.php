<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Chat\Presentation\Controller;

use DZunke\NovDoc\Chat\Infrastructure\Repository\Conversation\Storage;
use DZunke\NovDoc\Shared\Presentation\FlashMessages\Alert;
use DZunke\NovDoc\Shared\Presentation\FlashMessages\HandleFlashMessages;
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
