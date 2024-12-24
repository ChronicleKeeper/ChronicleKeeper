<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

use function array_map;
use function array_values;

final class ExtendedMessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    public const string WITH_CONTEXT_DOCUMENTS = 'with_context_documents';
    public const string WITH_CONTEXT_IMAGES    = 'with_context_images';
    public const string WITH_DEBUG_FUNCTIONS   = 'with_debug_functions';

    private DenormalizerInterface $denormalizer;

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /** @inheritDoc */
    public function denormalize(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): ExtendedMessage {
        Assert::isArray($data);

        $message = $this->denormalizer->denormalize(
            $data['message'],
            MessageInterface::class,
            $format,
            $context,
        );

        $extendedMessage     = new ExtendedMessage($message);
        $extendedMessage->id = $data['id'];

        $documents = [];
        if (isset($context[self::WITH_CONTEXT_DOCUMENTS]) && $context[self::WITH_CONTEXT_DOCUMENTS] === true) {
            // Denormalize documents to be added
            $documents = array_map(
                static fn (array $document) => new Reference($document['id'], $document['type'], $document['title']),
                $data['context']['documents'] ?? [],
            );
        }

        $images = [];
        if (isset($context[self::WITH_CONTEXT_IMAGES]) && $context[self::WITH_CONTEXT_IMAGES] === true) {
            // Denormalize documents to be added
            $images = array_map(
                static fn (array $image) => new Reference($image['id'], $image['type'], $image['title']),
                $data['context']['images'] ?? [],
            );
        }

        $extendedMessage->context = new MessageContext(array_values($documents), array_values($images));

        $debug = [];
        if (isset($context[self::WITH_DEBUG_FUNCTIONS]) && $context[self::WITH_DEBUG_FUNCTIONS] === true) {
            // Denormalize debug functions to be added
            $debug = array_map(
                static fn (array $function) => new FunctionDebug(
                    $function['tool'],
                    $function['arguments'],
                    $function['result'],
                ),
                $data['debug']['functions'] ?? [],
            );
        }

        $extendedMessage->debug = new MessageDebug(array_values($debug));

        return $extendedMessage;
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === ExtendedMessage::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [ExtendedMessage::class => true];
    }
}
