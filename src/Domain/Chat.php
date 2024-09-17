<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use DZunke\NovDoc\Infrastructure\LLMChainExtension\Tool\NovalisBackground;
use DZunke\NovDoc\Infrastructure\LLMChainExtension\Tool\NovalisImages;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\ToolChain;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

use const PHP_EOL;

final class Chat
{
    private const SESSION_KEY = 'chat-messages';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ToolChain $toolChain,
        private readonly SettingsHandler $settingsHandler,
        private readonly NovalisBackground $novalisBackground,
        private readonly NovalisImages $novalisImages,
        private readonly RouterInterface $router,
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

        $response = $this->toolChain->call($messages, ['model' => Version::GPT_4o_MINI]);
        $response = $this->appendReferencedDocumentsFromBackground($response);
        $response = $this->appendReferencedImages($response);

        $messages[] = Message::ofAssistant($response);

        $this->saveMessages($messages);
    }

    private function appendReferencedImages(string $response): string
    {
        $referencedImages = $this->novalisImages->getReferencedImages();
        if ($referencedImages === []) {
            return $response;
        }

        $referencedDocumentsString = '***' . PHP_EOL . 'Die folgenden Bilder wurden referenziert:' . PHP_EOL;
        foreach ($referencedImages as $image) {
            $linkLabel = $image->directory->flattenHierarchyTitle() . ' > ' . $image->title;
            $link      = $this->router->generate('library_image_view', ['image' => $image->id]);

            $referencedDocumentsString .= '- [' . $linkLabel . '](' . $link . ')' . PHP_EOL;
        }

        return $response . PHP_EOL . $referencedDocumentsString;
    }

    private function appendReferencedDocumentsFromBackground(string $response): string
    {
        $referencedDocuments = $this->novalisBackground->getReferencedDocuments();
        if ($referencedDocuments === []) {
            return $response;
        }

        $referencedDocumentsString = '***' . PHP_EOL . 'Die folgenden Dokumente wurden referenziert:' . PHP_EOL;
        foreach ($referencedDocuments as $document) {
            $linkLabel = $document->directory->flattenHierarchyTitle() . ' > ' . $document->title;
            $link      = $this->router->generate('library_document_view', ['document' => $document->id]);

            $referencedDocumentsString .= '- [' . $linkLabel . '](' . $link . ')' . PHP_EOL;
        }

        return $response . PHP_EOL . $referencedDocumentsString;
    }

    private function initMessages(): MessageBag
    {
        $settings = $this->settingsHandler->get();

        return new MessageBag(Message::forSystem($settings->getChatbotSystemPrompt()->getSystemPrompt()));
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
