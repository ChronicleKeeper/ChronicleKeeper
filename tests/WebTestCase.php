<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test;

use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;
use ChronicleKeeper\Shared\Infrastructure\Database\Schema\SchemaManager;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

use function assert;

class WebTestCase extends SymfonyWebTestCase
{
    protected KernelBrowser $client;
    protected DatabasePlatform $databasePlatform;
    protected SchemaManager $schemaManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $databasePlatform = self::getContainer()->get(DatabasePlatform::class);
        assert($databasePlatform instanceof DatabasePlatform);

        $this->databasePlatform = $databasePlatform;

        $schemaManager = self::getContainer()->get(SchemaManager::class);
        assert($schemaManager instanceof SchemaManager);

        $this->schemaManager = $schemaManager;
        $this->schemaManager->createSchema();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->schemaManager->dropSchema();

        unset($this->databasePlatform, $this->client, $this->schemaManager);

        parent::tearDown();
    }
}
