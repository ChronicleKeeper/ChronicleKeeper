<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Service\ImportExport;

use ChronicleKeeper\Settings\Application\Service\Exporter\ExportData;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use ChronicleKeeper\Settings\Application\Service\Exporter\Type;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\FindRelationsOfItem;
use ChronicleKeeper\World\Application\Query\GetWorldItem;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\Relation;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use function array_map;
use function assert;
use function count;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class WorldExport implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var Item[] $items */
        $items = $this->queryService->query(new SearchWorldItems(limit: -1));
        if (count($items) === 0) {
            $this->logger->debug('No items found, skipping export.');

            return;
        }

        foreach ($items as $item) {
            $this->logger->debug('Exporting item.', ['id' => $item->getId()]);

            $item = $this->queryService->query(new GetWorldItem($item->getId()));
            assert($item instanceof Item);

            $data = $item->jsonSerialize();

            // Add Relations
            $data['relations'] = array_map(
                static fn (Relation $relation): array => [
                    'toItem' => $relation->toItem->getId(),
                    'relationType' => $relation->relationType,
                ],
                $this->queryService->query(new FindRelationsOfItem($item->getId())),
            );

            $archive->addFromString(
                'world/' . $item->getId() . '.json',
                $this->serializer->serialize(
                    ExportData::create(
                        $exportSettings,
                        Type::WORLD_ITEM,
                        $data,
                    ),
                    'json',
                    ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
                ),
            );
        }
    }
}
