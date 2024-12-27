<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service;

use ChronicleKeeper\Settings\Application\Service\SystemPromptLoader;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(SystemPromptLoader::class)]
#[Small]
final class SystemPromptLoaderTest extends TestCase
{
    #[Test]
    public function itLoadsSystemPrompts(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->build();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(LoadSystemPrompts::class))
            ->willReturnCallback(
                static function (LoadSystemPrompts $event) use ($systemPrompt): LoadSystemPrompts {
                    $event->add($systemPrompt);

                    return $event;
                },
            );

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())->method('exists')->willReturn(false);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->never())->method('deserialize');

        $loader  = new SystemPromptLoader($eventDispatcher, $fileAccess, $serializer);
        $prompts = $loader->load();

        self::assertCount(1, $prompts);
        self::assertArrayHasKey($systemPrompt->getId(), $prompts);
        self::assertSame($systemPrompt, $prompts[$systemPrompt->getId()]);
    }

    #[Test]
    public function itLoadsUserPrompts(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->build();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(LoadSystemPrompts::class))
            ->willReturnCallback(
                static function (LoadSystemPrompts $event) use ($systemPrompt): LoadSystemPrompts {
                    $event->add($systemPrompt);

                    return $event;
                },
            );

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())->method('exists')->willReturn(true);
        $fileAccess->expects($this->once())->method('read')->willReturn('{"foo": true}');

        $userPrompt = (new SystemPromptBuilder())->build();

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('{"foo": true}', SystemPrompt::class . '[]', 'json')
            ->willReturn([$userPrompt->getId() => $userPrompt]);

        $loader  = new SystemPromptLoader($eventDispatcher, $fileAccess, $serializer);
        $prompts = $loader->load();

        self::assertCount(2, $prompts);

        self::assertArrayHasKey($systemPrompt->getId(), $prompts);
        self::assertSame($systemPrompt, $prompts[$systemPrompt->getId()]);

        self::assertArrayHasKey($userPrompt->getId(), $prompts);
        self::assertSame($userPrompt, $prompts[$userPrompt->getId()]);
    }

    #[Test]
    public function itIsSortingByPurposeAndName(): void
    {
        $systemPrompt1 = (new SystemPromptBuilder())
            ->withPurpose(Purpose::CONVERSATION)
            ->withName('Foo Foo')
            ->build();

        $systemPrompt2 = (new SystemPromptBuilder())
            ->withPurpose(Purpose::IMAGE_UPLOAD)
            ->withName('Foo Foo')
            ->build();

        $systemPrompt3 = (new SystemPromptBuilder())
            ->withPurpose(Purpose::IMAGE_UPLOAD)
            ->withName('Bar Bar')
            ->build();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(self::isInstanceOf(LoadSystemPrompts::class))
            ->willReturnCallback(
                static function (LoadSystemPrompts $event) use (
                    $systemPrompt1,
                    $systemPrompt2,
                    $systemPrompt3,
                ): LoadSystemPrompts {
                    $event->add($systemPrompt1);
                    $event->add($systemPrompt2);
                    $event->add($systemPrompt3);

                    return $event;
                },
            );

        $fileAccess = $this->createMock(FileAccess::class);
        $fileAccess->expects($this->once())->method('exists')->willReturn(false);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects($this->never())->method('deserialize');

        $loader  = new SystemPromptLoader($eventDispatcher, $fileAccess, $serializer);
        $prompts = $loader->load();

        self::assertCount(3, $prompts);

        $expectedPrompts = [
            $systemPrompt1->getId() => $systemPrompt1,
            $systemPrompt3->getId() => $systemPrompt3,
            $systemPrompt2->getId() => $systemPrompt2,
        ];

        self::assertSame($expectedPrompts, $prompts);
    }
}
