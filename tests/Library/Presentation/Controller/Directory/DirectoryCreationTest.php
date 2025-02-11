<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Presentation\Controller\Directory;

use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Presentation\Controller\Directory\DirectoryCreation;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(DirectoryCreation::class)]
#[Large]
class DirectoryCreationTest extends WebTestCase
{
    #[Test]
    public function itIsShowingTheDirectoryCreationForm(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/create_directory',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Neues Verzeichnis');

        self::assertFormValue('form', 'directory[title]', '');
        self::assertFormValue('form', 'directory[parent]', RootDirectory::ID);
    }

    #[Test]
    public function itIsStoringANewDirectory(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/library/directory/' . RootDirectory::ID . '/create_directory',
            [
                'directory' => [
                    'title' => 'New Directory',
                    'parent' => RootDirectory::ID,
                ],
            ],
        );

        self::assertResponseRedirects();
        $this->client->followRedirect();

        self::assertSelectorTextContains('h2', 'Bibliothek');
        self::assertSelectorTextContains('li.active', 'New Directory');
    }

    #[Test]
    public function itIsNotStoringANewDirectoryWithEmptyTitle(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/library/directory/' . RootDirectory::ID . '/create_directory',
            [
                'directory' => [
                    'title' => '',
                    'parent' => RootDirectory::ID,
                ],
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Neues Verzeichnis');
        self::assertSelectorTextContains('form', 'This value should not be blank.');
    }
}
