<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Presentation\Controller\Directory;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationsByDirectoryParameters;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Query\FindDocumentsByDirectory;
use ChronicleKeeper\Image\Application\Command\StoreImage;
use ChronicleKeeper\Image\Application\Query\FindImagesByDirectory;
use ChronicleKeeper\Library\Application\Command\DeleteDirectory;
use ChronicleKeeper\Library\Application\Command\DeleteDirectoryHandler;
use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Application\Query\FindDirectoriesByParent;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Presentation\Controller\Directory\DirectoryDelete;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use ChronicleKeeper\Test\Document\Domain\Entity\DocumentBuilder;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function is_array;

#[CoversClass(DirectoryDelete::class)]
#[CoversClass(DeleteDirectory::class)]
#[CoversClass(DeleteDirectoryHandler::class)]
#[CoversClass(FindDirectoriesByParent::class)]
#[CoversClass(FindDocumentsByDirectory::class)]
#[CoversClass(FindImagesByDirectory::class)]
#[CoversClass(FindConversationsByDirectoryParameters::class)]
#[CoversClass(StoreDirectory::class)]
#[Large]
final class DirectoryDeleteTest extends WebTestCase
{
    #[Test]
    public function itIsNotCallableForRootDirectory(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/edit',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function itHasACallableDeletionForm(): void
    {
        // -------------- Setup Data Fixtures --------------

        $directory = (new DirectoryBuilder())->withTitle('Origin Name')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_GET,
            '/library/directory/' . $directory->getId() . '/delete',
        );

        // -------------- Asserts --------------

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Verzeichnis "Origin Name" löschen');

        // Check the breadcrumb
        self::assertSelectorTextContains('ol.breadcrumb li:nth-child(1)', 'Hauptverzeichnis');
        self::assertSelectorTextContains('ol.breadcrumb li:nth-child(2)', $directory->getTitle());
        self::assertSelectorTextContains('ol.breadcrumb li:nth-child(3)', 'Verzeichnis löschen');

        // Check both form buttons are there
        self::assertSelectorTextContains('form button.btn-warning', 'Verschieben & Löschen');
        self::assertSelectorTextContains('form button.btn-danger', 'Alles Löschen');

        // Check form elements are there
        self::assertSelectorExists('form input[name="directory_delete_options[confirmDeleteAll]"]');
        self::assertFormValue('form', 'directory_delete_options[moveContentTo]', RootDirectory::ID);
    }

    #[Test]
    public function itCanFullyDeleteADirectory(): void
    {
        // -------------- Setup Data Fixtures --------------

        $directory = (new DirectoryBuilder())->withTitle('Origin Name')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        $image = (new ImageBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreImage($image));

        $document = (new DocumentBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreDocument($document));

        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreConversation($conversation));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_POST,
            '/library/directory/' . $directory->getId() . '/delete',
            [
                'directory_delete_options' => [
                    'confirmDeleteAll' => true,
                    'moveContentTo' => RootDirectory::ID,
                ],
            ],
        );

        // -------------- Asserts --------------

        // Check if the redirect to the parent directory has been done, so the root
        self::assertResponseRedirects('/library');

        // Check that the data that was there is removed
        $directoryResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('directories')
            ->where('id = :id')
            ->setParameter('id', $directory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertFalse($directoryResult);

        $imageResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('images')
            ->where('directory = :directory')
            ->setParameter('directory', $directory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertFalse($imageResult);

        $documentResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('documents')
            ->where('directory = :directory')
            ->setParameter('directory', $directory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertFalse($documentResult);

        $conversationResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('conversations')
            ->where('directory = :directory')
            ->setParameter('directory', $directory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertFalse($conversationResult);
    }

    #[Test]
    public function itIsAbleToMoveContentBeforeDeletingADirectory(): void
    {
        // -------------- Setup Data Fixtures --------------

        $directory = (new DirectoryBuilder())->withTitle('Origin Name')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        $subDirectory = (new DirectoryBuilder())->withParent($directory)->withTitle('Sub Directory Name')->build();
        $this->bus->dispatch(new StoreDirectory($subDirectory));

        $image = (new ImageBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreImage($image));

        $document = (new DocumentBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreDocument($document));

        $conversation = (new ConversationBuilder())->withDirectory($directory)->build();
        $this->bus->dispatch(new StoreConversation($conversation));

        $targetDirectory = (new DirectoryBuilder())->withTitle('Target Name')->build();
        $this->bus->dispatch(new StoreDirectory($targetDirectory));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_POST,
            '/library/directory/' . $directory->getId() . '/delete',
            [
                'directory_delete_options' => [
                    'moveContentTo' => $targetDirectory->getId(),
                ],
            ],
        );

        // -------------- Asserts --------------

        // Check if the redirect to the parent directory has been done, so the root
        self::assertResponseRedirects('/library/' . $targetDirectory->getId());

        // Check that the data that was there is removed or changed
        $directoryResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('directories')
            ->where('id = :id')
            ->setParameter('id', $directory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertFalse($directoryResult);

        $imageResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('images')
            ->where('directory = :directory')
            ->setParameter('directory', $targetDirectory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertTrue(is_array($imageResult));

        $documentResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('documents')
            ->where('directory = :directory')
            ->setParameter('directory', $targetDirectory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertTrue(is_array($documentResult));

        $conversationResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('conversations')
            ->where('directory = :directory')
            ->setParameter('directory', $targetDirectory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertTrue(is_array($conversationResult));

        $subDirectoryResult = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('directories')
            ->where('parent = :parent')
            ->setParameter('parent', $targetDirectory->getId())
            ->executeQuery()
            ->fetchAssociative();

        self::assertTrue(is_array($subDirectoryResult));
    }
}
