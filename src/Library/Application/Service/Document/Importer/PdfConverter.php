<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Document\Importer;

use ChronicleKeeper\Library\Application\Service\Document\Exception\PDFHasEmptyContent;
use Smalot\PdfParser\Config as PdfParserConfig;
use Smalot\PdfParser\Parser as PdfParser;
use Symfony\Component\Mime\MimeTypes;

final class PdfConverter implements FileConverter
{
    /** @inheritDoc */
    public function mimeTypes(): array
    {
        return (new MimeTypes())->getMimeTypes('pdf');
    }

    public function convert(string $filePath): string
    {
        $config = new PdfParserConfig();
        $config->setRetainImageContent(true); // Disable Images as we want to just interpret the text

        $text = (new PdfParser())->parseFile($filePath)->getText();
        if ($text === '') {
            throw PDFHasEmptyContent::forFile($filePath);
        }

        return $text;
    }
}
