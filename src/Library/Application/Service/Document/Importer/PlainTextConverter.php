<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Application\Service\Document\Importer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;

use function array_merge;

final class PlainTextConverter implements FileConverter
{
    public function __construct(
        private readonly Filesystem $filesystem,
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
