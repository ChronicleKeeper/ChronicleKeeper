<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Controller;

use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/changelog', name: 'changelog')]
class ViewChangelog extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly FileAccess $fileAccess,
    ) {
    }

    public function __invoke(): Response
    {
        $changelog = $this->fileAccess->read('general.project', 'CHANGELOG.md');

        return new Response($this->environment->render('changelog.html.twig', ['changelog' => $changelog]));
    }
}
