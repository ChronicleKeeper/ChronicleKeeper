<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Calendar\Infrastructure\LLMChain\Memory;

use ChronicleKeeper\Calendar\Application\Service\CalendarSettingsChecker;
use ChronicleKeeper\Calendar\Infrastructure\LLMChain\Memory\CalendarSystemInfo;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Test\Calendar\Domain\Entity\Fixture\ExampleCalendars;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(CalendarSystemInfo::class)]
#[Small]
final class CalendarSystemInfoTest extends TestCase
{
    private QueryService&Stub $queryService;

    protected function setUp(): void
    {
        $this->queryService = self::createStub(QueryService::class);
        $this->queryService
            ->method('query')
            ->willReturn(ExampleCalendars::getFullFeatured());
    }

    #[Test]
    public function itReturnsMemoryWithCalendarInfo(): void
    {
        $calendarSettingsChecker = $this->createMock(CalendarSettingsChecker::class);
        $calendarSettingsChecker->method('hasValidSettings')->willReturn(true);

        $provider = new CalendarSystemInfo($this->queryService, $calendarSettingsChecker);
        $input    = new Input(
            self::createStub(Model::class),
            self::createStub(MessageBagInterface::class),
            [],
        );
        $memory   = $provider->loadMemory($input);

        self::assertNotEmpty($memory);

        $content = $memory[0]->content;
        self::assertStringContainsString('Fantasy Calendar Instructions', $content);
        self::assertStringContainsString('WEEK STRUCTURE:', $content);
        self::assertStringContainsString('MONTH STRUCTURE:', $content);
        self::assertStringContainsString('MOON CYCLE:', $content);
        self::assertStringContainsString('IMPORTANT NOTES FOR AI:', $content);
    }

    #[Test]
    public function itWillReturnEmptyMemoryIfSettingsAreInvalid(): void
    {
        $calendarSettingsChecker = $this->createMock(CalendarSettingsChecker::class);
        $calendarSettingsChecker->method('hasValidSettings')->willReturn(false);

        $provider = new CalendarSystemInfo($this->queryService, $calendarSettingsChecker);
        $input    = new Input(
            self::createStub(Model::class),
            self::createStub(MessageBagInterface::class),
            [],
        );
        $memory   = $provider->loadMemory($input);

        self::assertEmpty($memory);
    }
}
