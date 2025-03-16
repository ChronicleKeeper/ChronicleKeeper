<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Calendar\Application\Query\GetCurrentDate;
use ChronicleKeeper\Calendar\Application\Query\GetCurrentDateQuery;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview\MoveCurrentDay;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\LinearWithLeapDaysCalendarSettingsBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(MoveCurrentDay::class)]
#[CoversClass(CurrentDay::class)]
#[CoversClass(GetCurrentDate::class)]
#[CoversClass(GetCurrentDateQuery::class)]
#[Large]
final class MoveCurrentDayTest extends WebTestCase
{
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $originSettings = $this->settingsHandler->get();
        $originSettings->setCalendarSettings((new LinearWithLeapDaysCalendarSettingsBuilder())->build());
        $this->settingsHandler->store();
    }

    #[Test]
    public function itMovesTheCurrentDayForward(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/add_days/1');

        self::assertResponseRedirects('/calendar');

        $calendarSettings = $this->settingsHandler->get()->getCalendarSettings();
        $currentDay       = $calendarSettings->getCurrentDay();

        self::assertNotNull($currentDay);
        self::assertSame(2163, $currentDay->getYear());
        self::assertSame(6, $currentDay->getMonth());
        self::assertSame(8, $currentDay->getDay());
    }

    #[Test]
    public function itMovesTheCurrentDayForwardSomeMoreDays(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/add_days/2136');

        self::assertResponseRedirects('/calendar');

        $calendarSettings = $this->settingsHandler->get()->getCalendarSettings();
        $currentDay       = $calendarSettings->getCurrentDay();

        self::assertNotNull($currentDay);
        self::assertSame(2169, $currentDay->getYear());
        self::assertSame(4, $currentDay->getMonth());
        self::assertSame(12, $currentDay->getDay());
    }

    #[Test]
    public function itMovesTheCurrentDayBackwards(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/sub_days/1');

        self::assertResponseRedirects('/calendar');

        $calendarSettings = $this->settingsHandler->get()->getCalendarSettings();
        $currentDay       = $calendarSettings->getCurrentDay();

        self::assertNotNull($currentDay);
        self::assertSame(2163, $currentDay->getYear());
        self::assertSame(6, $currentDay->getMonth());
        self::assertSame(6, $currentDay->getDay());
    }

    #[Test]
    public function itMovesTheCurrentDayBackwardsSomeMoreDays(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/sub_days/5687');

        self::assertResponseRedirects('/calendar');

        $calendarSettings = $this->settingsHandler->get()->getCalendarSettings();
        $currentDay       = $calendarSettings->getCurrentDay();

        self::assertNotNull($currentDay);
        self::assertSame(2147, $currentDay->getYear());
        self::assertSame(11, $currentDay->getMonth());
        self::assertSame(12, $currentDay->getDay());
    }
}
