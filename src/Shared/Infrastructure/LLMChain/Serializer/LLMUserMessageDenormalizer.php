<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\LLMChain\Serializer;

use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\Content;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\MessageInterface;
use PhpLlm\LlmChain\Message\Role;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\ToolCallMessage;
use PhpLlm\LlmChain\Message\UserMessage;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

use function array_filter;
use function array_map;
use function is_string;

final class LLMUserMessageDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
        Assert::keyExists($data, 'role');

        $messageClass = $this->getMessageClass($data['role']);
        if ($messageClass !== UserMessage::class) {
            return $this->denormalizer->denormalize($data, $messageClass, $format, $context);
        }

        Assert::keyExists($data, 'content');
        if (is_string($data['content'])) {
            return new UserMessage(new Text($data['content']));
        }

        $content = array_map(static function (array $content): Content|null {
            if ($content['type'] === 'text') {
                return new Text($content['text']);
            }

            if ($content['type'] === 'image_url') {
                return new Image($content['image_url']['url']);
            }

            return null;
        }, $data['content']);

        return new UserMessage(...array_filter($content));
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === MessageInterface::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [MessageInterface::class => true];
    }

    private function getMessageClass(string $role): string
    {
        $role = Role::from($role);

        return match ($role) {
            Role::System => SystemMessage::class,
            Role::Assistant => AssistantMessage::class,
            Role::User => UserMessage::class,
            Role::ToolCall => ToolCallMessage::class,
        };
    }
}
