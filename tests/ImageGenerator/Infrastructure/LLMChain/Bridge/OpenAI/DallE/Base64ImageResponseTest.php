<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;

use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE\Base64ImageResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(Base64ImageResponse::class)]
#[Small]
class Base64ImageResponseTest extends TestCase
{
    #[Test]
    public function instantiationIsPossible(): void
    {
        $base64ImageResponse = new Base64ImageResponse('revised-prompt', 'image');
        self::assertSame('image', $base64ImageResponse->getContent());
    }

    #[Test]
    public function instantiationIsNotPossibleWithEmptyRevisedPrompt(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The revised prompt by endpoint must not be empty.');

        new Base64ImageResponse('', 'image');
    }

    #[Test]
    public function instantiationIsNotPossibleWithEmptyImage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The image generated must be given.');

        new Base64ImageResponse('revised-prompt', '');
    }
}
