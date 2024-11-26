<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;

use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class Base64ImageResponse implements ResponseInterface
{
    public function __construct(
        public string $revisedPrompt,
        public string $image,
    ) {
        Assert::stringNotEmpty($revisedPrompt);
    }

    public function getContent(): string|iterable|object|null
    {
        return $this->image;
    }
}
