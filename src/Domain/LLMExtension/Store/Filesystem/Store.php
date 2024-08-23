<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\LLMExtension\Store\Filesystem;

use DZunke\NovDoc\Domain\SearchIndex\DocumentBag;
use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Store\VectorStoreInterface;

use Symfony\Component\Uid\Uuid;

use function array_map;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final class Store implements VectorStoreInterface
{
    public function __construct(
        private string $filepath,
    ) {
        if (file_exists($this->filepath)) {
            return;
        }

        file_put_contents($this->filepath, json_encode([], JSON_PRETTY_PRINT));
    }

    public function addDocument(Document $document): void
    {
        $existingDocuments = $this->convertFileToDocuments();
        $existingDocuments->append($document);

        $this->storeJson($existingDocuments);
    }

    public function addDocuments(array $documents): void
    {
        $existingDocuments = $this->convertFileToDocuments();

        foreach ($documents as $document) {
            $existingDocuments->append($document);
        }

        $this->storeJson($existingDocuments);
    }

    public function query(Vector $vector): array
    {
        // TODO: Implement query() method.
    }

    private function storeJson(DocumentBag $documentBag): void
    {
        $documentsAsArray = [];
        foreach ($documentBag as $document) {
            $documentsAsArray[] = [
                'id' => $document->id->toString(),
                'text' => $document->text,
                'vector' => $document->vector->getData(),
            ];
        }

        file_put_contents(
            $this->filepath,
            json_encode($documentsAsArray, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
        );
    }

    private function convertFileToDocuments(): DocumentBag
    {
        $rawJson = file_get_contents($this->filepath);
        if ($rawJson === false) {
            return new DocumentBag();
        }

        $deserializedJson = json_decode($rawJson, true, JSON_THROW_ON_ERROR, JSON_THROW_ON_ERROR);

        if (! is_array($deserializedJson)) {
            return new DocumentBag();
        }

        $documents = array_map(static function (array $rawDocument) {
            return new Document(
                id: Uuid::fromString($rawDocument['id']),
                text: $rawDocument['text'],
                vector: Vector::create1536($rawDocument['vector']),
            );
        }, $deserializedJson);

        return new DocumentBag(...$documents);
    }
}
