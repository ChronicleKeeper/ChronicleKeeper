<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service\ImportExport;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportData;
use ChronicleKeeper\Settings\Application\Service\Exporter\ExportSettings;
use ChronicleKeeper\Settings\Application\Service\Exporter\SingleExport;
use ChronicleKeeper\Settings\Application\Service\Exporter\Type;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use function array_column;
use function assert;
use function count;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class ConversationExporter implements SingleExport
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
        $conversationIdentifier = $this->databasePlatform->fetch('SELECT id FROM conversations');
        if (count($conversationIdentifier) === 0) {
            $this->logger->debug('No conversations found, skipping export.');

            return;
        }

        foreach (array_column($conversationIdentifier, 'id') as $identifier) {
            $conversation = $this->queryService->query(new FindConversationByIdParameters($identifier));
            assert($conversation instanceof Conversation);

            $this->logger->debug('Exporting conversation.', ['id' => $conversation->getId()]);

            $archive->addFromString(
                'library/conversations/' . $conversation->getId() . '.json',
                $this->serializer->serialize(
                    ExportData::create(
                        $exportSettings,
                        Type::CONVERSATION,
                        $conversation->jsonSerialize(),
                    ),
                    'json',
                    ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
                ),
            );
        }
    }
}
