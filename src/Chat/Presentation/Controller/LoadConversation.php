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

#[Route('/load_conversation', name: 'load_conversation')]
class LoadConversation extends AbstractController
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
