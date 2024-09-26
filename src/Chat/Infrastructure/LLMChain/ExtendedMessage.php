<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\LLMChain;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
use JsonSerializable;
use PhpLlm\LlmChain\Message\MessageInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-type ExtendedMessageArray = array{
 *      message: MessageInterface,
 *      documents: list<Document>,
 *      images: list<Image>,
 *      calledTools: list<array{tool: string, arguments: array<string,mixed>}>
 *  }
 */
final class ExtendedMessage implements JsonSerializable
{
    public readonly string $id;

    /**
     * @param list<Document>                                            $documents
     * @param list<Image>                                               $images
     * @param list<array{tool: string, arguments: array<string,mixed>}> $calledTools
     */
    public function __construct(
        public readonly MessageInterface $message,
        public array $documents = [],
        public array $images = [],
        public array $calledTools = [],
    ) {
        $this->id = Uuid::v4()->toString();
    }

    /** @return ExtendedMessageArray */
    public function jsonSerialize(): array
    {
        return [
            'message' => $this->message,
            'documents' => $this->documents,
            'images' => $this->images,
            'calledTools' => $this->calledTools,
        ];
    }
}
