<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview\WeekSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\WeekdayType;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\WeekType;
use ChronicleKeeper\Settings\Presentation\Twig\WeekForm;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(CalendarSettings\WeekSettings::class)]
#[CoversClass(CalendarSettings::class)]
#[CoversClass(WeekSettings::class)]
#[CoversClass(SettingsHandler::class)]
#[CoversClass(WeekForm::class)]
#[CoversClass(WeekType::class)]
#[CoversClass(WeekdayType::class)]
#[Large]
final class WeekSettingsTest extends WebTestCase
{
    #[Test]
    public function itShowsTheWeekForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/week');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Wochentage');
        self::assertSelectorExists('form[name="week"]');
        self::assertSelectorExists('#week_weekdays_add');
    }

    #[Test]
    public function itShowsAFilledWeekForm(): void
    {
        $settings = (new SettingsBuilder())->withDefaultCalendarSettings()->build();

        $originSettings = $this->settingsHandler->get();
        $originSettings->setCalendarSettings($settings->getCalendarSettings());
        $this->settingsHandler->store();

        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/week');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Wochentage');
        self::assertSelectorExists('form[name="week"]');
        self::assertSelectorExists('#week_weekdays_add');

        self::assertFormValue('form[name="week"]', 'week[weekdays][0][index]', '1');
        self::assertFormValue('form[name="week"]', 'week[weekdays][0][name]', 'First Day');
    }

    #[Test]
    public function itIsAbleToStoreWeekdays(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/week', [
            'week' => [
                'weekdays' => [
                    ['index' => 1, 'name' => 'Firstday'],
                    ['index' => 2, 'name' => 'Secondday'],
                ],
            ],
        ]);

        self::assertResponseRedirects('/settings/calendar_overview');

        $settings         = $this->settingsHandler->get();
        $calendarSettings = $settings->getCalendarSettings();

        self::assertCount(2, $calendarSettings->getWeeks());

        $weekdays = $calendarSettings->getWeeks();

        self::assertEquals('Firstday', $weekdays[0]->getName());
        self::assertEquals('Secondday', $weekdays[1]->getName());
        self::assertEquals(1, $weekdays[0]->getIndex());
        self::assertEquals(2, $weekdays[1]->getIndex());
    }

    #[Test]
    public function itExecutesAValidationToTheSubmittedData(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/week', [
            'week' => [
                'weekdays' => [
                    ['index' => 12, 'name' => 'Firstday'],
                    ['index' => 2, 'name' => 'Secondday'],
                ],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            '.alert-danger',
            'Die Indizes der Wochentage müssen sequentiell sein. Erwarteter Index: 1, tatsächlicher Index: 2.',
        );
    }
}
