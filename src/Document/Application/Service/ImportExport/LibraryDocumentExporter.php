<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service\ImportExport;

use ChronicleKeeper\Document\Application\Query\FindAllDocuments;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportData;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use ChronicleKeeper\Settings\Application\Service\Exporter\Type;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class LibraryDocumentExporter implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var Document[] $documents */
        $documents = $this->queryService->query(new FindAllDocuments());

        foreach ($documents as $document) {
            $archive->addFromString(
                'library/documents/' . $document->getId() . '.json',
                $this->serializer->serialize(
                    ExportData::create(
                        $exportSettings,
                        Type::DIRECTORY,
                        $document->toArray(),
                    ),
                    'json',
                    ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
                ),
            );
        }
    }
}
