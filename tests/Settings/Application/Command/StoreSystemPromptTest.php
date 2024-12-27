<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Command;

use ChronicleKeeper\Settings\Application\Command\StoreSystemPrompt;
use ChronicleKeeper\Settings\Application\Command\StoreSystemPromptHandler;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(StoreSystemPrompt::class)]
#[CoversClass(StoreSystemPromptHandler::class)]
#[Small]
class StoreSystemPromptTest extends TestCase
{
    #[Test]
    public function itHasACreatableConmmand(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->build();
        $command      = new StoreSystemPrompt($systemPrompt);

        self::assertSame($systemPrompt, $command->systemPrompt);
    }

    #[Test]
    public function itStoresThePromptWithoutAnExistingFile(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->build();

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('exists')
            ->with('storage', 'system_prompts.json')
            ->willReturn(false);

        $fileAccess->expects($this->once())
            ->method('write')
            ->with('storage', 'system_prompts.json', '{"encoded": "data"}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with([$systemPrompt->getId() => $systemPrompt->jsonSerialize()])
            ->willReturn('{"encoded": "data"}');

        $handler = new StoreSystemPromptHandler($fileAccess, $serializer);
        $handler(new StoreSystemPrompt($systemPrompt));
    }

    #[Test]
    public function itStoresThePromptWithAnExistingFile(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->build();

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())
            ->method('exists')
            ->with('storage', 'system_prompts.json')
            ->willReturn(true);

        $fileAccess->expects($this->once())
            ->method('read')
            ->with('storage', 'system_prompts.json')
            ->willReturn('{"existing": {"existing": "data"}}');

        $fileAccess->expects($this->once())
            ->method('write')
            ->with('storage', 'system_prompts.json', '{"overhauled_data"}');

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(['existing' => ['existing' => 'data'], $systemPrompt->getId() => $systemPrompt->jsonSerialize()])
            ->willReturn('{"overhauled_data"}');

        $handler = new StoreSystemPromptHandler($fileAccess, $serializer);
        $handler(new StoreSystemPrompt($systemPrompt));
    }
}
