<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings\ChatbotFunctions;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\SettingsToolbox;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PhpLlm\LlmChain\Chain\Toolbox\ExecutionReference;
use PhpLlm\LlmChain\Chain\Toolbox\Metadata;
use PhpLlm\LlmChain\Chain\Toolbox\ToolboxInterface;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(SettingsToolbox::class)]
#[Small]
class SettingsToolboxTest extends TestCase
{
    #[Test]
    public function getMapWithoutTools(): void
    {
        $settingsHandler = self::createStub(SettingsHandler::class);
        $llmToolbox      = self::createStub(ToolboxInterface::class);
        $settingsToolbox = new SettingsToolbox($settingsHandler, $llmToolbox);

        self::assertEmpty($settingsToolbox->getMap());
    }

    #[Test]
    public function getMapWithTools(): void
    {
        $exampleTool = new Metadata(
            new ExecutionReference(stdClass::class),
            'ExampleTool',
            'This is an example tool.',
            null,
        );

        $settingsHandler = self::createStub(SettingsHandler::class);
        $llmToolbox      = self::createStub(ToolboxInterface::class);
        $llmToolbox->method('getMap')->willReturn([$exampleTool]);
        $settingsToolbox = new SettingsToolbox($settingsHandler, $llmToolbox);

        self::assertCount(1, $settingsToolbox->getMap());
    }

    #[Test]
    public function getMapWithToolsAndDescriptions(): void
    {
        $exampleTool = new Metadata(
            new ExecutionReference(stdClass::class),
            'ExampleTool',
            'This is an example tool.',
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

        $llmToolbox = self::createStub(ToolboxInterface::class);
        $llmToolbox->method('getMap')->willReturn([$exampleTool]);
        $settingsToolbox = new SettingsToolbox($settingsHandler, $llmToolbox);

        $metadata = $settingsToolbox->getMap()[0];
        self::assertSame('foo bar baz', $metadata->description);
    }

    #[Test]
    public function execute(): void
    {
        $toolCall = new ToolCall('ExampleTool', '__invoke', ['foo' => 'foo bar baz']);

        $llmToolbox = $this->createMock(ToolboxInterface::class);
        $llmToolbox->expects($this->once())->method('execute')->with($toolCall)->willReturn('foo bar baz');

        $settingsToolbox = new SettingsToolbox(self::createStub(SettingsHandler::class), $llmToolbox);

        self::assertSame('foo bar baz', $settingsToolbox->execute($toolCall));
    }
}
