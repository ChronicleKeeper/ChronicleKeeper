<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Presentation\Controller;

use ChronicleKeeper\Calendar\Presentation\Controller\Calendar;
use ChronicleKeeper\Calendar\Presentation\Twig\TableCalendar;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Calendar::class)]
#[CoversClass(TableCalendar::class)]
#[Large]
final class CalendarTest extends WebTestCase
{
    #[Test]
    public function itHasALoadablePageForTheCurrentDay(): void
    {
        // -------------------- Setup Data -------------------- //

        // Currently not needed as the loader has a static calendar!

        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Taranis 1262 nach der Flut');
    }

    #[Test]
    public function itIsRenderingTheRequestedMonthOfCalendar(): void
    {
        // -------------------- Setup Data -------------------- //

        // Currently not needed as the loader has a static calendar!

        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/0/3');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Brigid 0 nach der Flut');
    }

    #[Test]
    public function itIsRedirectingToTheFirstValidYear(): void
    {
        // -------------------- Setup Data -------------------- //

        // Currently not needed as the loader has a static calendar!

        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/-12/5');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseRedirects('/calendar/0/1');
    }

    #[Test]
    public function itIsRedirectingToTheLastMonthInPreviousYearIfMonthIsTooLow(): void
    {
        // -------------------- Setup Data -------------------- //

        // Currently not needed as the loader has a static calendar!

        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/1/-5');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseRedirects('/calendar/0/12');
    }

    #[Test]
    public function itIsRedirectingToTheFirstMonthInNextYearIfMonthIsTooHigh(): void
    {
        // -------------------- Setup Data -------------------- //

        // Currently not needed as the loader has a static calendar!

        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/0/13');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseRedirects('/calendar/1/1');
    }

    #[Test]
    public function itIsRedirectingToTheFirstYearFirstMonthIfTooLowMonthLeadsToInvalidYear(): void
    {
        // -------------------- Setup Data -------------------- //

        // Currently not needed as the loader has a static calendar!

        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/0/0');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseRedirects('/calendar/0/1');
    }
}
