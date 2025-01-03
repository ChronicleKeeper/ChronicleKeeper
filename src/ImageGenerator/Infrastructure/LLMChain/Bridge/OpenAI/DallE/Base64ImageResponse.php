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
        Assert::stringNotEmpty($revisedPrompt, 'The revised prompt by endpoint must not be empty.');
        Assert::stringNotEmpty($image, 'The image generated must be given.');
    }

    public function getContent(): string
    {
        return $this->image;
    }
}
