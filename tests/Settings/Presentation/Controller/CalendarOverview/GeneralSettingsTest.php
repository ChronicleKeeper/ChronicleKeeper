<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Presentation\Controller\CalendarOverview;

use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Presentation\Controller\CalendarOverview\GeneralSettings;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\CurrentDayType;
use ChronicleKeeper\Settings\Presentation\Form\Calendar\GeneralType;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\OnlyRegularDaysCalendarSettingsBuilder;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use ChronicleKeeper\Test\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(GeneralSettings::class)]
#[CoversClass(GeneralType::class)]
#[CoversClass(CalendarSettings::class)]
#[CoversClass(CurrentDayType::class)]
#[Large]
final class GeneralSettingsTest extends WebTestCase
{
    #[Test]
    public function itShowsTheGeneralForm(): void
    {
        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/general');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Allgemein');
        self::assertSelectorExists('form[name="general"]');
    }

    #[Test]
    public function itShowsAFilledGeneralForm(): void
    {
        $settings = (new SettingsBuilder())->withDefaultCalendarSettings()->build();

        $originSettings = $this->settingsHandler->get();
        $originSettings->setCalendarSettings($settings->getCalendarSettings());
        $this->settingsHandler->store();

        $this->client->request(Request::METHOD_GET, '/settings/calendar_overview/general');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Einstellungen - Kalender - Allgemein');
        self::assertSelectorExists('form[name="general"]');

        self::assertFormValue('form[name="general"]', 'general[current_day][year]', '1');
        self::assertFormValue('form[name="general"]', 'general[current_day][month]', '1');
        self::assertFormValue('form[name="general"]', 'general[current_day][day]', '1');
        self::assertFormValue('form[name="general"]', 'general[moonName]', 'Mond');
        self::assertFormValue('form[name="general"]', 'general[moonCycleDays]', '30');
        self::assertFormValue('form[name="general"]', 'general[moonCycleOffset]', '0');
    }

    #[Test]
    public function itIsAbleToStoreTheForm(): void
    {
        $originSettings = $this->settingsHandler->get();
        $originSettings->setCalendarSettings((new OnlyRegularDaysCalendarSettingsBuilder())->build());
        $this->settingsHandler->store();

        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/general', [
            'general' => [
                'current_day' => [
                    'year' => 12,
                    'month' => 3,
                    'day' => 4,
                ],
                'moonName' => 'My Moon',
                'moonCycleDays' => 12,
                'moonCycleOffset' => 2.5,
            ],
        ]);

        self::assertResponseRedirects('/settings/calendar_overview');

        $settings         = $this->settingsHandler->get();
        $calendarSettings = $settings->getCalendarSettings();

        self::assertSame(12, $calendarSettings->getCurrentDay()?->getYear());
        self::assertSame(3, $calendarSettings->getCurrentDay()->getMonth());
        self::assertSame(4, $calendarSettings->getCurrentDay()->getDay());
        self::assertSame('My Moon', $calendarSettings->getMoonName());
        self::assertSame(12.0, $calendarSettings->getMoonCycleDays());
        self::assertSame(2.5, $calendarSettings->getMoonCycleOffset());
    }

    #[Test]
    public function itExecutesAValidationToTheSubmittedData(): void
    {
        $this->client->request(Request::METHOD_POST, '/settings/calendar_overview/general', [
            'general' => [
                'current_day' => [
                    'year' => 3,
                    'month' => 24,
                    'day' => 16,
                ],
                'moonName' => 'My Moon',
                'moonCycleDays' => 12,
                'moonCycleOffset' => 2.5,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-danger', 'basierend auf deinen Einstellungen, kein gültiges Datum.');
    }
}
