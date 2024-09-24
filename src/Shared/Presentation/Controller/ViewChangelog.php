<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Controller;

use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/changelog', name: 'changelog')]
class ViewChangelog extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly Filesystem $filesystem,
        private readonly string $changelogFile,
    ) {
    }

    public function __invoke(): Response
    {
        $changelog = $this->filesystem->readFile($this->changelogFile);

        return new Response($this->environment->render('changelog.html.twig', ['changelog' => $changelog]));
    }
}
