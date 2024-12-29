<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Application\Service;

use ChronicleKeeper\Settings\Application\Service\SystemPromptLoader;
use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_combine;
use function array_map;

#[CoversClass(SystemPromptRegistry::class)]
#[Small]
final class SystemPromptRegistryTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $prompts = [(new SystemPromptBuilder())->build()];

        $systemPromptLoader = $this->createMock(SystemPromptLoader::class);
        $systemPromptLoader
            ->expects($this->once())
            ->method('load')
            ->willReturn($prompts);

        $registry = new SystemPromptRegistry($systemPromptLoader);

        // Get prompts from reflection to check if the same as with the mock is in the property
        $reflection = new ReflectionClass($registry);
        $property   = $reflection->getProperty('prompts');

        self::assertSame($prompts, $property->getValue($registry));
    }

    #[Test]
    public function itGetsAllPrompts(): void
    {
        $prompts = [
            (new SystemPromptBuilder())->build(),
            (new SystemPromptBuilder())->build(),
        ];

        $systemPromptLoader = $this->createMock(SystemPromptLoader::class);
        $systemPromptLoader
            ->expects($this->once())
            ->method('load')
            ->willReturn(array_combine(array_map(static fn ($prompt) => $prompt->getId(), $prompts), $prompts));

        $registry = new SystemPromptRegistry($systemPromptLoader);

        $allPrompts = $registry->all();

        self::assertCount(2, $allPrompts);
        self::assertSame($prompts[0], $allPrompts[0]);
        self::assertSame($prompts[1], $allPrompts[1]);
    }

    #[Test]
    public function itGetsDefaultForPurposeWithoutDefaultEntry(): void
    {
        $prompts = [(new SystemPromptBuilder())->withPurpose(Purpose::CONVERSATION)->build()];

        $systemPromptLoader = $this->createMock(SystemPromptLoader::class);
        $systemPromptLoader
            ->expects($this->once())
            ->method('load')
            ->willReturn($prompts);

        $registry = new SystemPromptRegistry($systemPromptLoader);

        $prompt = $registry->getDefaultForPurpose(Purpose::CONVERSATION);

        self::assertSame($prompts[0], $prompt);
    }

    #[Test]
    public function itGetsDefaultForPurposeWithDefaultEntry(): void
    {
        $prompts = [
            (new SystemPromptBuilder())->withPurpose(Purpose::CONVERSATION)->build(),
            (new SystemPromptBuilder())->withPurpose(Purpose::CONVERSATION)->asDefault()->build(),
        ];

        $systemPromptLoader = $this->createMock(SystemPromptLoader::class);
        $systemPromptLoader
            ->expects($this->once())
            ->method('load')
            ->willReturn($prompts);

        $registry = new SystemPromptRegistry($systemPromptLoader);

        $prompt = $registry->getDefaultForPurpose(Purpose::CONVERSATION);

        self::assertSame($prompts[1], $prompt);
    }

    #[Test]
    public function itCanFindByPurpose(): void
    {
        $prompts = [
            (new SystemPromptBuilder())->withPurpose(Purpose::CONVERSATION)->build(),
            (new SystemPromptBuilder())->withPurpose(Purpose::CONVERSATION)->build(),
            (new SystemPromptBuilder())->withPurpose(Purpose::IMAGE_GENERATOR_OPTIMIZER)->build(),
        ];

        $systemPromptLoader = $this->createMock(SystemPromptLoader::class);
        $systemPromptLoader
            ->expects($this->once())
            ->method('load')
            ->willReturn($prompts);

        $registry = new SystemPromptRegistry($systemPromptLoader);

        $foundPrompts = $registry->findByPurpose(Purpose::CONVERSATION);

        self::assertCount(2, $foundPrompts);
        self::assertSame($prompts[0], $foundPrompts[0]);
        self::assertSame($prompts[1], $foundPrompts[1]);
    }

    #[Test]
    public function itCanGetById(): void
    {
        $prompts = [
            (new SystemPromptBuilder())->build(),
            (new SystemPromptBuilder())->build(),
        ];

        $systemPromptLoader = $this->createMock(SystemPromptLoader::class);
        $systemPromptLoader
            ->expects($this->once())
            ->method('load')
            ->willReturn(array_combine(array_map(static fn ($prompt) => $prompt->getId(), $prompts), $prompts));

        $registry = new SystemPromptRegistry($systemPromptLoader);

        $prompt = $registry->getById($prompts[1]->getId());

        self::assertSame($prompts[1], $prompt);
    }

    #[Test]
    public function itThrowsAnExceptionOnNotFoundGettingById(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prompt not found');

        $systemPromptLoader = $this->createMock(SystemPromptLoader::class);
        $systemPromptLoader
            ->expects($this->once())
            ->method('load')
            ->willReturn([]);

        $registry = new SystemPromptRegistry($systemPromptLoader);
        $registry->getById('foo');
    }
}
