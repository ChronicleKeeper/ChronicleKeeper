<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Document;

use DZunke\NovDoc\Infrastructure\Repository\FilesystemDirectoryRepository;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function is_string;

#[Route('/documents/directory/{directory}/edit', name: 'documents_directory_edit')]
class EditDirectory
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly RouterInterface $router,
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    public function __invoke(Request $request, string $directory): Response
    {
        $directory = $this->directoryRepository->findById($directory);
        if ($directory === null) {
            $this->addFlashMessage(
                $request,
                Alert::DANGER,
                'Das Verzeichnis wurde nicht gefunden.',
            );

            return new RedirectResponse($this->router->generate('documents_overview'));
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $title = $request->get('title', '');
            if (is_string($title) && $title !== '') {
                $directory->title = $title;

                $this->directoryRepository->store($directory);

                $this->addFlashMessage(
                    $request,
                    Alert::SUCCESS,
                    'Das Verzeichnis "' . $title . '" wurde erfolgreich bearbeitet.',
                );

                if ($directory->parent === null) {
                    return new RedirectResponse($this->router->generate('documents_overview'));
                }

                return new RedirectResponse($this->router->generate(
                    'documents_overview_directory',
                    ['directory' => $directory?->parent->id ?? $directory->id],
                ));
            }
        }

        return new Response(
            $this->environment->render(
                'documents/directory_edit.html.twig',
                ['directory' => $directory],
            ),
        );
    }
}
