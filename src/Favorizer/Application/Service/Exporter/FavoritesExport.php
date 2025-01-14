<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Application\Service\Exporter;

use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportData;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use ChronicleKeeper\Settings\Application\Service\Exporter\Type;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use function assert;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class FavoritesExport implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        $favoritesBag = $this->queryService->query(new GetTargetBag());
        assert($favoritesBag instanceof TargetBag);

        $archive->addFromString(
            'favorites.json',
            $this->serializer->serialize(
                ExportData::create(
                    $exportSettings,
                    Type::FAVORITES,
                    $favoritesBag->jsonSerialize(),
                ),
                'json',
                ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
            ),
        );

        $this->logger->debug('Favorites exported to "favorites.json" in archive.');
    }
}
