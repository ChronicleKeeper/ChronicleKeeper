<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Presentation\Controller;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Presentation\Form\DocumentType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/library/document/{document}/edit',
    name: 'library_document_edit',
    requirements: ['directory' => Requirement::UUID],
)]
class DocumentEdit extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, Document $document): Response
    {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->bus->dispatch(new StoreDocument($document));

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Das Dokument wurde bearbeitet, damit die Änderungen in der Suche aktiv sind muss der Index aktualisiert werden.',
            );

            return $this->redirectToRoute('library', ['directory' => $document->getDirectory()->id]);
        }

        return $this->render(
            'document/document_edit.html.twig',
            ['form' => $form->createView(), 'document' => $document],
        );
    }
}
