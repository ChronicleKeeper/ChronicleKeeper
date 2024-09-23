<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Application\Service\Migrator;

use DZunke\NovDoc\Settings\Application\Service\FileType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('application_migration')]
interface FileMigration
{
    public function isSupporting(FileType $type, string $fileVersion): bool;

    public function migrate(string $file): void;
}
