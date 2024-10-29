<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Presentation\Controller;

use ChronicleKeeper\ImageGenerator\Presentation\Controller\Overview;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(Overview::class)]
#[Large]
class OverviewTest extends WebTestCase
{
    public function testThatRequestingThePageIsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/image_generator');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Künste der Mechthild');
    }
}
