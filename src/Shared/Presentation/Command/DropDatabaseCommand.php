<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\SchemaManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

#[AsCommand(
    name: 'app:db:drop',
    description: 'Completly drop the database, removing all data and schema',
)]
final class DropDatabaseCommand extends Command
{
    public function __construct(
        private readonly SchemaManager $schemaManager,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Have to be used to drop of the database, just to be sure you know what you are doing',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Dropping Chronicle Keeper Database');

        if ($input->getOption('force') === false) {
            $io->warning('Are you sure? You have to declare it with the -f option.');

            return self::FAILURE;
        }

        $this->logger->debug('Removing existing database.');
        try {
            $this->schemaManager->dropSchema();
        } catch (Throwable $e) {
            $io->writeln('');
            $io->error('An error occurred while dropping the database: ' . $e->getMessage());

            return self::FAILURE;
        }

        $io->writeln('');
        $io->success('Database was dropped and is empty.');

        return self::SUCCESS;
    }
}
