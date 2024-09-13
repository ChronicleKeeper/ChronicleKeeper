<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Document;

use DZunke\NovDoc\Infrastructure\Repository\FilesystemDirectoryRepository;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemDocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/documents', name: 'documents_overview', defaults: ['directory' => null])]
#[Route('/documents/{directory}', name: 'documents_overview_directory')]
class DocumentOverview
{
    public function __construct(
        private readonly Environment $environment,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    public function __invoke(Request $request, string|null $directory): Response
    {
        if ($directory !== null) {
            $directory = $this->directoryRepository->findById($directory);
        }

        return new Response($this->environment->render(
            'documents.html.twig',
            [
                'currentDirectory' => $directory,
                'directories' => $this->directoryRepository->findByParent($directory),
                'documents' => $this->documentRepository->findByDirectory($directory),
            ],
        ));
    }
}
