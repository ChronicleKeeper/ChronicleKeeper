<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\SettingsToolBox;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBoxInterface;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SettingsToolBox::class)]
#[Small]
class SettingsToolBoxTest extends TestCase
{
    #[Test]
    public function getMapWithoutTools(): void
    {
        $settingsHandler = self::createStub(SettingsHandler::class);
        $llmToolBox      = self::createStub(ToolBoxInterface::class);
        $settingsToolBox = new SettingsToolBox($settingsHandler, $llmToolBox);

        self::assertEmpty($settingsToolBox->getMap());
    }

    #[Test]
    public function getMapWithTools(): void
    {
        $exampleTool = new Metadata(
            'ExampleTool',
            'ExampleTool',
            'This is an example tool.',
            '__invoke',
            null,
        );

        $settingsHandler = self::createStub(SettingsHandler::class);
        $llmToolBox      = self::createStub(ToolBoxInterface::class);
        $llmToolBox->method('getMap')->willReturn([$exampleTool]);
        $settingsToolBox = new SettingsToolBox($settingsHandler, $llmToolBox);

        self::assertCount(1, $settingsToolBox->getMap());
    }

    #[Test]
    public function getMapWithToolsAndDescriptions(): void
    {
        $exampleTool = new Metadata(
            'ExampleTool',
            'ExampleTool',
            'This is an example tool.',
            '__invoke',
            null,
        );

        $settings = (new SettingsBuilder())
            ->withChatbotFunctions((new ChatbotFunctions(
                false,
                ['ExampleTool' => 'foo bar baz'],
            )))
            ->build();

        $settingsHandler = self::createStub(SettingsHandler::class);
        $settingsHandler->method('get')->willReturn($settings);

        $llmToolBox = self::createStub(ToolBoxInterface::class);
        $llmToolBox->method('getMap')->willReturn([$exampleTool]);
        $settingsToolBox = new SettingsToolBox($settingsHandler, $llmToolBox);

        $metadata = $settingsToolBox->getMap()[0];
        self::assertSame('foo bar baz', $metadata->description);
    }

    #[Test]
    public function execute(): void
    {
        $toolCall = new ToolCall('ExampleTool', '__invoke', ['foo' => 'foo bar baz']);

        $llmToolBox = $this->createMock(ToolBoxInterface::class);
        $llmToolBox->expects($this->once())->method('execute')->with($toolCall)->willReturn('foo bar baz');

        $settingsToolBox = new SettingsToolBox(self::createStub(SettingsHandler::class), $llmToolBox);

        self::assertSame('foo bar baz', $settingsToolBox->execute($toolCall));
    }
}
