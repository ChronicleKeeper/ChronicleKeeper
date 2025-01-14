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
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use function count;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class VectorStorageDocumentsExporter implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
        private DatabasePlatform $databasePlatform,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var Document[] $documents */
        $documents = $this->queryService->query(new FindAllDocuments());

        $query = <<<'SQL'
            SELECT
                document_id,
                content,
                vectorContentHash,
                vec_to_json(embedding) as embedding
            FROM
                documents_vectors
            WHERE
                document_id = :id
        SQL;

        foreach ($documents as $document) {
            $embeddingsOfDocument = $this->databasePlatform->fetch($query, ['id' => $document->getId()]);

            if (count($embeddingsOfDocument) === 0) {
                continue;
            }

            $archive->addFromString(
                'library/document_embeddings/' . $document->getId() . '.json',
                $this->serializer->serialize(
                    ExportData::create(
                        $exportSettings,
                        Type::DOCUMENT_EMBEDDING,
                        $embeddingsOfDocument,
                    ),
                    'json',
                    ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
                ),
            );
        }
    }
}
