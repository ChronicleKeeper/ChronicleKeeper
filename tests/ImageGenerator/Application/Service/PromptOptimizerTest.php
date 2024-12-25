<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Service;

use ChronicleKeeper\Chat\Application\Service\ChatMessageExecution;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use PhpLlm\LlmChain\Model\Message\Message;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PromptOptimizer::class)]
#[Small]
class PromptOptimizerTest extends TestCase
{
    #[Test]
    public function optimizeAnInputPrompt(): void
    {
        $chatMessageExecution = $this->createMock(ChatMessageExecution::class);
        $chatMessageExecution
            ->expects($this->once())
            ->method('execute')
            ->with(
                self::callback(static function (string $originPrompt): bool {
                    self::assertSame('Foo Bar Baz', $originPrompt);

                    return true;
                }),
                self::callback(static function (Conversation $conversation): bool {
                    $messages = $conversation->getMessages();

                    self::assertCount(1, $messages);
                    $messages[] = new ExtendedMessage(Message::ofAssistant('Success!'));

                    return true;
                }),
            );

        self::assertSame('Success!', (new PromptOptimizer($chatMessageExecution))->optimize('Foo Bar Baz'));
    }
}
