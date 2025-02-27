<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Presentation\Command;

use ChronicleKeeper\Settings\Application\Service\Importer;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Presentation\Command\ImportCommand;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

use function file_put_contents;
use function strtolower;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

#[CoversClass(ImportCommand::class)]
#[Small]
final class ImportCommandTest extends TestCase
{
    private MockObject&Importer $importer;
    private CommandTester $commandTester;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->importer = $this->createMock(Importer::class);
        $command        = new ImportCommand($this->importer);

        $application = new Application();
        $application->add($command);
        $command = $application->find('app:import');

        $this->commandTester = new CommandTester($command);
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->importer, $this->commandTester);
    }

    #[Test]
    public function itShouldImportArchiveFromFile(): void
    {
        // Create a test archive file
        $archiveFile = tempnam(sys_get_temp_dir(), 'test_archive_');
        file_put_contents($archiveFile, 'test archive content');

        // Set up expectations
        $this->importer->expects($this->once())
            ->method('import')
            ->with(
                $archiveFile,
                self::callback(static fn (ImportSettings $settings) => $settings->overwriteSettings === false
                    && $settings->overwriteLibrary === false
                    && $settings->pruneLibrary === false
                    && $settings->removeArchive === false),
            );

        // Execute the command
        $this->commandTester->execute(['archive' => $archiveFile]);

        // Assert command output
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Importing ZIP Archive', $output);
        self::assertStringContainsString('Importing application from:', $output);
        self::assertStringContainsString('ZIP Archive was imported successfully.', $output);

        // Assert command status code
        self::assertSame(0, $this->commandTester->getStatusCode());

        // Clean up
        @unlink($archiveFile);
    }

    #[Test]
    public function itShouldImportArchiveWithAllOptionsEnabled(): void
    {
        // Create a test archive file
        $archiveFile = tempnam(sys_get_temp_dir(), 'test_archive_');
        file_put_contents($archiveFile, 'test archive content');

        // Set up expectations
        $this->importer->expects($this->once())
            ->method('import')
            ->with(
                $archiveFile,
                self::callback(static fn (ImportSettings $settings) => $settings->overwriteSettings === true
                    && $settings->overwriteLibrary === true
                    && $settings->pruneLibrary === true
                    && $settings->removeArchive === false),
            );

        // Execute the command
        $this->commandTester->execute([
            'archive' => $archiveFile,
            '--overwrite_settings' => true,
            '--overwrite_library' => true,
            '--prune_library' => true,
        ]);

        // Assert command output
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Importing ZIP Archive', $output);
        self::assertStringContainsString('ZIP Archive was imported successfully.', $output);

        // Assert command status code
        self::assertSame(0, $this->commandTester->getStatusCode());

        // Clean up
        @unlink($archiveFile);
    }

    #[Test]
    public function itShouldFailWhenFileDoesNotExist(): void
    {
        // Execute the command with non-existent file
        $this->commandTester->execute(['archive' => '/path/to/nonexistent/file.zip']);

        // Assert command output
        $output = $this->commandTester->getDisplay(true);

        self::assertStringContainsString('Importing ZIP Archive', $output);
        self::assertStringContainsString('error', strtolower($output));
        self::assertStringContainsString('/path/to/nonexistent/file.zip', $output);

        // Assert command status code
        self::assertSame(1, $this->commandTester->getStatusCode());
    }

    #[Test]
    public function itShouldFailWhenNoFileProvidedWithoutStream(): void
    {
        // Execute the command without a file
        $this->commandTester->execute([]);

        // Assert command output
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Importing ZIP Archive', $output);
        self::assertStringContainsString('Archive path must be provided when not using stream mode.', $output);

        // Assert command status code
        self::assertSame(1, $this->commandTester->getStatusCode());
    }

    #[Test]
    public function itShouldHandleImporterExceptions(): void
    {
        // Create a test archive file
        $archiveFile = tempnam(sys_get_temp_dir(), 'test_archive_');
        file_put_contents($archiveFile, 'test archive content');

        // Set up expectations for failure
        $this->importer->expects($this->once())
            ->method('import')
            ->willThrowException(new RuntimeException('Import process failed'));

        // Execute the command
        $this->commandTester->execute(['archive' => $archiveFile]);

        // Assert command output
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Importing ZIP Archive', $output);
        self::assertStringContainsString('Import failed: Import process failed', $output);

        // Assert command status code
        self::assertSame(1, $this->commandTester->getStatusCode());

        // Clean up
        @unlink($archiveFile);
    }
}
