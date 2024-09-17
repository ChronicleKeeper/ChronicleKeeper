<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Repository;

use DateTimeImmutable;
use DZunke\NovDoc\Domain\Library\Image\Image;

use function file_put_contents;
use function json_encode;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;

class FilesystemImageRepository
{
    public function __construct(
        private readonly string $libraryImageStoragePath,
    ) {
    }

    public function store(Image $image): void
    {
        // When stored it is updated! Maybe change later with a change detection ... but yeah .. it is changed for now
        $image->updatedAt = new DateTimeImmutable();

        $filename        = $image->id . '.json';
        $filepath        = $this->libraryImageStoragePath . DIRECTORY_SEPARATOR . $filename;
        $documentAsArray = $image->toArray();
        $documentAsJson  = json_encode($documentAsArray, JSON_PRETTY_PRINT);

        file_put_contents($filepath, $documentAsJson);
    }
}
