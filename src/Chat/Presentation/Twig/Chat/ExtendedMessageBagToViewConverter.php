<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Twig\Chat;

use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessageBag;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\Content;
use PhpLlm\LlmChain\Message\Content\Text;
use PhpLlm\LlmChain\Message\Role;
use PhpLlm\LlmChain\Message\UserMessage;

use function array_column;
use function array_filter;
use function implode;

use const PHP_EOL;

class ExtendedMessageBagToViewConverter
{
    public function __construct(private readonly SettingsHandler $settingsHandler)
    {
    }

    /** @return list<array{role: string, message: string, extended: ExtendedMessage}> */
    public function convert(ExtendedMessageBag $messageBag): array
    {
        $settings = $this->settingsHandler->get();

        $messages = [];

        foreach ($messageBag as $extendedMessage) {
            $originMessage = $extendedMessage->message;

            if ($originMessage->getRole() !== Role::User && $originMessage->getRole() !== Role::Assistant) {
                continue;
            }

            $role = $settings->getChatbotGeneral()->getChatbotName();
            if ($originMessage->getRole() === Role::User) {
                $role = $settings->getChatbotGeneral()->getChatterName();
            }

            $content = '';
            if ($originMessage instanceof UserMessage) {
                $contentText = array_filter(
                    $originMessage->content,
                    static fn (Content $entry) => $entry instanceof Text,
                );
                $content     = implode(PHP_EOL, array_column($contentText, 'text'));
            } elseif ($originMessage instanceof AssistantMessage) {
                $content = (string) $originMessage->content;
            }

            $messages[] = [
                'role' => $role,
                'message' => $content,
                'extended' => $extendedMessage,
            ];
        }

        return $messages;
    }
}
