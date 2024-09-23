<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Application\Service\Document;

use DZunke\NovDoc\Chat\Application\Service\LLMContentOptimizer;
use DZunke\NovDoc\Library\Application\Service\Document\Importer\FileConverter;
use DZunke\NovDoc\Library\Domain\Entity\Directory;
use DZunke\NovDoc\Library\Domain\Entity\Document;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use function array_key_exists;

class Importer
{
    /** @var array<string, FileConverter> */
    private array $fileConverters = [];

    /** @param iterable<FileConverter> $fileConverters */
    public function __construct(
        #[AutowireIterator(tag: 'document_file_converter')]
        iterable $fileConverters,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly LLMContentOptimizer $contentOptimizer,
    ) {
        foreach ($fileConverters as $converter) {
            $this->addFileConverter($converter);
        }
    }

    public function addFileConverter(FileConverter $converter): void
    {
        $mimeTypes = $converter->mimeTypes();
        foreach ($mimeTypes as $mimeType) {
            $this->fileConverters[$mimeType] = $converter;
        }
    }

    public function import(
        UploadedFile $file,
        Directory $directory,
        bool $optimizeImportedDocument = true,
    ): Document {
        $fileMimeType = (string) $file->getMimeType();
        if (! array_key_exists($fileMimeType, $this->fileConverters)) {
            throw new RuntimeException('There is no file converter registered for mime type "' . $fileMimeType . '"');
        }

        $convertedDocumentContent = $this->fileConverters[$fileMimeType]->convert($file->getRealPath());
        if ($optimizeImportedDocument === true) {
            $convertedDocumentContent = $this->contentOptimizer->optimize($convertedDocumentContent);
        }

        $document            = new Document($file->getClientOriginalName(), $convertedDocumentContent);
        $document->directory = $directory;

        $this->documentRepository->store($document);

        return $document;
    }
}
