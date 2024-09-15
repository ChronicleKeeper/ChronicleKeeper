<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Library;

use DZunke\NovDoc\Infrastructure\Repository\FilesystemDirectoryRepository;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemDocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Twig\Environment;

#[Route(
    '/library/{directory}',
    name: 'library',
    requirements: ['directory' => Requirement::UUID],
)]
class Library
{
    public function __construct(
        private readonly Environment $environment,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    public function __invoke(Request $request, string $directory): Response
    {
        $directory = $this->directoryRepository->findById($directory);
        if ($directory === null) {
            throw new NotFoundHttpException('Directory not found.');
        }

        return new Response($this->environment->render(
            'library/library.html.twig',
            [
                'currentDirectory' => $directory,
                'directories' => $this->directoryRepository->findByParent($directory),
                'documents' => $this->documentRepository->findByDirectory($directory),
            ],
        ));
    }
}