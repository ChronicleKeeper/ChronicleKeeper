<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Library;

use DZunke\NovDoc\Domain\Library\Directory\RootDirectory;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemDirectoryRepository;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function is_string;

#[Route(
    '/library/directory/{directory}/edit',
    name: 'library_directory_edit',
    requirements: ['directory' => Requirement::UUID],
)]
class DirectoryEdit
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

            return new RedirectResponse($this->router->generate('library', ['directory' => RootDirectory::ID]));
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

                return new RedirectResponse($this->router->generate(
                    'library',
                    ['directory' => $directory->id],
                ));
            }
        }

        return new Response(
            $this->environment->render(
                'library/directory_edit.html.twig',
                ['directory' => $directory],
            ),
        );
    }
}
