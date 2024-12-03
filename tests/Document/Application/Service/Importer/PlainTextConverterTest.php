<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Service\Importer;

use ChronicleKeeper\Document\Application\Service\Importer\PlainTextConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(PlainTextConverter::class)]
#[Small]
class PlainTextConverterTest extends TestCase
{
    #[Test]
    public function itGivesCorrectMimeTypes(): void
    {
        $mimeTypes = (new PlainTextConverter(self::createStub(Filesystem::class)))
            ->mimeTypes();

        self::assertSame(
            [
                'text/plain',
                'text/markdown',
                'text/x-markdown',
                'application/x-genesis-rom',
            ],
            $mimeTypes,
        );
    }

    #[Test]
    public function itConvertsPlainTextToText(): void
    {
        $fileSystem = self::createStub(Filesystem::class);
        $fileSystem->method('readFile')
            ->willReturn('Hello World');

        $plainTextConverter = new PlainTextConverter($fileSystem);

        $text = $plainTextConverter->convert(__DIR__ . '/Stubs/hello_world.txt');

        self::assertSame('Hello World', $text);
    }
}
