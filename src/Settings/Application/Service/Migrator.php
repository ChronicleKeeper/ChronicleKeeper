<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Settings\Application\Service;

use DZunke\NovDoc\Settings\Application\Service\Migrator\FileMigration;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

use function version_compare;

class Migrator
{
    /** @param iterable<FileMigration> $migrations */
    public function __construct(
        #[AutowireIterator('application_migration')]
        private readonly iterable $migrations,
        private readonly Version $version,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function migrate(string $path, FileType $type, string $version): void
    {
        $currentApplicationVersion = $this->version->getCurrentNumericVersion();
        if (version_compare($version, $currentApplicationVersion, '>')) {
            $this->logger->debug(
                'Migration of "' . $path . '" is ignored, sourced from newer version',
                ['file_version' => $version, 'application_version' => $currentApplicationVersion],
            );

            return;
        }

        foreach ($this->migrations as $migration) {
            if (! $migration->isSupporting($type, $version)) {
                continue;
            }

            $migration->migrate($path);
            $this->logger->debug('Migration for "' . $path . '" done.', ['migration' => $migration::class]);
        }
    }
}
