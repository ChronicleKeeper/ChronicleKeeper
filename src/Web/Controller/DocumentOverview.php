<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use DZunke\NovDoc\Infrastructure\Repository\FilesystemDocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/documents', name: 'documents_overview')]
class DocumentOverview
{
    public function __construct(
        private readonly Environment $environment,
        private readonly FilesystemDocumentRepository $documentRepository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        return new Response($this->environment->render(
            'documents.html.twig',
            ['documents' => $this->documentRepository->findAll()],
        ));
    }
}
