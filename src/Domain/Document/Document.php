<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Document;

use DateTimeImmutable;
use DateTimeInterface;
use DZunke\NovDoc\Domain\Library\Directory\RootDirectory;
use Symfony\Component\Uid\Uuid;

use function array_key_exists;
use function count;
use function sha1;
use function strlen;

/**
 * @phpstan-type DocumentArray = array{
 *     id: string,
 *     title: string,
 *     content: string,
 *     directory?: string,
 *     last_updated?: string
 * }
 */
class Document
{
    public string $id;
    public DateTimeImmutable $updatedAt;
    public Directory $directory;

    public function __construct(public string $title, public string $content)
    {
        $this->id        = Uuid::v4()->toString();
        $this->directory = RootDirectory::get();
        $this->updatedAt = new DateTimeImmutable();
    }

    /** @param DocumentArray $documentArr */
    public static function fromArray(array $documentArr): Document
    {
        $document     = new Document($documentArr['title'], $documentArr['content']);
        $document->id = $documentArr['id'];

        if (array_key_exists('last_updated', $documentArr)) {
            $document->updatedAt = new DateTimeImmutable($documentArr['last_updated']);
        }

        return $document;
    }

    /**
     * @param mixed[] $documentArr
     *
     * @phpstan-return ($documentArr is DocumentArray ? true : false)
     */
    public static function isDocumentArray(array $documentArr): bool
    {
        return count($documentArr) >= 3
            && array_key_exists('id', $documentArr)
            && array_key_exists('title', $documentArr)
            && array_key_exists('content', $documentArr);
    }

    /** @return DocumentArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'directory' => $this->directory->id,
            'last_updated' => $this->updatedAt->format(DateTimeInterface::ATOM),
        ];
    }

    public function getSize(): int
    {
        return strlen($this->content);
    }

    public function getContentHash(): string
    {
        return sha1($this->content);
    }
}
