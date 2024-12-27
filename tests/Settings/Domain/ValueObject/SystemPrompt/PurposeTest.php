<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Settings\Domain\ValueObject\SystemPrompt;

use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Purpose::class)]
#[Small]
final class PurposeTest extends TestCase
{
    #[Test]
    public function itGeneratesCorrectLabels(): void
    {
        self::assertSame('Gespräche', Purpose::CONVERSATION->getLabel());
        self::assertSame('Bilder-Upload', Purpose::IMAGE_UPLOAD->getLabel());
        self::assertSame('Dokumenten-Optimierung', Purpose::DOCUMENT_OPTIMIZER->getLabel());
        self::assertSame('Mathildes Atelier', Purpose::IMAGE_GENERATOR_OPTIMIZER->getLabel());
    }
}
