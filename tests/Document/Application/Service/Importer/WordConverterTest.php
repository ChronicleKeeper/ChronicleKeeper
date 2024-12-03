<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Service\Importer;

use ChronicleKeeper\Document\Application\Service\Importer\WordConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordConverter::class)]
#[Small]
class WordConverterTest extends TestCase
{
    #[Test]
    public function itGivesCorrectMimeTypes(): void
    {
        $mimeTypes = (new WordConverter())->mimeTypes();

        self::assertSame(
            ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            $mimeTypes,
        );
    }

    #[Test]
    public function itConvertsWordToText(): void
    {
        $text = (new WordConverter())->convert(__DIR__ . '/Stubs/hello_world.docx');

        self::assertSame('Hello World', $text);
    }
}
