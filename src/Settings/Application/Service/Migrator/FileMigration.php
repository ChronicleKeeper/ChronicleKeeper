<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Migrator;

use ChronicleKeeper\Settings\Application\Service\FileType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('application_migration')]
interface FileMigration
{
    public function isSupporting(FileType $type, string $fileVersion): bool;

    public function migrate(string $file): void;
}
