<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Document;

use DZunke\NovDoc\Domain\Document\Document;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemDirectoryRepository;
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

#[Route('/documents/create', name: 'documents_create', defaults: ['directory' => null])]
#[Route('/documents/{directory}/create', name: 'documents_create_directory')]
class DocumentCreation
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly RouterInterface $router,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    public function __invoke(Request $request, string|null $directory): Response
    {
        if ($directory !== null) {
            $directory = $this->directoryRepository->findById($directory);
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $title   = $request->get('title', '');
            $content = $request->get('content', '');
            if (is_string($title) && is_string($content)) {
                $document            = new Document($title, $content);
                $document->directory = $directory;

                $this->documentRepository->store($document);

                $this->addFlashMessage(
                    $request,
                    Alert::SUCCESS,
                    'Das Dokument wurde erstellt, damit es in der Suche aktiv ist kannst du den Suchindex aktualisieren.',
                );

                $forward = $this->router->generate('documents_overview');
                if ($directory !== null) {
                    $forward = $this->router->generate('documents_overview_directory', ['directory' => $directory->id]);
                }

                return new RedirectResponse($forward);
            }
        }

        return new Response($this->environment->render(
            'documents_create.html.twig',
            ['directory' => $directory],
        ));
    }
}
