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
use Psr\Log\LoggerInterface;
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
        private LoggerInterface $logger,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var Image[] $images */
        $images = $this->queryService->query(new FindAllImages());
        if (count($images) === 0) {
            $this->logger->debug('No images found, skipping export of vector storage.');

            return;
        }

        foreach ($images as $image) {
            $embeddingsOfImage = $this->databasePlatform->createQueryBuilder()
                ->createSelect()
                ->select('image_id', 'content', 'vectorContentHash')
                ->vectorToJson('embedding', 'embedding')
                ->from('images_vectors')
                ->where('image_id', '=', $image->getId())
                ->fetchAll();

            if (count($embeddingsOfImage) === 0) {
                $this->logger->debug(
                    'No embeddings found for image, skipping export.',
                    ['id' => $image->getId()],
                );

                continue;
            }

            $this->logger->debug('Exporting image embeddings.', ['id' => $image->getId()]);
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
