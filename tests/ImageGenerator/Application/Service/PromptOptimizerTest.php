<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Service;

use ChronicleKeeper\Chat\Application\Service\SingleChatMessageExecution;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\ImageGenerator\Application\Service\PromptOptimizer;
use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
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
        $systemPromptRegistry = $this->createMock(SystemPromptRegistry::class);
        $systemPromptRegistry->expects($this->once())
            ->method('getDefaultForPurpose')
            ->willReturn(SystemPrompt::createSystemPrompt('foo', Purpose::IMAGE_GENERATOR_OPTIMIZER, 'bar', 'baz'));

        $chatMessageExecution = $this->createMock(SingleChatMessageExecution::class);
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

        self::assertSame(
            'Success!',
            (new PromptOptimizer($chatMessageExecution, $systemPromptRegistry))->optimize('Foo Bar Baz'),
        );
    }
}
