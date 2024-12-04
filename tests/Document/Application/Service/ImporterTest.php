<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Application\Service;

use ChronicleKeeper\Chat\Application\Service\LLMContentOptimizer;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Service\Importer;
use ChronicleKeeper\Document\Application\Service\Importer\FileConverter;
use ChronicleKeeper\Test\Library\Domain\Entity\DirectoryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(Importer::class)]
class ImporterTest extends TestCase
{
    #[Test]
    public function itGivesEmptyMimeTypesWithoutRegisteredConverters(): void
    {
        $mimeTypes = (new Importer(
            [],
            self::createStub(LLMContentOptimizer::class),
            self::createStub(MessageBusInterface::class),
        ))->getSupportedMimeTypes();

        self::assertSame([], $mimeTypes);
    }

    #[Test]
    public function itRegistersConvertersByMimeTypes(): void
    {
        $fileConverter = $this->createMock(FileConverter::class);
        $fileConverter->method('mimeTypes')->willReturn(['application/pdf', 'text/plain']);

        $mimeTypes = (new Importer(
            [$fileConverter],
            self::createStub(LLMContentOptimizer::class),
            self::createStub(MessageBusInterface::class),
        ))->getSupportedMimeTypes();

        self::assertSame(['application/pdf', 'text/plain'], $mimeTypes);
    }

    #[Test]
    public function itThrowsExceptionWhenNoConverterFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There is no file converter registered for mime type "application/pdf"');

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getMimeType')->willReturn('application/pdf');

        (new Importer(
            [],
            self::createStub(LLMContentOptimizer::class),
            self::createStub(MessageBusInterface::class),
        ))->import($uploadedFile, (new DirectoryBuilder())->build(), false);
    }

    #[Test]
    public function itConvertsFileWithoutOptimization(): void
    {
        $fileConverter = $this->createMock(FileConverter::class);
        $fileConverter->method('mimeTypes')->willReturn(['application/pdf']);
        $fileConverter->method('convert')->willReturn('Hello World');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(static fn (StoreDocument $command) => new Envelope($command));

        $contentOptimizer = $this->createMock(LLMContentOptimizer::class);
        $contentOptimizer->expects($this->never())->method('optimize');

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getMimeType')->willReturn('application/pdf');
        $uploadedFile->method('getRealPath')->willReturn('/my_file.pdf');
        $uploadedFile->method('getClientOriginalName')->willReturn('my_file.pdf');

        $document      = (new Importer([$fileConverter], $contentOptimizer, $bus))->import(
            $uploadedFile,
            $directory = (new DirectoryBuilder())->build(),
            false,
        );

        self::assertSame('Hello World', $document->content);
        self::assertSame('my_file.pdf', $document->title);
        self::assertSame($directory, $document->directory);
    }

    #[Test]
    public function itConvertsFileWithContentOptimization(): void
    {
        $fileConverter = $this->createMock(FileConverter::class);
        $fileConverter->method('mimeTypes')->willReturn(['application/pdf']);
        $fileConverter->method('convert')->willReturn('Hello World');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(static fn (StoreDocument $command) => new Envelope($command));

        $contentOptimizer = $this->createMock(LLMContentOptimizer::class);
        $contentOptimizer->expects($this->once())
            ->method('optimize')
            ->with('Hello World')
            ->willReturn('Optimized World');

        $uploadedFile = self::createStub(UploadedFile::class);
        $uploadedFile->method('getMimeType')->willReturn('application/pdf');
        $uploadedFile->method('getRealPath')->willReturn('/my_file.pdf');
        $uploadedFile->method('getClientOriginalName')->willReturn('my_file.pdf');

        $document      = (new Importer([$fileConverter], $contentOptimizer, $bus))->import(
            $uploadedFile,
            $directory = (new DirectoryBuilder())->build(),
            true,
        );

        self::assertSame('Optimized World', $document->content);
        self::assertSame('my_file.pdf', $document->title);
        self::assertSame($directory, $document->directory);
    }
}
