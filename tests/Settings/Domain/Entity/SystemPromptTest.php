<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\Entity;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function json_encode;

use const JSON_THROW_ON_ERROR;

#[CoversClass(SystemPrompt::class)]
#[Small]
final class SystemPromptTest extends TestCase
{
    #[Test]
    public function itCanBeConstructed(): void
    {
        $systemPrompt = new SystemPrompt(
            'id',
            Purpose::CONVERSATION,
            'name',
            'content',
            true,
            false,
        );

        self::assertSame('id', $systemPrompt->getId());
        self::assertSame('conversation', $systemPrompt->getPurpose()->value);
        self::assertSame('name', $systemPrompt->getName());
        self::assertSame('content', $systemPrompt->getContent());
        self::assertTrue($systemPrompt->isSystem());
        self::assertFalse($systemPrompt->isDefault());
    }

    #[Test]
    public function itCanBeCreatedAsSystemPrompt(): void
    {
        $systemPrompt = SystemPrompt::createSystemPrompt(
            'id',
            Purpose::CONVERSATION,
            'name',
            'content',
        );

        self::assertSame('id', $systemPrompt->getId());
        self::assertSame('conversation', $systemPrompt->getPurpose()->value);
        self::assertSame('name', $systemPrompt->getName());
        self::assertSame('content', $systemPrompt->getContent());
        self::assertTrue($systemPrompt->isSystem());
        self::assertFalse($systemPrompt->isDefault());
    }

    #[Test]
    public function itCanBeCreated(): void
    {
        $systemPrompt = SystemPrompt::create(
            Purpose::CONVERSATION,
            'name',
            'content',
        );

        self::assertNotEmpty($systemPrompt->getId());
        self::assertSame('conversation', $systemPrompt->getPurpose()->value);
        self::assertSame('name', $systemPrompt->getName());
        self::assertSame('content', $systemPrompt->getContent());
        self::assertFalse($systemPrompt->isSystem());
        self::assertFalse($systemPrompt->isDefault());
    }

    #[Test]
    public function itCanBeCreatedAsDefault(): void
    {
        $systemPrompt = SystemPrompt::create(
            Purpose::CONVERSATION,
            'name',
            'content',
            true,
        );

        self::assertTrue($systemPrompt->isDefault());
    }

    #[Test]
    public function itCanBeRenamed(): void
    {
        $systemPrompt = SystemPrompt::create(
            Purpose::CONVERSATION,
            'name',
            'content',
        );

        $systemPrompt->rename('new name');

        self::assertSame('new name', $systemPrompt->getName());
    }

    #[Test]
    public function itCannotBeRenamedIfSystem(): void
    {
        $systemPrompt = SystemPrompt::createSystemPrompt(
            'id',
            Purpose::CONVERSATION,
            'name',
            'content',
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('System relevant prompts cannot be renamed.');

        $systemPrompt->rename('new name');
    }

    #[Test]
    public function itCannotBeRenamedIfSame(): void
    {
        $systemPrompt = SystemPrompt::create(
            Purpose::CONVERSATION,
            'name',
            'content',
        );

        $systemPrompt->rename('name');

        self::assertSame('name', $systemPrompt->getName());
    }

    #[Test]
    public function itCanChangeTheContent(): void
    {
        $systemPrompt = SystemPrompt::create(
            Purpose::CONVERSATION,
            'name',
            'content',
        );

        $systemPrompt->changeContent('new content');

        self::assertSame('new content', $systemPrompt->getContent());
    }

    #[Test]
    public function itCannotChangeTheContentIfSame(): void
    {
        $systemPrompt = SystemPrompt::create(
            Purpose::CONVERSATION,
            'name',
            'content',
        );

        $systemPrompt->changeContent('content');

        self::assertSame('content', $systemPrompt->getContent());
    }

    #[Test]
    public function itCanBeJsonSerialized(): void
    {
        $systemPrompt = SystemPrompt::create(
            Purpose::CONVERSATION,
            'name',
            'content',
        );

        $json = json_encode($systemPrompt, JSON_THROW_ON_ERROR);

        self::assertJson($json);
        self::assertSame(
            '{"id":"' . $systemPrompt->getId() . '","purpose":"conversation","name":"name","content":"content","isSystem":false,"isDefault":false}',
            $json,
        );
    }

    #[Test]
    public function itCanBeMadeToDefault(): void
    {
        $systemPrompt = SystemPrompt::create(Purpose::CONVERSATION, 'name', 'content');
        self::assertFalse($systemPrompt->isDefault());
        $systemPrompt->toDefault();
        self::assertTrue($systemPrompt->isDefault());
    }

    #[Test]
    public function itIgnoresMakingADefaultForAlreadyDefaultEntry(): void
    {
        $systemPrompt = SystemPrompt::create(Purpose::CONVERSATION, 'name', 'content');
        $systemPrompt->toDefault();
        self::assertTrue($systemPrompt->isDefault());
        $systemPrompt->toDefault();
        self::assertTrue($systemPrompt->isDefault());
    }

    #[Test]
    public function itCanNotMakeSystemPromptsToDefault(): void
    {
        $systemPrompt = SystemPrompt::createSystemPrompt('id', Purpose::CONVERSATION, 'name', 'content');
        self::assertTrue($systemPrompt->isSystem());
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('System relevant prompts cannot be set as default as they are already fallbacks when no default defined.');
        $systemPrompt->toDefault();
    }
}
