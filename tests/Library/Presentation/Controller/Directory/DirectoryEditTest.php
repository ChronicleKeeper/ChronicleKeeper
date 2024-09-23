<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Library\Presentation\Controller\Directory;

use DZunke\NovDoc\Library\Domain\RootDirectory;
use DZunke\NovDoc\Library\Presentation\Controller\Directory\DirectoryEdit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(DirectoryEdit::class)]
#[Large]
class DirectoryEditTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $client = static::createClient();
        $client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/create_directory',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Neues Verzeichnis');
    }
}
