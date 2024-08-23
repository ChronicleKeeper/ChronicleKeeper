<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Repository;

use DateTimeImmutable;
use DZunke\NovDoc\Domain\Document\Document;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Finder\Finder;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filectime;
use function is_array;
use function is_readable;
use function json_decode;
use function json_encode;
use function json_validate;
use function usort;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;

class FilesystemDocumentRepository
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $documentStoragePath,
    ) {
    }

    public function store(Document $document): void
    {
        // When stored it is updated! Maybe change later with a change detection ... but yeah .. it is changed for now
        $document->updatedAt = new DateTimeImmutable();

        $filename        = $document->id . '.json';
        $filepath        = $this->documentStoragePath . DIRECTORY_SEPARATOR . $filename;
        $documentAsArray = $document->toArray();
        $documentAsJson  = json_encode($documentAsArray, JSON_PRETTY_PRINT);

        file_put_contents($filepath, $documentAsJson);
    }

    /** @return list<Document> */
    public function findAll(): array
    {
        $documentFinder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->documentStoragePath)
            ->files();

        $documents = [];
        foreach ($documentFinder as $documentFound) {
            try {
                $documents[] = $this->convertJsonToDocument($documentFound->getContents());
            } catch (RuntimeException $e) {
                $this->logger->debug($e);
            }
        }

        usort(
            $documents,
            static fn (Document $documentLeft, Document $documentRight) => $documentLeft->title <=> $documentRight->title,
        );

        return $documents;
    }

    public function findById(string $id): Document|null
    {
        $documentJson = $this->getContentOfDocumentFile($id . '.json');

        if ($documentJson === null || ! json_validate($documentJson)) {
            return null;
        }

        try {
            return $this->convertJsonToDocument($documentJson);
        } catch (RuntimeException $e) {
            $this->logger->debug($e);

            return null;
        }
    }

    private function convertJsonToDocument(string $json): Document
    {
        $documentArr = json_decode($json, true);

        if (! is_array($documentArr) || ! Document::isDocumentArray($documentArr)) {
            throw new RuntimeException('Document to load contain invalid content.');
        }

        $document = Document::fromArray($documentArr);

        $filepath            = $this->documentStoragePath . DIRECTORY_SEPARATOR . $document->id . '.json';
        $document->updatedAt = (new DateTimeImmutable())->setTimestamp((int) filectime($filepath));

        return $document;
    }

    private function getContentOfDocumentFile(string $filename): string|null
    {
        $filepath = $this->documentStoragePath . DIRECTORY_SEPARATOR . $filename;
        if (! file_exists($filepath) || ! is_readable($filepath)) {
            return null;
        }

        $documentJson = file_get_contents($filepath);
        if ($documentJson === false || ! json_validate($documentJson)) {
            return null;
        }

        return $documentJson;
    }
}
