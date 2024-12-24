<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\Calendar as CalendarSettings;
use ChronicleKeeper\Settings\Infrastructure\LLMChain\Tool\CurrentDate;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CurrentDate::class)]
#[Small]
class CurrentDateTest extends TestCase
{
    #[Test]
    public function itReturnsCurrentDate(): void
    {
        $settingsHandler  = $this->createMock(SettingsHandler::class);
        $runtimeCollector = $this->createMock(RuntimeCollector::class);
        $currentDate      = '1st of January, 2024';

        $settings = (new SettingsBuilder())
            ->withCalendar(new CalendarSettings(currentDate: $currentDate))
            ->build();

        $settingsHandler->method('get')
            ->willReturn($settings);

        $runtimeCollector->expects($this->once())
            ->method('addFunctionDebug')
            ->with(new FunctionDebug('current_date', result: 'Heute ist der ' . $currentDate));

        $currentDateTool = new CurrentDate($settingsHandler, $runtimeCollector);
        $result          = $currentDateTool();

        self::assertSame('Heute ist der ' . $currentDate, $result);
    }
}
