<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database;

use ChronicleKeeper\Shared\Infrastructure\Database\Schema\SchemaManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:db:init',
    description: 'Initialize an empty database, loading schema and initial data',
)]
final class InitializeDatabaseCommand extends Command
{
    public function __construct(
        private readonly string $databasePath,
        private readonly Filesystem $filesystem,
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

        $filename = $this->databasePath;
        if ($this->filesystem->exists($filename) && $input->getOption('force') === false) {
            $io->warning('Database already exists at "' . $filename . '". You could overwrite it with the -f option.');

            return self::FAILURE;
        }

        $this->filesystem->remove($filename);
        $this->schemaManager->createSchema();

        $io->success('Database was created and successfully stored at "' . $filename . '".');

        return self::SUCCESS;
    }
}