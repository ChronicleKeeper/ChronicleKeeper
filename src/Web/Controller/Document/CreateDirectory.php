<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Document;

use DZunke\NovDoc\Domain\Document\Directory;
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

#[Route('/documents/{parentDirectory}/directory/create', name: 'documents_directory_create', defaults: ['parentDirectory' => 'root'])]
class CreateDirectory
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly RouterInterface $router,
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    public function __invoke(Request $request, string $parentDirectory): Response
    {
        if ($parentDirectory === 'root') {
            $parentDirectory = null;
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $title = $request->get('title', '');
            if (is_string($title) && $title !== '') {
                $directory = new Directory($title);
                if ($parentDirectory !== null) {
                    $directory->parent = $this->directoryRepository->findById($parentDirectory);
                }

                $this->directoryRepository->store($directory);

                $this->addFlashMessage(
                    $request,
                    Alert::SUCCESS,
                    'Das Verzeichnis "' . $title . '" wurde erfolgreich erstellt.',
                );

                return new RedirectResponse($this->router->generate(
                    'documents_overview_directory',
                    ['directory' => $directory->id],
                ));
            }
        }

        return new Response(
            $this->environment->render(
                'documents/directory_create.html.twig',
                ['parentDirectory' => $parentDirectory],
            ),
        );
    }
}
