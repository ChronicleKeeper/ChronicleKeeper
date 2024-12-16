<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Document\Infrastructure\LLMChain\DocumentSearch;
use ChronicleKeeper\Library\Infrastructure\LLMChain\Tool\LibraryImages;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Response\TextResponse;

use function assert;

class ChatMessageExecution
{
    public function __construct(
        private readonly LLMChainFactory $chain,
        private readonly DocumentSearch $libraryDocuments,
        private readonly LibraryImages $libraryImages,
        private readonly ToolUsageCollector $collector,
    ) {
    }

    public function execute(
        string $message,
        Conversation $conversation,
        string $useModel = 'gpt-4o',
        float $useTemperature = 1.0,
    ): void {
        $messages   = $conversation->messages;
        $messages[] = new ExtendedMessage(message: Message::ofUser($message));

        // Set Maximum distances in tools
        $this->libraryDocuments->setOneTimeMaxDistance($conversation->settings->documentsMaxDistance);
        $this->libraryImages->setOneTimeMaxDistance($conversation->settings->imagesMaxDistance);

        $response = $this->chain->create()->call(
            $messages->getLLMChainMessages(),
            [
                'model' => $useModel,
                'temperature' => $useTemperature,
            ],
        );
        assert($response instanceof TextResponse);

        // Remove maximum distances in tools after the response ... just for saftey of the request
        $this->libraryDocuments->setOneTimeMaxDistance($conversation->settings->documentsMaxDistance);
        $this->libraryImages->setOneTimeMaxDistance($conversation->settings->imagesMaxDistance);

        $response = new ExtendedMessage(message: Message::ofAssistant($response->getContent()));

        /*$this->appendReferencedDocumentsFromBackground($response);
        $this->appendReferencedImages($response);
        $this->appendCalledTools($response); */

        $messages[] = $response;

        $conversation->messages = $messages;
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
        $referencedImages = $this->libraryImages->getReferencedImages();
        if ($referencedImages === []) {
            return;
        }

        $response->images = $referencedImages;
    }

    private function appendReferencedDocumentsFromBackground(ExtendedMessage $response): void
    {
        $referencedDocuments = $this->libraryDocuments->getReferencedDocuments();
        if ($referencedDocuments === []) {
            return;
        }

        $response->documents = $referencedDocuments;
    }
}
