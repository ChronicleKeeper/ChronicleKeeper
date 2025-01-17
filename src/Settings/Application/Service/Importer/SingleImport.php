<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('application_exporer_single_import')]
interface SingleImport
{
    public function import(Filesystem $filesystem, ImportSettings $settings): void;
}
