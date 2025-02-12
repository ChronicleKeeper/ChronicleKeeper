<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller;

use ChronicleKeeper\Library\Application\Query\FindDirectoryContent;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Twig\Environment;

#[Route(
    '/library/{directory}',
    name: 'library',
    requirements: ['directory' => Requirement::UUID],
    defaults: ['directory' => RootDirectory::ID],
)]
class Library extends AbstractController
{
    public function __construct(
        private readonly Environment $environment,
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(Directory $directory): Response
    {
        $directoryContent = $this->queryService->query(new FindDirectoryContent($directory->getId()));

        return new Response($this->environment->render(
            'library/library.html.twig',
            [
                'currentDirectory' => $directory,
                'content' => $directoryContent,
            ],
        ));
    }
}
