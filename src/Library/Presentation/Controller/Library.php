<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Presentation\Controller;

use DZunke\NovDoc\Library\Domain\Entity\Directory;
use DZunke\NovDoc\Library\Domain\Entity\Document;
use DZunke\NovDoc\Library\Domain\Entity\Image;
use DZunke\NovDoc\Library\Domain\RootDirectory;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    defaults: ['directory' => RootDirectory::ID],
    requirements: ['directory' => Requirement::UUID],
)]
class Library extends AbstractController
{
    public function __construct(
        private readonly Environment $environment,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemDirectoryRepository $directoryRepository,
        private readonly FilesystemImageRepository $imageRepository,
    ) {
    }

    public function __invoke(Request $request, Directory $directory): Response
    {
        $directoryContent = array_merge(
            $this->documentRepository->findByDirectory($directory),
            $this->imageRepository->findByDirectory($directory),
        );

        usort(
            $directoryContent,
            static fn (Image|Document $left, Image|Document $right) => strcasecmp($left->getSlug(), $right->getSlug()),
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
