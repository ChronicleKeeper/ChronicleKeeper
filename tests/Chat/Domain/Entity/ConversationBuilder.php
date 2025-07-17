<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Domain\Entity;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Chat\Domain\ValueObject\Settings;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use Symfony\Component\Uid\Uuid;

class ConversationBuilder
{
    private string $id;
    private string $title;
    private Directory $directory;
    private Settings $settings;
    private MessageBag $messages;

    public function __construct()
    {
        $this->id        = Uuid::v4()->toString();
        $this->title     = 'Default Title';
        $this->directory = RootDirectory::get();
        $this->settings  = new Settings();
        $this->messages  = new MessageBag();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function withDirectory(Directory $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function withSettings(Settings $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function withMessages(MessageBag $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    public function build(): Conversation
    {
        return new Conversation(
            $this->id,
            $this->title,
            $this->directory,
            $this->settings,
            $this->messages,
        );
    }
}
