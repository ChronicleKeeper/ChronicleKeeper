<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\ToolChain;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;

final class Chat
{
    private const SESSION_KEY = 'chat-messages';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ToolChain $toolChain,
        private readonly SettingsHandler $settingsHandler,
    ) {
    }

    public function loadMessages(): MessageBag
    {
        $messageBag = $this->requestStack->getSession()->get(self::SESSION_KEY, $this->initMessages());
        if (! $messageBag instanceof MessageBag) {
            throw new RuntimeException('Session is corrupted and does not contain a MessageBag.');
        }

        return $messageBag;
    }

    public function submitMessage(string $message): void
    {
        $messages = $this->loadMessages();

        $messages[] = Message::ofUser($message);
        $response   = $this->toolChain->call($messages, ['model' => Version::GPT_4o_MINI]);
        $messages[] = Message::ofAssistant($response);

        $this->saveMessages($messages);
    }

    private function initMessages(): MessageBag
    {
        $settings = $this->settingsHandler->get();

        return new MessageBag(Message::forSystem($settings->systemPrompt));
    }

    public function reset(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }

    private function saveMessages(MessageBag $messages): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $messages);
    }
}
