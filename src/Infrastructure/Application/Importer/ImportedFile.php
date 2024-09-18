<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Importer;

use DZunke\NovDoc\Infrastructure\Application\FileType;

class ImportedFile
{
    public function __construct(
        public readonly string $file,
        public readonly FileType $type,
        public readonly State $state,
        public readonly string $comment = '',
    ) {
    }

    public static function asSuccess(string $file, FileType $type, string $comment = ''): ImportedFile
    {
        return new self($file, $type, State::SUCCESS, $comment);
    }

    public static function asIgnored(string $file, FileType $type, string $comment = ''): ImportedFile
    {
        return new self($file, $type, State::IGNORED, $comment);
    }

    public static function asError(string $file, FileType $type, string $comment = ''): ImportedFile
    {
        return new self($file, $type, State::ERROR, $comment);
    }
}
