<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service;

use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\Entity\MessageBag;
use ChronicleKeeper\Settings\Application\SettingsHandler;

class ExtendedMessageBagToViewConverter
{
    public function __construct(private readonly SettingsHandler $settingsHandler)
    {
    }

    /** @return list<array{role: string, message: string, extended: Message}> */
    public function convert(MessageBag $messageBag): array
    {
        $settings = $this->settingsHandler->get();

        $messages = [];

        foreach ($messageBag as $originMessage) {
            if (! $originMessage->isUser() && ! $originMessage->isAssistant()) {
                continue;
            }

            $role = $settings->getChatbotGeneral()->getChatbotName();
            if ($originMessage->isUser()) {
                $role = $settings->getChatbotGeneral()->getChatterName();
            }

            $messages[] = [
                'role' => $role,
                'message' => $originMessage->getContent(),
                'extended' => $originMessage,
            ];
        }

        return $messages;
    }
}
