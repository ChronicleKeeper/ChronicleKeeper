<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings as AppSettings;
use ChronicleKeeper\Shared\Domain\Sluggable;
use JsonSerializable;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Model\Message\Message;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class Conversation implements JsonSerializable, Sluggable
{
    public function __construct(
        public string $id,
        public string $title,
        public Directory $directory,
        public Settings $settings,
        public ExtendedMessageBag $messages,
    ) {
    }

    public static function createEmpty(): Conversation
    {
        return new self(
            Uuid::v4()->toString(),
            'Ungespeichert',
            RootDirectory::get(),
            new Settings(),
            new ExtendedMessageBag(),
        );
    }

    public static function createFromSettings(AppSettings $settings): Conversation
    {
        return new self(
            Uuid::v4()->toString(),
            'Ungespeichert',
            RootDirectory::get(),
            new Settings(
                GPT::GPT_4O_MINI,
                $settings->getChatbotTuning()->getTemperature(),
                $settings->getChatbotTuning()->getDocumentsMaxDistance(),
                $settings->getChatbotTuning()->getDocumentsMaxDistance(),
            ),
            new ExtendedMessageBag(
                new ExtendedMessage(Message::forSystem($settings->getChatbotSystemPrompt()->getSystemPrompt())),
            ),
        );
    }

    public function getSlug(): string
    {
        return (new AsciiSlugger('de'))->slug($this->title)->toString();
    }

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     directory: string,
     *     settings: Settings,
     *     messages: ExtendedMessageBag
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'directory' => $this->directory->id,
            'settings' => $this->settings,
            'messages' => $this->messages,
        ];
    }
}
