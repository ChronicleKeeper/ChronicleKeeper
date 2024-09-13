<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/changelog', name: 'changelog')]
class ViewChangelog
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
