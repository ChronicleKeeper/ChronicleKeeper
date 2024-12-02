<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Document\Importer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('document_file_converter')]
interface FileConverter
{
    /** @return array<string> */
    public function mimeTypes(): array;

    public function convert(string $filePath): string;
}
