<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\MoonCalendar as CalendarSettings;
use ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool\MoonCalendar;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MoonCalendar::class)]
#[Small]
class MoonCalendarTest extends TestCase
{
    #[Test]
    public function itReturnsMoonCalendarDescription(): void
    {
        $settingsHandler         = $this->createMock(SettingsHandler::class);
        $runtimeCollector        = $this->createMock(RuntimeCollector::class);
        $moonCalendarDescription = 'This is the moon calendar description.';

        $settings = (new SettingsBuilder())
            ->withMoonCalendar(new CalendarSettings(moonCalendarDescription: $moonCalendarDescription))
            ->build();

        $settingsHandler->method('get')
            ->willReturn($settings);

        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(new FunctionDebug('moon_calendar', [], $moonCalendarDescription));

        $moonCalendar = new MoonCalendar($settingsHandler, $runtimeCollector);
        $result       = $moonCalendar();

        self::assertSame($moonCalendarDescription, $result);
    }

    #[Test]
    public function itReturnsNoInformationMessageWhenDescriptionIsEmpty(): void
    {
        $settingsHandler  = $this->createMock(SettingsHandler::class);
        $runtimeCollector = $this->createMock(RuntimeCollector::class);

        $settings = (new SettingsBuilder())
            ->withMoonCalendar(new CalendarSettings(moonCalendarDescription: ''))
            ->build();

        $settingsHandler->method('get')
            ->willReturn($settings);

        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(new FunctionDebug('moon_calendar'));

        $moonCalendar = new MoonCalendar($settingsHandler, $runtimeCollector);
        $result       = $moonCalendar();

        self::assertSame('Es gibt keine Informationen zum Mondkalender.', $result);
    }
}
