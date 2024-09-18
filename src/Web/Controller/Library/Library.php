<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Library;

use DZunke\NovDoc\Domain\Document\Directory;
use DZunke\NovDoc\Domain\Document\Document;
use DZunke\NovDoc\Domain\Library\Directory\RootDirectory;
use DZunke\NovDoc\Domain\Library\Image\Image;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemDirectoryRepository;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemDocumentRepository;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemImageRepository;
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
class Library
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
