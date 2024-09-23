<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Test\Library\Presentation\Controller\Document;

use DZunke\NovDoc\Library\Domain\RootDirectory;
use DZunke\NovDoc\Library\Presentation\Controller\Document\DocumentCreation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(DocumentCreation::class)]
#[Large]
class DocumentCreationTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $client = static::createClient();
        $client->request(
            Request::METHOD_GET,
            '/library/directory/' . RootDirectory::ID . '/create_document',
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h2', 'Neues Dokument');
    }
}
