<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Image;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

use function array_diff;
use function array_keys;
use function array_values;

final class ExtendedMessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    private DenormalizerInterface $denormalizer;

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): mixed
    {
        Assert::isArray($data);
        Assert::true(array_diff([
            'message',
            'documents',
            'images',
            'calledTools',
        ], array_keys($data)) === []);

        $message = $this->denormalizer->denormalize(
            $data['message'],
            MessageInterface::class,
            $format,
            $context,
        );

        $documents = $this->denormalizer->denormalize($data['documents'], Document::class . '[]', $format, $context);
        $images    = $this->denormalizer->denormalize($data['images'], Image::class . '[]', $format, $context);

        $extendedMessage     = new ExtendedMessage(
            $message,
            array_values($documents),
            array_values($images),
            $data['calledTools'],
        );
        $extendedMessage->id = $data['id'];

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
