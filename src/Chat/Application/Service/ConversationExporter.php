<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service;

use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use Symfony\Component\Finder\Finder;
use ZipArchive;

final readonly class ConversationExporter implements SingleExport
{
    public function __construct(
        private string $conversationStoragePath,
    ) {
    }

    public function export(ZipArchive $archive): void
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->conversationStoragePath)
            ->files();

        foreach ($finder as $file) {
            $archive->addFile($file->getRealPath(), 'library/conversations/' . $file->getFilename());
        }
    }
}
