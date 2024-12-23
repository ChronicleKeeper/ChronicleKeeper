<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Holiday as CalendarSettings;
use ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool\Holiday;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Holiday::class)]
#[Small]
class HolidayTest extends TestCase
{
    #[Test]
    public function itReturnsHolidayDescription(): void
    {
        $settingsHandler    = $this->createMock(SettingsHandler::class);
        $runtimeCollector   = $this->createMock(RuntimeCollector::class);
        $holidayDescription = 'This is the holiday description.';

        $settings = (new SettingsBuilder())
            ->withHoliday(new CalendarSettings(description: $holidayDescription))
            ->build();

        $settingsHandler->method('get')
            ->willReturn($settings);

        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(new FunctionDebug('calendar_holiday', [], $holidayDescription));

        $holiday = new Holiday($settingsHandler, $runtimeCollector);
        $result  = $holiday();

        self::assertSame($holidayDescription, $result);
    }

    #[Test]
    public function itReturnsNoInformationMessageWhenDescriptionIsEmpty(): void
    {
        $settingsHandler  = $this->createMock(SettingsHandler::class);
        $runtimeCollector = $this->createMock(RuntimeCollector::class);

        $settings = (new SettingsBuilder())
            ->withHoliday(new CalendarSettings(description: ''))
            ->build();

        $settingsHandler->method('get')
            ->willReturn($settings);

        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(new FunctionDebug('calendar_holiday'));

        $holiday = new Holiday($settingsHandler, $runtimeCollector);
        $result  = $holiday();

        self::assertSame('Es gibt keine Informationen zu Feiertagen.', $result);
    }
}
