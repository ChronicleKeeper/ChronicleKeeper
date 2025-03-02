<?php

declare(strict_types=1);

namespace Calendar\Presentation\Controller;

use ChronicleKeeper\Calendar\Application\Service\CalendarSettingsChecker;
use ChronicleKeeper\Calendar\Presentation\Controller\Calendar;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Calendar::class)]
#[CoversClass(CalendarSettingsChecker::class)]
#[Large]
final class InvalidCalendarTest extends WebTestCase
{
    #[Test]
    public function itIsLoadingAnEmptyCalendar(): void
    {
        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Kalender - Unvollständige Konfiguration');

        $pageContent = (string) $this->client->getResponse()->getContent();

        self::assertStringContainsString('Das Aktuelles Datum (Heute) fehlt.', $pageContent);
        self::assertStringContainsString('Mindestens eine Epochen (Ära) erforderlich.', $pageContent);
        self::assertStringContainsString('Mindestens ein Monat erforderlich.', $pageContent);
        self::assertStringContainsString('Es sollte mindestens einen Wochentag geben.', $pageContent);
    }
}
