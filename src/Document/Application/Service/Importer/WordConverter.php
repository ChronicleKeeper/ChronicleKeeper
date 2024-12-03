<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\Importer;

use League\HTMLToMarkdown\HtmlConverter;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Writer\HTML;
use Symfony\Component\Mime\MimeTypes;

use function assert;

final class WordConverter implements FileConverter
{
    /** @inheritDoc */
    public function mimeTypes(): array
    {
        return (new MimeTypes())->getMimeTypes('docx');
    }

    public function convert(string $filePath): string
    {
        $reader = IOFactory::createReader('Word2007');
        $word   = $reader->load($filePath);
        assert($word instanceof PhpWord);

        $htmlWriter = IOFactory::createWriter($word, 'HTML');
        assert($htmlWriter instanceof HTML);

        $htmlToMarkdown = new HtmlConverter([
            'strip_tags' => true,
            'header_style' => 'atx',
            'strip_placeholder_links' => true,
            'remove_nodes' => 'meta style script title img',
            'hard_break' => true,
            'use_autolinks' => false,

        ]);

        return $htmlToMarkdown->convert($htmlWriter->getContent());
    }
}
