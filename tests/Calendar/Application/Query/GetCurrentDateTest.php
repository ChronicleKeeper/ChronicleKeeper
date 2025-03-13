<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Application\Query;

use ChronicleKeeper\Calendar\Application\Query\GetCurrentDate;
use ChronicleKeeper\Calendar\Application\Query\GetCurrentDateQuery;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\OnlyRegularDaysCalendarSettingsBuilder;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetCurrentDate::class)]
#[CoversClass(GetCurrentDateQuery::class)]
#[Small]
final class GetCurrentDateTest extends TestCase
{
    private QueryService&Stub $queryService;
    private SettingsHandler&Stub $settingsHandler;

    protected function setUp(): void
    {
        $this->queryService = self::createStub(QueryService::class);
        $this->queryService
            ->method('query')
            ->willReturn(ExampleCalendars::getOnlyRegularDays());

        $this->settingsHandler = self::createStub(SettingsHandler::class);
    }

    protected function tearDown(): void
    {
        unset($this->queryService, $this->settingsHandler);
    }

    #[Test]
    public function itHasTheCorrectQueryClass(): void
    {
        self::assertSame(GetCurrentDateQuery::class, (new GetCurrentDate())->getQueryClass());
    }

    #[Test]
    public function itDeliversADefaultDateWithoutSetting(): void
    {
        $this->settingsHandler->method('get')->willReturn((new SettingsBuilder())->build());

        $query       = new GetCurrentDateQuery($this->queryService, $this->settingsHandler);
        $currentDate = $query->query(new GetCurrentDate());

        self::assertSame(0, $currentDate->getYear());
        self::assertSame(1, $currentDate->getMonth());
        self::assertSame(1, $currentDate->getDay()->getDayOfTheMonth());
    }

    #[Test]
    public function itDeliversTheCurrentDateWhenSet(): void
    {
        $this->settingsHandler->method('get')->willReturn(
            (new SettingsBuilder())
                ->withCalendarSettings((new OnlyRegularDaysCalendarSettingsBuilder())->build())
                ->build(),
        );

        $query       = new GetCurrentDateQuery($this->queryService, $this->settingsHandler);
        $currentDate = $query->query(new GetCurrentDate());

        self::assertSame(1985, $currentDate->getYear());
        self::assertSame(9, $currentDate->getMonth());
        self::assertSame(11, $currentDate->getDay()->getDayOfTheMonth());
    }
}
