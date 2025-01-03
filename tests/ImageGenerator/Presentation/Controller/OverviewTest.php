<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Presentation\Controller\Overview;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Overview::class)]
#[Large]
class OverviewTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $this->client->request(Request::METHOD_GET, '/image_generator');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Künste der Mechthild');
    }
}
