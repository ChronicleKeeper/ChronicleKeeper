<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\LLMChainExtension\Message;

use DZunke\NovDoc\Domain\Document\Document;
use DZunke\NovDoc\Domain\Library\Image\Image;
use PhpLlm\LlmChain\Message\Message;
use Symfony\Component\Uid\Uuid;

final class ExtendedMessage
{
    public readonly string $id;

    /**
     * @param list<Document> $documents
     * @param list<Image>    $images
     */
    public function __construct(
        public readonly Message $message,
        public array $documents = [],
        public array $images = [],
    ) {
        $this->id = Uuid::v4()->toString();
    }
}
