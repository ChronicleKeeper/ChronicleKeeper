<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Application\Service;

use ChronicleKeeper\Calendar\Application\Exception\CalendarConfigurationIncomplete;
use ChronicleKeeper\Calendar\Application\Service\CalendarSettingsChecker;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\CalendarSettings\CurrentDay;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalendarSettingsChecker::class)]
#[CoversClass(CalendarConfigurationIncomplete::class)]
#[Small]
final class CalendarSettingsCheckerTest extends TestCase
{
    #[Test]
    public function itAllowsCompleteSettings(): void
    {
        $calendarSettings = $this->createMock(CalendarSettings::class);
        $calendarSettings->method('getCurrentDay')->willReturn($this->createMock(CurrentDay::class));
        $calendarSettings->method('getEpochs')->willReturn(['epoch1']);
        $calendarSettings->method('getMonths')->willReturn(['month1']);
        $calendarSettings->method('getWeeks')->willReturn(['week1']);

        $settings = $this->createMock(Settings::class);
        $settings->method('getCalendarSettings')->willReturn($calendarSettings);

        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->method('get')->willReturn($settings);

        $checker = new CalendarSettingsChecker($settingsHandler);

        // No exception should be thrown
        self::assertTrue($checker->hasValidSettings());
    }

    /**
     * @param string[] $epochs
     * @param string[] $months
     * @param string[] $weeks
     * @param string[] $expectedMissingSettings
     */
    #[Test]
    #[DataProvider('provideIncompleteSetting')]
    public function itRejectsIncompleteSettings(
        CurrentDay|null $currentDay,
        array $epochs,
        array $months,
        array $weeks,
        array $expectedMissingSettings,
    ): void {
        $calendarSettings = $this->createMock(CalendarSettings::class);
        $calendarSettings->method('getCurrentDay')->willReturn($currentDay);
        $calendarSettings->method('getEpochs')->willReturn($epochs);
        $calendarSettings->method('getMonths')->willReturn($months);
        $calendarSettings->method('getWeeks')->willReturn($weeks);

        $settings = $this->createMock(Settings::class);
        $settings->method('getCalendarSettings')->willReturn($calendarSettings);

        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->method('get')->willReturn($settings);

        $checker = new CalendarSettingsChecker($settingsHandler);

        $this->expectException(CalendarConfigurationIncomplete::class);

        try {
            $checker->hasValidSettings();
        } catch (CalendarConfigurationIncomplete $e) {
            self::assertSame($expectedMissingSettings, $e->missingSettings);

            throw $e;
        }
    }

    /**
     * @param string[] $epochs
     * @param string[] $months
     * @param string[] $weeks
     * @param string[] $expectedMissingSettings
     */
    #[Test]
    #[DataProvider('provideIncompleteSetting')]
    public function itRejectsIncompleteSettingsWithABoolean(
        CurrentDay|null $currentDay,
        array $epochs,
        array $months,
        array $weeks,
        array $expectedMissingSettings,
    ): void {
        $calendarSettings = $this->createMock(CalendarSettings::class);
        $calendarSettings->method('getCurrentDay')->willReturn($currentDay);
        $calendarSettings->method('getEpochs')->willReturn($epochs);
        $calendarSettings->method('getMonths')->willReturn($months);
        $calendarSettings->method('getWeeks')->willReturn($weeks);

        $settings = $this->createMock(Settings::class);
        $settings->method('getCalendarSettings')->willReturn($calendarSettings);

        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->method('get')->willReturn($settings);

        $checker = new CalendarSettingsChecker($settingsHandler);

        self::assertFalse($checker->hasValidSettings(false));
    }

    public static function provideIncompleteSetting(): Generator
    {
        $currentDay = self::createStub(CurrentDay::class);

        yield 'Missing current day' => [
            null,
            ['epoch1'],
            ['month1'],
            ['week1'],
            ['Das Aktuelles Datum (Heute) fehlt.'],
        ];

        yield 'Missing epochs' => [
            $currentDay,
            [],
            ['month1'],
            ['week1'],
            ['Mindestens eine Epochen (Ära) erforderlich.'],
        ];

        yield 'Missing months' => [
            $currentDay,
            ['epoch1'],
            [],
            ['week1'],
            ['Mindestens ein Monat erforderlich.'],
        ];

        yield 'Missing weeks' => [
            $currentDay,
            ['epoch1'],
            ['month1'],
            [],
            ['Es sollte mindestens einen Wochentag geben.'],
        ];

        yield 'Missing all settings' => [
            null,
            [],
            [],
            [],
            [
                'Das Aktuelles Datum (Heute) fehlt.',
                'Mindestens eine Epochen (Ära) erforderlich.',
                'Mindestens ein Monat erforderlich.',
                'Es sollte mindestens einen Wochentag geben.',
            ],
        ];

        yield 'Missing current day and epochs' => [
            null,
            [],
            ['month1'],
            ['week1'],
            [
                'Das Aktuelles Datum (Heute) fehlt.',
                'Mindestens eine Epochen (Ära) erforderlich.',
            ],
        ];

        yield 'Missing months and weeks' => [
            $currentDay,
            ['epoch1'],
            [],
            [],
            [
                'Mindestens ein Monat erforderlich.',
                'Es sollte mindestens einen Wochentag geben.',
            ],
        ];
    }
}
