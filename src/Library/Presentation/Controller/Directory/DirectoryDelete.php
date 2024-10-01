<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Directory;

use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Library\Presentation\Form\DirectoryDeleteOptions;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemImageRepository $imageRepository,
        private readonly ConversationFileStorage $conversationRepository,
    ) {
    }

    public function __invoke(Request $request, Directory $directory): Response
    {
        if ($directory->id === RootDirectory::ID) {
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

            return $this->redirectToRoute('library', ['directory' => $directory->parent?->id]);
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

        return $this->redirectToRoute('library', ['directory' => $targetDirectory->id]);
    }

    private function moveDirectoryContentToOtherDirectory(Directory $sourceDirectory, Directory $targetDirectory): void
    {
        foreach ($this->directoryRepository->findByParent($sourceDirectory) as $directory) {
            $directory->parent = $targetDirectory;
            $this->directoryRepository->store($directory);
        }

        foreach ($this->documentRepository->findByDirectory($sourceDirectory) as $document) {
            $document->directory = $targetDirectory;
            $this->documentRepository->store($document);
        }

        foreach ($this->imageRepository->findByDirectory($sourceDirectory) as $image) {
            $image->directory = $targetDirectory;
            $this->imageRepository->store($image);
        }

        foreach ($this->conversationRepository->findByDirectory($sourceDirectory) as $conversation) {
            $conversation->directory = $targetDirectory;
            $this->conversationRepository->store($conversation);
        }
    }
}
