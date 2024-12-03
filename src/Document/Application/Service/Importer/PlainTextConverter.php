<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\Importer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;

use function array_merge;

final readonly class PlainTextConverter implements FileConverter
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    /** @inheritDoc */
    public function mimeTypes(): array
    {
        $mimeTypes = new MimeTypes();

        return array_merge(
            $mimeTypes->getMimeTypes('txt'),
            $mimeTypes->getMimeTypes('md'),
        );
    }

    public function convert(string $filePath): string
    {
        return $this->filesystem->readFile($filePath);
    }
}
