<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Command;

use ChronicleKeeper\Settings\Application\Command\DeleteSystemPrompt;
use ChronicleKeeper\Settings\Application\Command\DeleteSystemPromptHandler;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(DeleteSystemPrompt::class)]
#[CoversClass(DeleteSystemPromptHandler::class)]
#[Small]
final class DeleteSystemPromptTest extends TestCase
{
    #[Test]
    public function itHasAConstructableCommand(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->asUser()->build();
        $command      = new DeleteSystemPrompt($systemPrompt);
        self::assertSame($systemPrompt, $command->systemPrompt);
    }

    #[Test]
    public function itHasACommandThatFailsOnSystemPrompts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('System prompts cannot be stored.');

        $systemPrompt = (new SystemPromptBuilder())->asSystem()->build();

        new DeleteSystemPrompt($systemPrompt);
    }

    #[Test]
    public function itIsNotDeletableIfTheSystemPromptFileIsMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The system prompts file does not exist.');

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->method('exists')->willReturn(false);

        $command = new DeleteSystemPrompt((new SystemPromptBuilder())->asUser()->build());
        $handler = new DeleteSystemPromptHandler(
            $fileAccess,
            self::createStub(SerializerInterface::class),
        );
        $handler($command);
    }

    #[Test]
    public function itDeletesASystemPrompt(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->asUser()->build();

        $fileAccess = $this->createMock(FileAccess::class);

        $fileAccess->method('exists')->willReturn(true);
        $fileAccess
            ->method('read')
            ->willReturn(json_encode([$systemPrompt->getId() => $systemPrompt->jsonSerialize()], JSON_THROW_ON_ERROR));

        $fileAccess->expects($this->once())->method('write')->with(
            'storage',
            'system_prompts.json',
            '[]',
        );

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->once())->method('serialize')->willReturn('[]');

        $command = new DeleteSystemPrompt($systemPrompt);
        $handler = new DeleteSystemPromptHandler($fileAccess, $serializer);
        $handler($command);
    }
}
