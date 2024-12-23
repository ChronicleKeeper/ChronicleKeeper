<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Calendar as CalendarSettings;
use ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool\Calendar;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calendar::class)]
#[Small]
class CalendarTest extends TestCase
{
    #[Test]
    public function itReturnsCalendarDescription(): void
    {
        $settingsHandler     = $this->createMock(SettingsHandler::class);
        $runtimeCollector    = $this->createMock(RuntimeCollector::class);
        $calendarDescription = 'This is the calendar description.';

        $settings = (new SettingsBuilder())
            ->withCalendar(new CalendarSettings(calendarDescription: $calendarDescription))
            ->build();

        $settingsHandler->method('get')
            ->willReturn($settings);

        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(new FunctionDebug('calendar', [], $calendarDescription));

        $calendar = new Calendar($settingsHandler, $runtimeCollector);
        $result   = $calendar();

        self::assertSame($calendarDescription, $result);
    }

    #[Test]
    public function itReturnsNoInformationMessageWhenDescriptionIsEmpty(): void
    {
        $settingsHandler  = $this->createMock(SettingsHandler::class);
        $runtimeCollector = $this->createMock(RuntimeCollector::class);

        $settings = (new SettingsBuilder())
            ->withCalendar(new CalendarSettings(calendarDescription: ''))
            ->build();

        $settingsHandler->method('get')
            ->willReturn($settings);

        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(new FunctionDebug('calendar'));

        $calendar = new Calendar($settingsHandler, $runtimeCollector);
        $result   = $calendar();

        self::assertSame('Es gibt keine Informationen zum Kalender.', $result);
    }
}
