<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Document\Domain\Entity;

use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use Symfony\Component\Uid\Uuid;

class VectorDocumentBuilder
{
    private string $id;
    private Document $document;
    private string $content;
    private string $vectorContentHash;
    /** @var list<float> */
    private array $vector;

    public function __construct()
    {
        $this->id                = Uuid::v4()->toString();
        $this->document          = (new DocumentBuilder())->build();
        $this->content           = 'Default Content';
        $this->vectorContentHash = 'Default Vector Content Hash';
        $this->vector            = [1.0, 2.0, 3.0];
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function withContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function withVectorContentHash(string $vectorContentHash): self
    {
        $this->vectorContentHash = $vectorContentHash;

        return $this;
    }

    /** @param list<float> $vector */
    public function withVector(array $vector): self
    {
        $this->vector = $vector;

        return $this;
    }

    public function build(): VectorDocument
    {
        $vectorDocument     = new VectorDocument(
            $this->document,
            $this->content,
            $this->vectorContentHash,
            $this->vector,
        );
        $vectorDocument->id = $this->id;

        return $vectorDocument;
    }
}
