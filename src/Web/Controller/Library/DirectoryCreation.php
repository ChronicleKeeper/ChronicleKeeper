<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Library;

use DZunke\NovDoc\Domain\Document\Directory;
use DZunke\NovDoc\Infrastructure\Repository\FilesystemDirectoryRepository;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use DZunke\NovDoc\Web\Form\Library\DirectoryType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function array_key_exists;
use function assert;
use function is_array;

#[Route(
    '/library/directory/{parentDirectory}/create_directory',
    name: 'library_directory_create',
    requirements: ['parentDirectory' => Requirement::UUID],
)]
class DirectoryCreation
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly RouterInterface $router,
        private readonly FilesystemDirectoryRepository $directoryRepository,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function __invoke(Request $request, Directory $parentDirectory): Response
    {
        $form = $this->formFactory->create(DirectoryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $directoryArray = $form->getData();
            assert(is_array($directoryArray) && array_key_exists('title', $directoryArray));

            $directory         = new Directory($directoryArray['title']);
            $directory->parent = $parentDirectory;

            $this->directoryRepository->store($directory);

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Das Verzeichnis "' . $directory->title . '" wurde erfolgreich erstellt.',
            );

            return new RedirectResponse($this->router->generate(
                'library',
                ['directory' => $directory->id],
            ));
        }

        return new Response(
            $this->environment->render(
                'library/directory_create.html.twig',
                [
                    'parentDirectory' => $parentDirectory,
                    'form' => $form->createView(),
                ],
            ),
        );
    }
}
