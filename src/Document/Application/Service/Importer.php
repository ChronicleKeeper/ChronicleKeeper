<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Service;

use ChronicleKeeper\Chat\Application\Service\LLMContentOptimizer;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Application\Service\Importer\FileConverter;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

use function array_key_exists;
use function array_keys;

class Importer
{
    /** @var array<string, FileConverter> */
    private array $fileConverters = [];

    /** @param iterable<FileConverter> $fileConverters */
    public function __construct(
        #[AutowireIterator(tag: 'document_file_converter')]
        iterable $fileConverters,
        private readonly LLMContentOptimizer $contentOptimizer,
        private readonly MessageBusInterface $bus,
    ) {
        foreach ($fileConverters as $converter) {
            $this->addFileConverter($converter);
        }
    }

    /** @return list<string> */
    public function getSupportedMimeTypes(): array
    {
        return array_keys($this->fileConverters);
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

        $this->bus->dispatch(new StoreDocument($document));

        return $document;
    }
}
