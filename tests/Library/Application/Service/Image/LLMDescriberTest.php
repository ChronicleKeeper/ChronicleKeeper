<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Library\Application\Service\Image;

use ChronicleKeeper\Library\Application\Service\Image\LLMDescriber;
use ChronicleKeeper\Settings\Application\Service\SystemPromptRegistry;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Test\Image\Domain\Entity\ImageBuilder;
use ChronicleKeeper\Test\Settings\Domain\Entity\SystemPromptBuilder;
use PhpLlm\LlmChain\ChainInterface;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(LLMDescriber::class)]
#[Small]
final class LLMDescriberTest extends TestCase
{
    #[Test]
    public function itDescribesAnImage(): void
    {
        $systemPromptRegistry = $this->createMock(SystemPromptRegistry::class);
        $systemPromptRegistry
            ->expects($this->once())
            ->method('getDefaultForPurpose')
            ->with(Purpose::IMAGE_UPLOAD)
            ->willReturn((new SystemPromptBuilder())->build());

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

        $describer = new LLMDescriber($llmChainFactory, $systemPromptRegistry);
        $describer->getDescription((new ImageBuilder())->build());
    }

    #[Test]
    public function itThrowsAnExceptionWhenResponseIsNotAText(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Image analyzing is expected to return string, given is an object.');

        $systemPromptRegistry = $this->createMock(SystemPromptRegistry::class);
        $systemPromptRegistry
            ->expects($this->once())
            ->method('getDefaultForPurpose')
            ->with(Purpose::IMAGE_UPLOAD)
            ->willReturn((new SystemPromptBuilder())->build());

        $chain = $this->createMock(ChainInterface::class);
        $chain
            ->expects($this->once())
            ->method('call')
            ->willReturn(new VectorResponse(new Vector([0.1])));

        $llmChainFactory = $this->createMock(LLMChainFactory::class);
        $llmChainFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($chain);

        $describer = new LLMDescriber($llmChainFactory, $systemPromptRegistry);
        $describer->getDescription((new ImageBuilder())->build());
    }
}
