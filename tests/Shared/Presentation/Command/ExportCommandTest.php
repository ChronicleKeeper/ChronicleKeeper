<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Presentation\Command;

use ChronicleKeeper\Settings\Application\Service\Exporter;
use ChronicleKeeper\Shared\Presentation\Command\ExportCommand;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const STDOUT;

#[CoversClass(ExportCommand::class)]
#[Small]
final class ExportCommandTest extends TestCase
{
    private MockObject&Exporter $exporter;
    private CommandTester $commandTester;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->exporter = $this->createMock(Exporter::class);
        $command        = new ExportCommand($this->exporter);

        $application = new Application();
        $application->add($command);
        $command = $application->find('app:export');

        $this->commandTester = new CommandTester($command);
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->exporter, $this->commandTester);
    }

    #[Test]
    public function itShouldExportToSpecifiedFile(): void
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'export_test_');
        @unlink($outputFile); // Remove the file so we can test creation

        // Set up expectation
        $this->exporter->expects($this->once())
            ->method('export')
            ->with($outputFile)
            ->willReturn($outputFile);

        // Execute the command
        $this->commandTester->execute(['filename' => $outputFile]);

        // Assert command output
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Exporting ZIP Archive', $output);
        self::assertStringContainsString('Exported ZIP Archive:', $output);

        // Assert command status code
        self::assertSame(0, $this->commandTester->getStatusCode());
    }

    #[Test]
    public function itShouldFailWhenFileExistsWithoutForceOption(): void
    {
        // Create a file that already exists
        $existingFile = tempnam(sys_get_temp_dir(), 'export_existing_');

        try {
            // Exporter should not be called
            $this->exporter->expects($this->never())->method('export');

            // Execute the command without force option
            $this->commandTester->execute(['filename' => $existingFile]);

            // Assert command output
            $output = $this->commandTester->getDisplay();
            self::assertStringContainsString('Exporting ZIP Archive', $output);
            self::assertStringContainsString('File already exists, use --force to overwrite.', $output);

            // Assert command status code
            self::assertSame(1, $this->commandTester->getStatusCode());
        } finally {
            // Clean up
            @unlink($existingFile);
        }
    }

    #[Test]
    public function itShouldOverwriteExistingFileWithForceOption(): void
    {
        // Create a file that already exists
        $existingFile = tempnam(sys_get_temp_dir(), 'export_force_');

        try {
            // Set up expectation
            $this->exporter->expects($this->once())
                ->method('export')
                ->with($existingFile)
                ->willReturn($existingFile);

            // Execute the command with force option
            $this->commandTester->execute([
                'filename' => $existingFile,
                '--force' => true,
            ]);

            // Assert command output
            $output = $this->commandTester->getDisplay();
            self::assertStringContainsString('Exporting ZIP Archive', $output);
            self::assertStringContainsString('Exported ZIP Archive:', $output);

            // Assert command status code
            self::assertSame(0, $this->commandTester->getStatusCode());
        } finally {
            // Clean up
            @unlink($existingFile);
        }
    }

    #[Test]
    public function itShouldFailWhenNoFilenameProvidedWithoutStream(): void
    {
        // Exporter should not be called
        $this->exporter->expects($this->never())->method('export');
        $this->exporter->expects($this->never())->method('exportToStream');

        // Execute the command without filename and without stream option
        $this->commandTester->execute([]);

        // Assert command output
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Exporting ZIP Archive', $output);
        self::assertStringContainsString('Filename must be provided when not using stream mode.', $output);

        // Assert command status code
        self::assertSame(1, $this->commandTester->getStatusCode());
    }

    #[Test]
    public function itShouldExportToStreamWhenStreamOptionProvided(): void
    {
        // Set up expectation for stream export
        $this->exporter->expects($this->once())
            ->method('exportToStream')
            ->with(STDOUT);

        // Execute the command with stream option
        $this->commandTester->execute(['--stream' => true]);

        // With stream output, we shouldn't see normal console output
        $output = $this->commandTester->getDisplay();
        self::assertEmpty($output);

        // Assert command status code
        self::assertSame(0, $this->commandTester->getStatusCode());
    }

    #[Test]
    public function itShouldHandleExporterExceptions(): void
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'export_error_');
        @unlink($outputFile); // Remove the file so we can test creation

        // Set up expectation for exception
        $this->exporter->expects($this->once())
            ->method('export')
            ->with($outputFile)
            ->willThrowException(new RuntimeException('Export process failed'));

        // Execute the command
        $this->commandTester->execute(['filename' => $outputFile]);

        // Assert command output
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Exporting ZIP Archive', $output);
        self::assertStringContainsString('Export failed: Export process failed', $output);

        // Assert command status code
        self::assertSame(1, $this->commandTester->getStatusCode());
    }
}
