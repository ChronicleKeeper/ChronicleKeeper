<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Application\Service\OpenAIGenerator;

use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator\ResponseImage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseImage::class)]
#[Small]
class ResponseImageTest extends TestCase
{
    #[Test]
    public function objectIsCreatable(): void
    {
        $responseImage = new ResponseImage('foo', 'bar', 'baz');

        self::assertSame('foo', $responseImage->prompt);
        self::assertSame('bar', $responseImage->mimeType);
        self::assertSame('baz', $responseImage->encodedImage);
    }

    #[Test]
    public function imageDataUrlGenerationIsCorrect(): void
    {
        $responseImage = new ResponseImage('foo', 'bar', 'baz');

        self::assertSame('data:bar;base64,baz', $responseImage->getImageUrl());
    }
}
