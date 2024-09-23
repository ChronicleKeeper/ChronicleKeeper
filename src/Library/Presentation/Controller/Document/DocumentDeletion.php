<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Presentation\Controller\Document;

use DZunke\NovDoc\Library\Domain\Entity\Document;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemVectorDocumentRepository;
use DZunke\NovDoc\Shared\Presentation\FlashMessages\Alert;
use DZunke\NovDoc\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\RouterInterface;

#[Route(
    '/library/document/{document}/delete',
    name: 'library_document_delete',
    requirements: ['document' => Requirement::UUID],
)]
class DocumentDeletion extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemVectorDocumentRepository $vectorDocumentRepository,
    ) {
    }

    public function __invoke(Request $request, Document $document): Response
    {
        if ($request->get('confirm', 0) === 0) {
            $this->addFlashMessage(
                $request,
                Alert::WARNING,
                'Das Löschen des Dokumentes "' . $document->title . '" muss erst bestätigt werden!',
            );

            return new RedirectResponse($this->router->generate('library', ['directory' => $document->directory->id]));
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

        return new RedirectResponse($this->router->generate('library', ['directory' => $document->directory->id]));
    }
}
