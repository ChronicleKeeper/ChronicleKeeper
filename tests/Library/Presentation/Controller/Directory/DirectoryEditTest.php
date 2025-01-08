<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Presentation\Controller\Directory;

use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Presentation\Controller\Directory\DirectoryEdit;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(DirectoryEdit::class)]
#[Large]
class DirectoryEditTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/create_directory',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Neues Verzeichnis');
    }
}
