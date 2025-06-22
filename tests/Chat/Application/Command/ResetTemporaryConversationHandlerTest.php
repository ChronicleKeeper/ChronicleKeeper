<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Command;

use ChronicleKeeper\Chat\Application\Command\ResetTemporaryConversation;
use ChronicleKeeper\Chat\Application\Command\ResetTemporaryConversationHandler;
use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(ResetTemporaryConversationHandler::class)]
#[CoversClass(ResetTemporaryConversation::class)]
#[Small]
class ResetTemporaryConversationHandlerTest extends TestCase
{
    #[Test]
    public function itCanConstructTheCommand(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->build();
        $command      = new ResetTemporaryConversation('foo', $systemPrompt);

        self::assertSame('foo', $command->title);
        self::assertSame($systemPrompt, $command->utilizePrompt);
    }

    #[Test]
    public function itWillResetAndCreateANewConversation(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->build();
        $settings     = (new SettingsBuilder())->build();

        $settingsHandler = $this->createMock(SettingsHandler::class);
        $settingsHandler->expects($this->once())->method('get')->willReturn($settings);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())->method('dispatch')->willReturnCallback(
            static function (StoreTemporaryConversation $message) use ($systemPrompt): Envelope {
                $conversation = $message->conversation;

                self::assertSame('foo', $conversation->getTitle());

                $messages = $conversation->getMessages();
                self::assertCount(1, $conversation->getMessages());

                $message = $messages[0];
                self::assertInstanceOf(ExtendedMessage::class, $message);
                self::assertInstanceOf(SystemMessage::class, $message->message);

                self::assertSame($systemPrompt->getContent(), $message->message->content);

                return new Envelope($message);
            },
        );

        $command = new ResetTemporaryConversation('foo', $systemPrompt);
        $handler = new ResetTemporaryConversationHandler($settingsHandler, $bus);
        $handler($command);
    }
}
