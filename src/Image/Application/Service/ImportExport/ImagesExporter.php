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
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class ImagesExporter implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var Image[] $images */
        $images = $this->queryService->query(new FindAllImages());

        foreach ($images as $image) {
            $archive->addFromString(
                'library/images/' . $image->getId() . '.json',
                $this->serializer->serialize(
                    ExportData::create(
                        $exportSettings,
                        Type::IMAGE,
                        $image->toArray(),
                    ),
                    'json',
                    ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
                ),
            );
        }
    }
}
