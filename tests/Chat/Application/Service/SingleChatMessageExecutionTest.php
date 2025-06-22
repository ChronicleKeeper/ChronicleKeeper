<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Service;

use ChronicleKeeper\Chat\Application\Service\SingleChatMessageExecution;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Document\Infrastructure\LLMChain\DocumentSearch;
use ChronicleKeeper\Library\Infrastructure\LLMChain\Tool\ImageSearch;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Test\Chat\Domain\Entity\ConversationBuilder;
use PhpLlm\LlmChain\Chain\ChainInterface;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SingleChatMessageExecution::class)]
#[Small]
class SingleChatMessageExecutionTest extends TestCase
{
    #[Test]
    public function itWorksAsExpectedAtMinimalService(): void
    {
        $llmChain = $this->createMock(ChainInterface::class);
        $llmChain->expects($this->once())
            ->method('call')
            ->with(
                self::isInstanceOf(MessageBag::class),
                [
                    'model' => 'gpt-4o',
                    'temperature' => 1.0,
                ],
            )
            ->willReturn(new TextResponse('Hello World!'));

        $chainFactory = self::createStub(LLMChainFactory::class);
        $chainFactory->method('create')->willReturn($llmChain);

        $serarchDocuments = self::createStub(DocumentSearch::class);
        $searchImages     = self::createStub(ImageSearch::class);

        $runtimeCollector = self::createStub(RuntimeCollector::class);

        $chatMessageExecution = new SingleChatMessageExecution(
            $chainFactory,
            $serarchDocuments,
            $searchImages,
            $runtimeCollector,
        );

        $conversation = (new ConversationBuilder())->build();
        $chatMessageExecution->execute('Hello?', $conversation);

        $conversation = $conversation->getMessages()->getArrayCopy();
        self::assertCount(2, $conversation);

        $userMessage = $conversation[0];
        self::assertInstanceOf(UserMessage::class, $userMessage->message);
        $textContent = $userMessage->message->content[0];
        self::assertInstanceOf(Text::class, $textContent);
        self::assertSame('Hello?', $textContent->text);

        $assistantMessage = $conversation[1];
        self::assertInstanceOf(AssistantMessage::class, $assistantMessage->message);
        self::assertSame('Hello World!', $assistantMessage->message->content);
    }
}
