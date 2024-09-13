<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Document;

use DZunke\NovDoc\Infrastructure\Repository\FilesystemDocumentRepository;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function is_string;

#[Route('/documents/{id}/edit', name: 'documents_edit')]
class DocumentEdit
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly RouterInterface $router,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemDocumentRepository $filesystemDocumentRepository,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $document = $this->filesystemDocumentRepository->findById($id);
        if ($document === null) {
            $this->addFlashMessage(
                $request,
                Alert::DANGER,
                'Das Dokument wurde nicht gefunden.',
            );

            return new RedirectResponse($this->router->generate('documents_overview'));
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $title   = $request->get('title', '');
            $content = $request->get('content', '');
            if (is_string($title) && is_string($content)) {
                $document->title   = $title;
                $document->content = $content;

                $this->documentRepository->store($document);

                $this->addFlashMessage(
                    $request,
                    Alert::SUCCESS,
                    'Das Dokument wurde bearbeitet, damit die Änderungen in der Suche aktiv sind muss der Index aktualisiert werden.',
                );

                return new RedirectResponse($this->router->generate('documents_overview'));
            }
        }

        return new Response($this->environment->render(
            'documents_edit.html.twig',
            ['document' => $document],
        ));
    }
}
