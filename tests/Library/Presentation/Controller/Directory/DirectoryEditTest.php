<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Presentation\Controller\Directory;

use ChronicleKeeper\Library\Application\Command\StoreDirectory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Presentation\Controller\Directory\DirectoryEdit;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(DirectoryEdit::class)]
#[Large]
final class DirectoryEditTest extends WebTestCase
{
    #[Test]
    public function itIsNotAvailableForTheRootDirectory(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/edit',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function itHasACallableForm(): void
    {
        // -------------- Setup Data Fixtures --------------

        $directory = (new DirectoryBuilder())->withTitle('Origin Name')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_GET,
            '/library/directory/' . $directory->getId() . '/edit',
        );

        // -------------- Asserts --------------

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Verzeichnis "Origin Name" bearbeiten');

        self::assertFormValue('form', 'directory[title]', 'Origin Name');
        self::assertFormValue('form', 'directory[parent]', RootDirectory::ID);
    }

    #[Test]
    public function itCanSubmitAChangedTitle(): void
    {
        // -------------- Setup Data Fixtures --------------

        $directory = (new DirectoryBuilder())->withTitle('Origin Name')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_POST,
            '/library/directory/' . $directory->getId() . '/edit',
            [
                'directory' => [
                    'title' => 'New Name',
                    'parent' => RootDirectory::ID,
                ],
            ],
        );

        // -------------- Asserts --------------

        self::assertResponseRedirects('/library/' . $directory->getId());
        $this->client->followRedirect();

        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains('ol.breadcrumb li:nth-child(1)', 'Hauptverzeichnis');
        self::assertSelectorTextContains('li.active', 'New Name');
    }

    #[Test]
    public function itCanChangeTheParentDirectory(): void
    {
        // -------------- Setup Data Fixtures --------------

        $parentDirectory = (new DirectoryBuilder())->withTitle('Parent Directory')->build();
        $this->bus->dispatch(new StoreDirectory($parentDirectory));

        $directory = (new DirectoryBuilder())->withTitle('Origin Name')->build();
        $this->bus->dispatch(new StoreDirectory($directory));

        // -------------- Execute Test --------------

        $this->client->request(
            Request::METHOD_POST,
            '/library/directory/' . $directory->getId() . '/edit',
            [
                'directory' => [
                    'title' => 'Origin Name',
                    'parent' => $parentDirectory->getId(),
                ],
            ],
        );

        // -------------- Asserts --------------

        self::assertResponseRedirects('/library/' . $directory->getId());
        $this->client->followRedirect();

        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains('ol.breadcrumb li:nth-child(1)', 'Hauptverzeichnis');
        self::assertSelectorTextContains('li.active', $directory->getTitle());
    }
}
