<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Service\Importer;

use ChronicleKeeper\Document\Application\Service\Exception\PDFHasEmptyContent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PDFHasEmptyContent::class)]
#[Small]
class PDFHasEmptyContentTest extends TestCase
{
    #[Test]
    public function itCreatesExceptionForFile(): void
    {
        $exception = PDFHasEmptyContent::forFile('test.pdf');

        self::assertSame(
            'The PDF "test.pdf" does not contain any readable text. Please try a different PDF or reformat the PDF to another format.',
            $exception->getMessage(),
        );
    }
}
