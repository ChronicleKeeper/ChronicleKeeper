<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Application\Service\Exporter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use ZipArchive;

#[AutoconfigureTag('application_exporer_single_export')]
interface SingleExport
{
    public function export(ZipArchive $archive): void;
}
