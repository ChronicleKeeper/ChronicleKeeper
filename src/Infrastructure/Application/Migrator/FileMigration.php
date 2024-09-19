<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Migrator;

use DZunke\NovDoc\Infrastructure\Application\FileType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('application_migration')]
interface FileMigration
{
    public function isSupporting(FileType $type, string $fileVersion): bool;

    public function migrate(string $file): void;
}
