<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Document;

use DZunke\NovDoc\Infrastructure\Repository\FilesystemDocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/documents/{document}/view', name: 'document_view')]
class DocumentView
{
    public function __construct(
        private readonly Environment $environment,
        private readonly FilesystemDocumentRepository $documentRepository,
    ) {
    }

    public function __invoke(Request $request, string $document): Response
    {
        $document = $this->documentRepository->findById($document);

        return new Response($this->environment->render(
            'documents/document_view.html.twig',
            ['document' => $document],
        ));
    }
}
