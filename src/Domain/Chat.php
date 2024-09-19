<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use DZunke\NovDoc\Infrastructure\LLMChainExtension\Message\ExtendedMessage;
use DZunke\NovDoc\Infrastructure\LLMChainExtension\Message\ExtendedMessageBag;
use DZunke\NovDoc\Infrastructure\LLMChainExtension\Tool\NovalisBackground;
use DZunke\NovDoc\Infrastructure\LLMChainExtension\Tool\NovalisImages;
use PhpLlm\LlmChain\Message\Message;
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
        private readonly NovalisBackground $novalisBackground,
        private readonly NovalisImages $novalisImages,
    ) {
    }

    public function loadMessages(): ExtendedMessageBag
    {
        $messageBag = $this->requestStack->getSession()->get(self::SESSION_KEY, $this->initMessages());
        if (! $messageBag instanceof ExtendedMessageBag) {
            throw new RuntimeException('Session is corrupted and does not contain a MessageBag.');
        }

        return $messageBag;
    }

    public function submitMessage(string $message): void
    {
        $messages = $this->loadMessages();

        $messages[] = new ExtendedMessage(message: Message::ofUser($message));

        $response = $this->toolChain->call(
            $messages->getLLMChainMessages(),
            [
                'model' => Version::GPT_4o,
                'temperature' => $this->settingsHandler->get()->getChatbotTuning()->getTemperature(),
            ],
        );
        $response = new ExtendedMessage(message: Message::ofAssistant($response));

        $this->appendReferencedDocumentsFromBackground($response);
        $this->appendReferencedImages($response);

        $messages[] = $response;

        $this->saveMessages($messages);
    }

    private function appendReferencedImages(ExtendedMessage $response): void
    {
        $referencedImages = $this->novalisImages->getReferencedImages();
        if ($referencedImages === []) {
            return;
        }

        $response->images = $referencedImages;
    }

    private function appendReferencedDocumentsFromBackground(ExtendedMessage $response): void
    {
        $referencedDocuments = $this->novalisBackground->getReferencedDocuments();
        if ($referencedDocuments === []) {
            return;
        }

        $response->documents = $referencedDocuments;
    }

    private function initMessages(): ExtendedMessageBag
    {
        $settings = $this->settingsHandler->get();

        return new ExtendedMessageBag(new ExtendedMessage(
            Message::forSystem($settings->getChatbotSystemPrompt()->getSystemPrompt()),
        ));
    }

    public function reset(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_KEY);
    }

    private function saveMessages(ExtendedMessageBag $messages): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $messages);
    }
}
