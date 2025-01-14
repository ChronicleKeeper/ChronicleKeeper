<?php

declare(strict_types=1);

namespace ChronicleKeeper\Image\Application\Service\ImportExport;

use ChronicleKeeper\Image\Application\Query\FindAllImages;
use ChronicleKeeper\Image\Domain\Entity\Image;
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

final readonly class VectorStorageImageExporter implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
        private DatabasePlatform $databasePlatform,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var Image[] $images */
        $images = $this->queryService->query(new FindAllImages());

        $query = <<<'SQL'
            SELECT
                image_id,
                content,
                vectorContentHash,
                vec_to_json(embedding) as embedding
            FROM
                images_vectors
            WHERE
                image_id = :id
        SQL;

        foreach ($images as $image) {
            $embeddingsOfImage = $this->databasePlatform->fetch($query, ['id' => $image->getId()]);

            if (count($embeddingsOfImage) === 0) {
                continue;
            }

            $archive->addFromString(
                'library/image_embeddings/' . $image->getId() . '.json',
                $this->serializer->serialize(
                    ExportData::create(
                        $exportSettings,
                        Type::IMAGE_EMBEDDING,
                        $embeddingsOfImage,
                    ),
                    'json',
                    ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
                ),
            );
        }
    }
}
