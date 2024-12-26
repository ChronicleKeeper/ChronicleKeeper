<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Directory;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Library\Presentation\Form\DirectoryDeleteOptions;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(
    '/library/directory/{directory}/delete',
    name: 'library_directory_delete',
    requirements: ['directory' => Requirement::UUID],
)]
class DirectoryDelete extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly FilesystemDirectoryRepository $directoryRepository,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, Directory $directory): Response
    {
        if ($directory->getId() === RootDirectory::ID) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(DirectoryDeleteOptions::class, options: ['exclude_directories' => [$directory]]);
        $form->handleRequest($request);

        if (! $form->isSubmitted() || ! $form->isValid()) {
            return $this->render(
                'library/directory_delete.html.twig',
                ['directory' => $directory, 'form' => $form->createView()],
            );
        }

        /** @var array{confirmDeleteAll: bool, moveContentTo: Directory} $directoryRemovalOptions */
        $directoryRemovalOptions = $form->getData();
        if ($directoryRemovalOptions['confirmDeleteAll'] === true) {
            // Delte all Content! Must search for all child directories ... all! Really ALL!
            $this->directoryRepository->remove($directory);

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Das Verzeichnis und alle seine Daten wurden gelöscht.',
            );

            return $this->redirectToRoute('library', ['directory' => $directory->getParent()?->getId()]);
        }

        // Ok, we just move all before deletion :)
        $targetDirectory = $directoryRemovalOptions['moveContentTo'];

        $this->moveDirectoryContentToOtherDirectory($directory, $directoryRemovalOptions['moveContentTo']);
        $this->directoryRepository->remove($directory);

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Das Verzeichnis wurde gelöscht und seine Inhalte verschoben.',
        );

        return $this->redirectToRoute('library', ['directory' => $targetDirectory->getId()]);
    }

    private function moveDirectoryContentToOtherDirectory(Directory $sourceDirectory, Directory $targetDirectory): void
    {
        foreach ($this->directoryRepository->findByParent($sourceDirectory) as $directory) {
            $directory->moveToDirectory($targetDirectory);
            $this->directoryRepository->store($directory);
        }

        foreach ($this->queryService->query(new FindDocumentsByDirectory($sourceDirectory->getId())) as $document) {
            $document->moveToDirectory($targetDirectory);
            $this->bus->dispatch(new StoreDocument($document));
        }

        foreach ($this->imageRepository->findByDirectory($sourceDirectory) as $image) {
            $image->moveToDirectory($targetDirectory);
            $this->imageRepository->store($image);
        }

        foreach ($this->queryService->query(new FindConversationsByDirectoryParameters($sourceDirectory)) as $conversation) {
            $conversation->moveToDirectory($targetDirectory);
            $this->bus->dispatch(new StoreConversation($conversation));
        }
    }
}
