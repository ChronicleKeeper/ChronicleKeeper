<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Command;

use ChronicleKeeper\Settings\Application\Service\Importer;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function realpath;

#[AsCommand(
    name: 'app:import',
    description: 'Imports a ZIP Archive directly on the CLI. It is the same as using the web interface.',
)]
final class ExecuteImportCommand extends Command
{
    public function __construct(
        private readonly Importer $importer,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument(
            'archive',
            InputArgument::REQUIRED,
            'The path to the ZIP archive to import',
        );

        $this->addOption(
            'overwrite_settings',
            's',
            InputOption::VALUE_NONE,
            'Force the overwriting of existing settings to remove current settings.',
        );

        $this->addOption(
            'overwrite_library',
            'l',
            InputOption::VALUE_NONE,
            'Force the overwriting of existing library to remove current library.',
        );

        $this->addOption(
            'prune_library',
            'p',
            InputOption::VALUE_NONE,
            'Force the pruning of the library to remove all files.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Importing ZIP Archive');

        $archiveFilename = realpath($input->getArgument('archive'));
        if ($archiveFilename === false) {
            $io->error('The provided archive "' . $input->getArgument('archive') . '" file does not exist.');

            return self::FAILURE;
        }

        $io->info('Importing application from: ' . $archiveFilename);

        $importSettings = ImportSettings::fromArray([
            'overwrite_settings' => $input->getOption('overwrite_settings'),
            'overwrite_library' => $input->getOption('overwrite_library'),
            'prune_library' => $input->getOption('prune_library'),
            'remove_archive' => false,
        ]);

        $this->importer->import($archiveFilename, $importSettings);

        $io->success('ZIP Archive was imported successfully.');

        return self::SUCCESS;
    }
}
