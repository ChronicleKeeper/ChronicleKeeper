<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Service;

use ChronicleKeeper\Document\Application\Service\LLMContentOptimizer;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use PhpLlm\LlmChain\Chain\ChainInterface;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LLMContentOptimizer::class)]
#[Small]
final class LLMContentOptimizerTest extends TestCase
{
    #[Test]
    public function itOptimizesContent(): void
    {
        $systemPrompt = (new SystemPromptBuilder())->build();

        $chain = $this->createMock(ChainInterface::class);
        $chain
            ->expects($this->once())
            ->method('call')
            ->willReturn(new TextResponse('This is an optimized content.'));

        $llmChainFactory = $this->createMock(LLMChainFactory::class);
        $llmChainFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($chain);

        $optimizer = new LLMContentOptimizer($llmChainFactory);

        $optimizedContent = $optimizer->optimize($systemPrompt, 'This is a test content.');

        self::assertSame('This is an optimized content.', $optimizedContent);
    }
}
