<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\LLMChain;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
use PhpLlm\LlmChain\Message\Message;
use Symfony\Component\Uid\Uuid;

final class ExtendedMessage
{
    public readonly string $id;

    /**
     * @param list<Document>                                            $documents
     * @param list<Image>                                               $images
     * @param list<array{tool: string, arguments: array<string,mixed>}> $calledTools
     */
    public function __construct(
        public readonly Message $message,
        public array $documents = [],
        public array $images = [],
        public array $calledTools = [],
    ) {
        $this->id = Uuid::v4()->toString();
    }
}
