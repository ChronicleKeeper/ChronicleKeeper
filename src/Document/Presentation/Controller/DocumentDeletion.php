<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Presentation\Controller;

use ChronicleKeeper\Document\Application\Command\DeleteDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, Document $document): Response
    {
        if ($request->get('confirm', 0) === 0) {
            $this->addFlashMessage(
                $request,
                Alert::WARNING,
                'Das Löschen des Dokumentes "' . $document->getTitle() . '" muss erst bestätigt werden!',
            );

            return new RedirectResponse($this->router->generate(
                'library',
                ['directory' => $document->getDirectory()->id],
            ));
        }

        $this->bus->dispatch(new DeleteDocument($document));

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Das Dokument "' . $document->getTitle() . '" wurde erfolgreich gelöscht.',
        );

        return new RedirectResponse($this->router->generate(
            'library',
            ['directory' => $document->getDirectory()->id],
        ));
    }
}
