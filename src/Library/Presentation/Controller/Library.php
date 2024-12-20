<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Domain\Sluggable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Twig\Environment;

use function array_merge;
use function strcasecmp;
use function usort;

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
        private readonly FilesystemDirectoryRepository $directoryRepository,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(Directory $directory): Response
    {
        $directoryContent = array_merge(
            $this->queryService->query(new FindDocumentsByDirectory($directory->id)),
            $this->imageRepository->findByDirectory($directory),
            $this->queryService->query(new FindConversationsByDirectoryParameters($directory)),
        );

        usort(
            $directoryContent,
            static fn (Sluggable $left, Sluggable $right) => strcasecmp($left->getSlug(), $right->getSlug()),
        );

        return new Response($this->environment->render(
            'library/library.html.twig',
            [
                'currentDirectory' => $directory,
                'directories' => $this->directoryRepository->findByParent($directory),
                'media' => $directoryContent,
            ],
        ));
    }
}
