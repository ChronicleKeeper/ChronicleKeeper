<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Domain\Entity;

use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\UserInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(GeneratorRequest::class)]
#[Small]
class GeneratorRequestTest extends TestCase
{
    #[Test]
    public function objectIsCreatable(): void
    {
        $userInput        = new UserInput('bar');
        $generatorRequest = new GeneratorRequest('foo', $userInput);

        self::assertTrue(Uuid::isValid($generatorRequest->id));
        self::assertSame('foo', $generatorRequest->title);
        self::assertSame($userInput, $generatorRequest->userInput);
        self::assertNull($generatorRequest->prompt);
    }

    #[Test]
    public function optimizedPromptIsEditable(): void
    {
        $generatorRequest         = new GeneratorRequest('foo', new UserInput('bar'));
        $generatorRequest->prompt = new OptimizedPrompt('baz');

        self::assertSame('baz', $generatorRequest->prompt->prompt);
    }

    #[Test]
    public function jsonSerializationIsCorrect(): void
    {
        $generatorRequest = new GeneratorRequest('foo', new UserInput('bar'));

        self::assertSame([
            'id' => $generatorRequest->id,
            'prompt' => null,
            'title' => 'foo',
            'userInput' => ['prompt' => 'bar', 'systemPrompt' => null],
        ], $generatorRequest->jsonSerialize());
    }
}
