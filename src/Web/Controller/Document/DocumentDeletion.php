<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Document;

use DZunke\NovDoc\Infrastructure\Repository\FilesystemDocumentRepository;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/documents/{id}/delete', name: 'document_delete')]
class DocumentDeletion
{
    use HandleFlashMessages;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemVectorDocumentRepository $vectorDocumentRepository,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $document = $this->documentRepository->findById($id);
        if ($document === null) {
            $this->addFlashMessage($request, Alert::WARNING, 'Das Dokument existiert nicht.');

            return new RedirectResponse($this->router->generate('documents_overview'));
        }

        $vectorDocuments = $this->vectorDocumentRepository->findAllByDocumentId($document->id);

        foreach ($vectorDocuments as $vectorDocument) {
            $this->vectorDocumentRepository->remove($vectorDocument);
        }

        $this->documentRepository->remove($document);

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Das Dokument "' . $document->title . '" wurde erfolgreich gelöscht.',
        );

        return new RedirectResponse($this->router->generate('documents_overview'));
    }
}
