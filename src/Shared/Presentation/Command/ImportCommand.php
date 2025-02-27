<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Command;

use ChronicleKeeper\Settings\Application\Service\Importer;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use function fclose;
use function feof;
use function fopen;
use function fread;
use function fwrite;
use function is_string;
use function realpath;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const STDIN;

#[AsCommand(
    name: 'app:import',
    description: 'Imports a ZIP Archive directly on the CLI. It is the same as using the web interface.',
)]
final class ImportCommand extends Command
{
    public function __construct(
        private readonly Importer $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'archive',
            InputArgument::OPTIONAL,
            'The path to the ZIP archive to import',
        );

        $this->addOption(
            'stream',
            't',
            InputOption::VALUE_NONE,
            'Read the ZIP file from STDIN instead of a file',
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

        $isStream = (bool) $input->getOption('stream');
        $tempFile = null;

        try {
            if ($isStream) {
                $archiveFilename = $this->handleStreamInput($io);
                $tempFile        = $archiveFilename; // Store for cleanup
            } else {
                $archiveFilename = $this->handleFileInput($input, $io);
            }

            if ($archiveFilename === null) {
                return self::FAILURE;
            }

            $io->info('Importing application from: ' . $archiveFilename);

            $importSettings = $this->createImportSettings($input);
            $this->importer->import($archiveFilename, $importSettings);

            $io->success('ZIP Archive was imported successfully.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $io->error('Import failed: ' . $e->getMessage());

            return self::FAILURE;
        } finally {
            // Clean up temporary file if it exists
            if ($isStream && $tempFile !== null) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Handles input from STDIN stream
     */
    private function handleStreamInput(SymfonyStyle $io): string|null
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'import_');
        if ($tempFile === false) {
            $io->error('Failed to create temporary file.');

            return null;
        }

        $handle = fopen($tempFile, 'wb');
        if ($handle === false) {
            $io->error('Failed to open temporary file for writing.');
            @unlink($tempFile);

            return null;
        }

        try {
            while (! feof(STDIN)) {
                $data = fread(STDIN, 8192);
                if ($data === false) {
                    throw new RuntimeException('Failed to read from STDIN');
                }

                if (fwrite($handle, $data) === false) {
                    throw new RuntimeException('Failed to write to temporary file');
                }
            }

            return $tempFile;
        } catch (Throwable $e) {
            $io->error($e->getMessage());

            return null;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Handles input from file argument
     */
    private function handleFileInput(InputInterface $input, SymfonyStyle $io): string|null
    {
        $archivePath = $input->getArgument('archive');
        if (! is_string($archivePath)) {
            $io->error('Archive path must be provided when not using stream mode.');

            return null;
        }

        $archiveFilename = realpath($archivePath);
        if ($archiveFilename === false) {
            $io->error('The provided archive "' . $archivePath . '" file does not exist.');

            return null;
        }

        return $archiveFilename;
    }

    /**
     * Creates ImportSettings object from command input
     */
    private function createImportSettings(InputInterface $input): ImportSettings
    {
        return ImportSettings::fromArray([
            'overwrite_settings' => (bool) $input->getOption('overwrite_settings'),
            'overwrite_library' => (bool) $input->getOption('overwrite_library'),
            'prune_library' => (bool) $input->getOption('prune_library'),
            'remove_archive' => false,
        ]);
    }
}
