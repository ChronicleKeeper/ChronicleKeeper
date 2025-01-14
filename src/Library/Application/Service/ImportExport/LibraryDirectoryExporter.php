<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\ImportExport;

use ChronicleKeeper\Library\Application\Query\FindAllDirectories;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportData;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use ChronicleKeeper\Settings\Application\Service\Exporter\Type;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class LibraryDirectoryExporter implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var Directory[] $directories */
        $directories = $this->queryService->query(new FindAllDirectories());

        $directoriesAsArray = [];
        foreach ($directories as $directory) {
            $directoriesAsArray[] = $directory->toArray();
        }

        $archive->addFromString(
            'library/directories.json',
            $this->serializer->serialize(
                ExportData::create(
                    $exportSettings,
                    Type::DIRECTORY,
                    $directoriesAsArray,
                ),
                'json',
                ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
            ),
        );
    }
}
