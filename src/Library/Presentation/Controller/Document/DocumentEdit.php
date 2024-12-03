<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Document;

use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
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
use Twig\Environment;

use function is_string;

#[Route(
    '/library/document/{document}/edit',
    name: 'library_document_edit',
    requirements: ['directory' => Requirement::UUID],
)]
class DocumentEdit extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly RouterInterface $router,
        private readonly FilesystemDirectoryRepository $directoryRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, Document $document): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $title   = $request->get('title', '');
            $content = $request->get('content', '');
            if (is_string($title) && is_string($content)) {
                $document->title   = $title;
                $document->content = $content;

                $storeDirectory = $request->getPayload()->get('directory');

                if (is_string($storeDirectory)) {
                    $storeDirectory = $this->directoryRepository->findById($storeDirectory) ?? $document->directory;
                } else {
                    $storeDirectory = $document->directory;
                }

                $document->directory = $storeDirectory;

                $this->bus->dispatch(new StoreDocument($document));

                $this->addFlashMessage(
                    $request,
                    Alert::SUCCESS,
                    'Das Dokument wurde bearbeitet, damit die Änderungen in der Suche aktiv sind muss der Index aktualisiert werden.',
                );

                return new RedirectResponse($this->router->generate('library', ['directory' => $document->directory->id]));
            }
        }

        return new Response($this->environment->render(
            'library/document_edit.html.twig',
            ['document' => $document],
        ));
    }
}
