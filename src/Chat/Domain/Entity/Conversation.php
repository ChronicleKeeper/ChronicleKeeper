<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Event\ConversationCreated;
use ChronicleKeeper\Chat\Domain\Event\ConversationMovedToDirectory;
use ChronicleKeeper\Chat\Domain\Event\ConversationRenamed;
use ChronicleKeeper\Chat\Domain\Event\ConversationSettingsChanged;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Settings\Domain\ValueObject\Settings as AppSettings;
use ChronicleKeeper\Shared\Domain\Entity\AggregateRoot;
use ChronicleKeeper\Shared\Domain\Sluggable;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class Conversation extends AggregateRoot implements Sluggable
{
    public function __construct(
        private readonly string $id,
        private string $title,
        private Directory $directory,
        private Settings $settings,
        private readonly MessageBag $messages,
    ) {
    }

    public static function createEmpty(): Conversation
    {
        $conversation = new self(
            Uuid::v4()->toString(),
            'Unbekanntes Gespräch',
            RootDirectory::get(),
            new Settings(),
            new MessageBag(),
        );
        $conversation->record(new ConversationCreated($conversation));

        return $conversation;
    }

    public static function createFromConversation(Conversation $conversation): Conversation
    {
        $conversation = new self(
            Uuid::v4()->toString(),
            $conversation->getTitle(),
            $conversation->getDirectory(),
            new Settings(
                $conversation->getSettings()->version,
                $conversation->getSettings()->temperature,
                $conversation->getSettings()->imagesMaxDistance,
                $conversation->getSettings()->documentsMaxDistance,
            ),
            new MessageBag(...$conversation->getMessages()->getArrayCopy()),
        );
        $conversation->record(new ConversationCreated($conversation));

        return $conversation;
    }

    public static function createFromSettings(AppSettings $settings): Conversation
    {
        $conversation = new self(
            Uuid::v4()->toString(),
            'Unbekanntes Gespräch',
            RootDirectory::get(),
            new Settings(
                GPT::GPT_4O_MINI,
                $settings->getChatbotTuning()->getTemperature(),
                $settings->getChatbotTuning()->getDocumentsMaxDistance(),
                $settings->getChatbotTuning()->getDocumentsMaxDistance(),
            ),
            new MessageBag(),
        );
        $conversation->record(new ConversationCreated($conversation));

        return $conversation;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDirectory(): Directory
    {
        return $this->directory;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function getMessages(): MessageBag
    {
        return $this->messages;
    }

    public function getSlug(): string
    {
        return (new AsciiSlugger('de'))->slug($this->title)->toString();
    }

    public function rename(string $title): void
    {
        if ($title === $this->title) {
            return;
        }

        $this->record(new ConversationRenamed($this, $this->title));

        $this->title = $title;
    }

    public function moveToDirectory(Directory $directory): void
    {
        if ($directory->equals($this->directory)) {
            return;
        }

        $this->record(new ConversationMovedToDirectory($this, $this->directory));

        $this->directory = $directory;
    }

    public function changeSettings(Settings $settings): void
    {
        if ($this->settings->equals($settings)) {
            return;
        }

        $this->record(new ConversationSettingsChanged($this, $this->settings));

        $this->settings = $settings;
    }
}
