<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller;

use ChronicleKeeper\Library\Application\Service\CacheReader;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clear_directory_cache', name: 'clear_directory_cache')]
class ClearDirectoryCache extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly CacheReader $cacheReader,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->cacheReader->clear();
        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Der Verzeichniscache wurde gelöscht, bitte beachte, dass die ersten Aufrufe jetzt wieder etwas langsamer sind.',
        );

        return $this->redirectToRoute('library');
    }
}
