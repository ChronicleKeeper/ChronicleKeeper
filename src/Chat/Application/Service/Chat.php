<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Chat\Application\Service;

use DZunke\NovDoc\Chat\Infrastructure\LLMChain\ExtendedMessage;
use DZunke\NovDoc\Chat\Infrastructure\LLMChain\ExtendedMessageBag;
use DZunke\NovDoc\Library\Infrastructure\LLMChain\Tool\NovalisBackground;
use DZunke\NovDoc\Library\Infrastructure\LLMChain\Tool\NovalisImages;
use DZunke\NovDoc\Settings\Application\SettingsHandler;
use DZunke\NovDoc\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;

use function assert;
use function is_string;

final class Chat
{
    private const SESSION_KEY = 'chat-messages';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Chain $chain,
        private readonly SettingsHandler $settingsHandler,
        private readonly NovalisBackground $novalisBackground,
        private readonly NovalisImages $novalisImages,
        private readonly ToolUsageCollector $collector,
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

        $response = $this->chain->call(
            $messages->getLLMChainMessages(),
            [
                'model' => Version::gpt4oMini()->name,
                'temperature' => $this->settingsHandler->get()->getChatbotTuning()->getTemperature(),
            ],
        );
        assert(is_string($response));

        $response = new ExtendedMessage(message: Message::ofAssistant($response));

        $this->appendReferencedDocumentsFromBackground($response);
        $this->appendReferencedImages($response);
        $this->appendCalledTools($response);

        $messages[] = $response;

        $this->saveMessages($messages);
    }

    private function appendCalledTools(ExtendedMessage $response): void
    {
        $toolCalls = $this->collector->getCalls();
        if ($toolCalls === []) {
            return;
        }

        $response->calledTools = $toolCalls;
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
