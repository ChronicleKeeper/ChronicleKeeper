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
use Throwable;

use function file_exists;
use function is_string;
use function unlink;

use const STDOUT;

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

    protected function configure(): void
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

        $this->addOption(
            'stream',
            's',
            InputOption::VALUE_NONE,
            'Stream the ZIP file to STDOUT instead of saving to disk',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $isStream = (bool) $input->getOption('stream');

        try {
            if ($isStream) {
                return $this->handleStreamExport();
            }

            return $this->handleFileExport($input, $io);
        } catch (Throwable $e) {
            if (! $isStream) {
                $io->error('Export failed: ' . $e->getMessage());
            }

            return self::FAILURE;
        }
    }

    /**
     * Handles exporting to a file
     */
    private function handleFileExport(InputInterface $input, SymfonyStyle $io): int
    {
        $io->title('Exporting ZIP Archive');

        $filename = $input->getArgument('filename');
        if (! is_string($filename)) {
            $io->error('Filename must be provided when not using stream mode.');

            return self::FAILURE;
        }

        if (! $this->validateFilename($filename, (bool) $input->getOption('force'), $io)) {
            return self::FAILURE;
        }

        $zipName = $this->exporter->export($filename);
        $io->success('Exported ZIP Archive: ' . $zipName);

        return self::SUCCESS;
    }

    /**
     * Handles exporting to STDOUT stream
     */
    private function handleStreamExport(): int
    {
        $this->exporter->exportToStream(STDOUT);

        return self::SUCCESS;
    }

    /**
     * Validates if the file can be written to
     */
    private function validateFilename(string $filename, bool $force, SymfonyStyle $io): bool
    {
        if (file_exists($filename) && ! $force) {
            $io->error('File already exists, use --force to overwrite.');

            return false;
        }

        if (file_exists($filename)) {
            @unlink($filename);
        }

        return true;
    }
}
