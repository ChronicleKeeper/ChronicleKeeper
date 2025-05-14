<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Command;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\SchemaManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:db:init',
    description: 'Initialize an empty database, loading schema and initial data',
)]
final class InitializeDatabaseCommand extends Command
{
    public function __construct(
        private readonly SchemaManager $schemaManager,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force the initialization of the database',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Initializing Chronicle Keeper Database');

        try {
            $this->schemaManager->dropSchema();
            $this->schemaManager->createSchema();
        } catch (Throwable $e) {
            $io->writeln('');
            $io->error('An error occurred while creating the database: ' . $e->getMessage());

            return self::FAILURE;
        }

        $io->writeln('');
        $io->success('Database was created.');

        return self::SUCCESS;
    }
}
