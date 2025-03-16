<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller;

use ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CalendarOverview::class)]
#[Large]
final class CalendarOverviewTest extends WebTestCase
{
    #[Test]
    public function itDisplaysCalendarSettings(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview');

        self::assertResponseIsSuccessful();

        $content = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString('Einstellungen - Kalender', $content);
    }
}
