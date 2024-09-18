<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Application\Importer;

use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('application_exporer_single_import')]
interface SingleImport
{
    public function import(Filesystem $filesystem): ImportedFileBag;
}
