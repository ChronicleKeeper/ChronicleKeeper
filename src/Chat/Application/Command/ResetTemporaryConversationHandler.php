<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Command;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Model\Message\Message;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class ResetTemporaryConversationHandler
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(ResetTemporaryConversation $message): void
    {
        // Fetch the Settings to create it initially
        $settings = $this->settingsHandler->get();

        // First Setup before creation of the real temporary conversation containing all temporary data
        $conversation = Conversation::createFromSettings($settings);
        $conversation->rename($message->title);
        $conversation->getMessages()[] = new ExtendedMessage(Message::forSystem($message->utilizePrompt->getContent()));

        // And now store the newly created temporary conversation
        $this->bus->dispatch(new StoreTemporaryConversation(Conversation::createFromConversation($conversation)));
    }
}
