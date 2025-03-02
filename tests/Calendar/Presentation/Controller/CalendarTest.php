<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Presentation\Controller;

use ChronicleKeeper\Calendar\Application\Query\GetCurrentDate;
use ChronicleKeeper\Calendar\Application\Query\GetCurrentDateQuery;
use ChronicleKeeper\Calendar\Application\Query\LoadCalendar;
use ChronicleKeeper\Calendar\Application\Query\LoadCalendarQuery;
use ChronicleKeeper\Calendar\Application\Service\CalendarFactory;
use ChronicleKeeper\Calendar\Presentation\Controller\Calendar;
use ChronicleKeeper\Calendar\Presentation\Twig\TableCalendar;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\LinearWithLeapDaysCalendarSettingsBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(Calendar::class)]
#[CoversClass(LoadCalendar::class)]
#[CoversClass(LoadCalendarQuery::class)]
#[CoversClass(CalendarFactory::class)]
#[CoversClass(TableCalendar::class)]
#[CoversClass(GetCurrentDate::class)]
#[CoversClass(GetCurrentDateQuery::class)]
#[Large]
final class CalendarTest extends WebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $settings = $this->settingsHandler->get();
        $settings->setCalendarSettings((new LinearWithLeapDaysCalendarSettingsBuilder())->build());
        $this->settingsHandler->store();
    }

    #[Test]
    public function itHasALoadablePageForTheCurrentDay(): void
    {
        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Litha 2163 after the Flood');
    }

    #[Test]
    public function itIsRenderingTheRequestedMonthOfCalendar(): void
    {
        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/0/3');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Brigid 0 after the Flood');
    }

    #[Test]
    public function itIsRedirectingToTheFirstValidYear(): void
    {
        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/-12/5');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseRedirects('/calendar/0/1');
    }

    #[Test]
    public function itIsRedirectingToTheLastMonthInPreviousYearIfMonthIsTooLow(): void
    {
        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/1/-5');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseRedirects('/calendar/0/12');
    }

    #[Test]
    public function itIsRedirectingToTheFirstMonthInNextYearIfMonthIsTooHigh(): void
    {
        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/0/13');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseRedirects('/calendar/1/1');
    }

    #[Test]
    public function itIsRedirectingToTheFirstYearFirstMonthIfTooLowMonthLeadsToInvalidYear(): void
    {
        // -------------------- Test Execution -------------------- //

        $this->client->request(Request::METHOD_GET, '/calendar/0/0');

        // -------------------- Test Assertions -------------------- //

        self::assertResponseRedirects('/calendar/0/1');
    }
}
