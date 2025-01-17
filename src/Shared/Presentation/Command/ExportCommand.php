<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Command;

use ChronicleKeeper\Settings\Application\Service\Exporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function file_exists;
use function unlink;

#[AsCommand(
    name: 'app:export',
    description: 'Exports a ZIP Archive with all needed data from the application. Can be utilized to reinstantiate a new instance of the application.',
)]
final class ExportCommand extends Command
{
    public function __construct(
        private readonly Exporter $exporter,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument(
            'filename',
            InputArgument::OPTIONAL,
            'The filename of the ZIP Archive',
        );

        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force the export, an existing file will be deleted.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Exporting ZIP Archive');

        $filename = $input->getArgument('filename');
        if (file_exists($filename) && $input->getOption('force') === false) {
            $io->error('File already exists, use --force to overwrite.');

            return self::FAILURE;
        }

        @unlink($filename);
        $zipName = $this->exporter->export($filename);

        $io->success('Exported ZIP Archive: ' . $zipName);

        return self::SUCCESS;
    }
}
