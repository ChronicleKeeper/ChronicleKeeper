<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\Serializer;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use PhpLlm\LlmChain\Model\Message\MessageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

final class ExtendedMessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    private DenormalizerInterface $denormalizer;

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): ExtendedMessage
    {
        Assert::isArray($data);

        $message = $this->denormalizer->denormalize(
            $data['message'],
            MessageInterface::class,
            $format,
            $context,
        );

        $extendedMessage     = new ExtendedMessage($message);
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
