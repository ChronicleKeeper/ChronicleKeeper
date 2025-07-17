<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\MonthSettings;
use ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview\LeapDaysSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\LeapDaysType;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\LeapDayType;
use ChronicleKeeper\Settings\Presentation\Twig\LeapDaysForm;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\LinearWithLeapDaysCalendarSettingsBuilder;
use ChronicleKeeper\Test\WebTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_filter;
use function reset;

#[CoversClass(LeapDaysSettings::class)]
#[CoversClass(MonthSettings::class)]
#[CoversClass(LeapDaysType::class)]
#[CoversClass(CalendarSettings::class)]
#[CoversClass(LeapDaysForm::class)]
#[CoversClass(LeapDayType::class)]
#[Large]
final class LeapDaysSettingsTest extends WebTestCase
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
    public function itFailsShowingTheLeapDaysFormForNonExistentMonth(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/leap_days/24');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function itShowsTheLeapDaysForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/leap_days/7');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Monatsansicht');
        self::assertSelectorExists('form[name="leap_days"]');
    }

    #[Test]
    public function itShowsAFilledLeapDaysForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/leap_days/7');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Monatsansicht');
        self::assertSelectorExists('form[name="leap_days"]');

        // The index field should not be editable
        self::assertNoFormValue('form[name="leap_days"]', 'leap_days[index]', '7');

        self::assertFormValue('form[name="leap_days"]', 'leap_days[name]', 'Arthan');
        self::assertFormValue('form[name="leap_days"]', 'leap_days[days]', '30');

        // And there should be two leap days already filled
        self::assertFormValue('form[name="leap_days"]', 'leap_days[leap_days][0][day]', '2');
        self::assertFormValue('form[name="leap_days"]', 'leap_days[leap_days][0][year_interval]', '4');
        self::assertFormValue('form[name="leap_days"]', 'leap_days[leap_days][0][name]', 'Shieldday');

        self::assertFormValue('form[name="leap_days"]', 'leap_days[leap_days][1][day]', '21');
        self::assertFormValue('form[name="leap_days"]', 'leap_days[leap_days][1][year_interval]', '');
        self::assertFormValue('form[name="leap_days"]', 'leap_days[leap_days][1][name]', 'Midsummer');
    }

    #[Test]
    public function itIsAbleToStoreTheForm(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/leap_days/7', [
            'leap_days' => [
                'name' => 'My Edited Month',
                'days' => '237',
                'leap_days' => [
                    0 => ['day' => '2', 'year_interval' => '4', 'name' => 'Shieldday'],
                    3 => ['day' => '3', 'year_interval' => '', 'name' => 'New Leap Day'],
                ],
            ],
        ]);

        self::assertResponseRedirects('/settings/calendar_overview');

        $settings         = $this->settingsHandler->get();
        $calendarSettings = $settings->getCalendarSettings();

        self::assertCount(12, $calendarSettings->getMonths());

        $filteredMonths = array_filter(
            $calendarSettings->getMonths(),
            static fn (MonthSettings $month) => $month->getIndex() === 7,
        );
        self::assertCount(1, $filteredMonths);

        $month = reset($filteredMonths);

        self::assertSame('My Edited Month', $month->getName());
        self::assertSame(237, $month->getDays());
        self::assertCount(2, $month->getLeapDays());

        self::assertSame(2, $month->getLeapDays()[0]->getDay());
        self::assertSame(4, $month->getLeapDays()[0]->getYearInterval());
        self::assertSame('Shieldday', $month->getLeapDays()[0]->getName());

        self::assertSame(3, $month->getLeapDays()[1]->getDay());
        self::assertNull($month->getLeapDays()[1]->getYearInterval());
        self::assertSame('New Leap Day', $month->getLeapDays()[1]->getName());
    }

    #[Test]
    public function itExecutesAValidationToTheSubmittedData(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/leap_days/7', [
            'leap_days' => [
                'name' => '',
                'days' => '',
                'leap_days' => [],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.invalid-feedback', 'This value should not be blank.');
    }
}
