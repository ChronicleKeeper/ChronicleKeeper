<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Exporter;

use ChronicleKeeper\Settings\Application\Query\FindUserPrompts;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ZipArchive;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final readonly class SystemPromptsExporter implements SingleExport
{
    public function __construct(
        private QueryService $queryService,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
    ) {
    }

    public function export(ZipArchive $archive, ExportSettings $exportSettings): void
    {
        /** @var SystemPrompt[] $userPrompts */
        $userPrompts = $this->queryService->query(new FindUserPrompts());

        $archive->addFromString(
            'system_prompts.json',
            $this->serializer->serialize(
                ExportData::create(
                    $exportSettings,
                    Type::PROMPTS,
                    $userPrompts,
                ),
                'json',
                ['json_encode_options' => JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR],
            ),
        );

        $this->logger->debug('System prompts exported to "system_prompts.json" in archive.');
    }
}
