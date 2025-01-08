<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Directory;

use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Presentation\Form\DirectoryType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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
class DirectoryCreation extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly RouterInterface $router,
        private readonly FormFactoryInterface $formFactory,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, Directory $parentDirectory): Response
    {
        $form = $this->formFactory->create(DirectoryType::class, ['parent' => $parentDirectory]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $directoryArray = $form->getData();
            assert(is_array($directoryArray) && array_key_exists('title', $directoryArray));

            $directory = Directory::create($directoryArray['title'], $parentDirectory);
            $this->bus->dispatch(new StoreDirectory($directory));

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Das Verzeichnis "' . $directory->getTitle() . '" wurde erfolgreich erstellt.',
            );

            return new RedirectResponse($this->router->generate(
                'library',
                ['directory' => $directory->getId()],
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
