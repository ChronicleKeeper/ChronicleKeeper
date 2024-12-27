<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service;

use ChronicleKeeper\Settings\Application\Service\SystemPromptLoader;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(SystemPromptLoader::class)]
#[Small]
final class SystemPromptLoaderTest extends TestCase
{
    #[Test]
    public function itLoadsSystemPrompts(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(LoadSystemPrompts::class))
            ->willReturnCallback(
                static function (LoadSystemPrompts $event): LoadSystemPrompts {
                    $event->add((new SystemPromptBuilder())->build());

                    return $event;
                },
            );

        $loader  = new SystemPromptLoader($eventDispatcher);
        $prompts = $loader->load();

        self::assertCount(1, $prompts);
    }
}
