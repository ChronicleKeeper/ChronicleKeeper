<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Application\Service\Document\Importer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('document_file_converter')]
interface FileConverter
{
    /** @return list<string> */
    public function mimeTypes(): array;

    public function convert(string $filePath): string;
}
