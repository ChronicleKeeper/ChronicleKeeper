<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI;

use ChronicleKeeper\ImageGenerator\Infrastructure\LLMChain\Bridge\OpenAI\DallE;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DallE::class)]
#[Small]
class DallETest extends TestCase
{
    #[Test]
    public function instantiationIsPossible(): void
    {
        $dallE = new DallE();
        self::assertSame(DallE::DALL_E_3, $dallE->getVersion());
        self::assertSame(['response_format' => 'b64_json'], $dallE->getOptions());
        self::assertFalse($dallE->supportsImageInput());
        self::assertFalse($dallE->supportsStructuredOutput());
        self::assertFalse($dallE->supportsStreaming());
        self::assertFalse($dallE->supportsToolCalling());
    }

    #[Test]
    public function instantiationIsPossibleWithCustomVersion(): void
    {
        $dallE = new DallE('custom-version');
        self::assertSame('custom-version', $dallE->getVersion());
    }

    #[Test]
    public function instantiationIsPossibleWithCustomOptions(): void
    {
        $dallE = new DallE(DallE::DALL_E_3, ['custom-option' => 'value']);
        self::assertSame(['custom-option' => 'value'], $dallE->getOptions());
    }
}
