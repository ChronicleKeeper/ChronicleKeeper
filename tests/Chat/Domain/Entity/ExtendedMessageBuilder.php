<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\Component\Uid\Uuid;

class ExtendedMessageBuilder
{
    private string $id;
    private MessageInterface $message;
    /** @var list<Document> */
    private array $documents = [];
    /** @var list<Image> */
    private array $images = [];
    /** @var list<array{tool: string, arguments: array<string,mixed>}> */
    private array $calledTools = [];

    public function __construct()
    {
        $this->id      = Uuid::v4()->toString();
        $this->message = (new SystemMessageBuilder())->build();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withMessage(MessageInterface $message): self
    {
        $this->message = $message;

        return $this;
    }

    /** @param list<Document> $documents */
    public function withDocuments(array $documents): self
    {
        $this->documents = $documents;

        return $this;
    }

    /** @param list<Image> $images */
    public function withImages(array $images): self
    {
        $this->images = $images;

        return $this;
    }

    /** @param list<array{tool: string, arguments: array<string,mixed>}> $calledTools */
    public function withCalledTools(array $calledTools): self
    {
        $this->calledTools = $calledTools;

        return $this;
    }

    public function build(): ExtendedMessage
    {
        $extendedMessage     = new ExtendedMessage(
            $this->message,
            $this->documents,
            $this->images,
            $this->calledTools,
        );
        $extendedMessage->id = $this->id;

        return $extendedMessage;
    }
}
