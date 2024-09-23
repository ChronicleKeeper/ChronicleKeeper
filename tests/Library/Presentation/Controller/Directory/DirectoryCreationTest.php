<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Library\Presentation\Controller\Directory;

use DZunke\NovDoc\Library\Domain\RootDirectory;
use DZunke\NovDoc\Library\Presentation\Controller\Directory\DirectoryCreation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(DirectoryCreation::class)]
#[Large]
class DirectoryCreationTest extends WebTestCase
{
    public function testThatRequestingTheRootDirectoryIsForbidden(): void
    {
        $client = static::createClient();
        $client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/edit',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
