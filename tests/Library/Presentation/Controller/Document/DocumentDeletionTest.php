<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Presentation\Controller\Document;

use ChronicleKeeper\Library\Presentation\Controller\Document\DocumentCreation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

#[CoversClass(DocumentCreation::class)]
#[Large]
class DocumentDeletionTest extends WebTestCase
{
    public function testThatThePageResponseWith404OnUnknownDocument(): void
    {
        $documentId = Uuid::v4()->toString();

        $client = static::createClient();
        $client->request(
            Request::METHOD_GET,
            '/library/document/' . $documentId . '/delete',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
