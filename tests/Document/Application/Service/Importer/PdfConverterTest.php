<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Service\Importer;

use ChronicleKeeper\Document\Application\Service\Exception\PDFHasEmptyContent;
use ChronicleKeeper\Document\Application\Service\Importer\PdfConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PdfConverter::class)]
#[Small]
class PdfConverterTest extends TestCase
{
    #[Test]
    public function itGivesCorrectMimeTypes(): void
    {
        $mimeTypes = (new PdfConverter())->mimeTypes();

        self::assertSame(
            [
                'application/pdf',
                'application/acrobat',
                'application/nappdf',
                'application/x-pdf',
                'image/pdf',
            ],
            $mimeTypes,
        );
    }

    #[Test]
    public function itConvertsPdfToText(): void
    {
        $pdfConverter = new PdfConverter();

        $text = $pdfConverter->convert(__DIR__ . '/Stubs/hello_world.pdf');

        self::assertStringContainsString('Hallo Welt', $text);
    }

    #[Test]
    public function itThrowsExceptionWhenPdfIsEmpty(): void
    {
        $this->expectException(PDFHasEmptyContent::class);

        $pdfConverter = new PdfConverter();
        $pdfConverter->convert(__DIR__ . '/Stubs/blank.pdf');
    }
}
