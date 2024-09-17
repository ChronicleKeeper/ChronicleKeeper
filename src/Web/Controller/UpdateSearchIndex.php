<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use DZunke\NovDoc\Domain\VectorStorage\Updater;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/reload', name: 'update_search_index')]
class UpdateSearchIndex
{
    use HandleFlashMessages;

    public function __construct(
        private Updater $updater,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->updater->updateAll();
        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Veränderte Dokumente wurden in den Suchindex geladen, ab in die Schenke zu einem Plausch!',
        );

        return new RedirectResponse($this->router->generate('chat'));
    }
}
