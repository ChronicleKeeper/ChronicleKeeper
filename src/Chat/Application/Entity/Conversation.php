<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Entity;

use ChronicleKeeper\Chat\Application\ValueObject\Settings;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings as AppSettings;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use Symfony\Component\Uid\Uuid;

class Conversation
{
    public function __construct(
        public string $id,
        public string $title,
        public Settings $settings,
        public ExtendedMessageBag $messages,
    ) {
    }

    public static function createEmpty(): Conversation
    {
        return new self(
            Uuid::v4()->toString(),
            'Ungespeichert',
            new Settings(),
            new ExtendedMessageBag(),
        );
    }

    public static function createFromSettings(AppSettings $settings): Conversation
    {
        return new self(
            Uuid::v4()->toString(),
            'Ungespeichert',
            new Settings(
                Version::gpt4oMini()->name,
                $settings->getChatbotTuning()->getTemperature(),
                $settings->getChatbotTuning()->getDocumentsMaxDistance(),
                $settings->getChatbotTuning()->getDocumentsMaxDistance(),
            ),
            new ExtendedMessageBag(
                new ExtendedMessage(Message::forSystem($settings->getChatbotSystemPrompt()->getSystemPrompt())),
            ),
        );
    }
}
