<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\SchemaManager;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Test\Shared\Infrastructure\LLMChain\LLMChainFactoryDouble;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

use function assert;

class WebTestCase extends SymfonyWebTestCase
{
    protected KernelBrowser $client;
    protected DatabasePlatform $databasePlatform;
    protected QueryService $queryService;
    protected MessageBusInterface $bus;
    protected SchemaManager $schemaManager;
    protected LLMChainFactoryDouble $llmChainFactory;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $databasePlatform = self::getContainer()->get(DatabasePlatform::class);
        assert($databasePlatform instanceof DatabasePlatform);

        $this->databasePlatform = $databasePlatform;

        $schemaManager = self::getContainer()->get(SchemaManager::class);
        assert($schemaManager instanceof SchemaManager);

        $this->schemaManager = $schemaManager;

        $queryService = self::getContainer()->get(QueryService::class);
        assert($queryService instanceof QueryService);

        $this->queryService = $queryService;

        $bus = self::getContainer()->get(MessageBusInterface::class);
        assert($bus instanceof MessageBusInterface);

        $this->bus = $bus;

        $llmChainFactory = $this->client->getContainer()->get(LLMChainFactory::class);
        assert($llmChainFactory instanceof LLMChainFactoryDouble);

        $this->llmChainFactory = $llmChainFactory;

        if (! self::willSetupSchema()) {
            return;
        }

        $this->schemaManager->createSchema();
    }

    protected static function willSetupSchema(): bool
    {
        return true;
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->schemaManager->dropSchema();

        unset($this->databasePlatform, $this->client, $this->schemaManager);

        parent::tearDown();
    }
}
