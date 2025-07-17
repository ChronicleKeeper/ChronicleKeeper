<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\Message;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\MessageBagConverter;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Document\Infrastructure\LLMChain\DocumentSearch;
use ChronicleKeeper\Library\Infrastructure\LLMChain\Tool\ImageSearch;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Platform\Response\TextResponse;

use function assert;

class SingleChatMessageExecution
{
    public function __construct(
        private readonly LLMChainFactory $chain,
        private readonly DocumentSearch $libraryDocuments,
        private readonly ImageSearch $libraryImages,
        private readonly RuntimeCollector $runtimeCollector,
        private readonly MessageBagConverter $messageBagConverter,
    ) {
    }

    public function execute(
        string $message,
        Conversation $conversation,
        string $useModel = 'gpt-4o',
        float $useTemperature = 1.0,
    ): void {
        $messages   = $conversation->getMessages();
        $messages[] = Message::forUser($message);

        // Set Maximum distances in tools
        $this->libraryDocuments->setOneTimeMaxDistance($conversation->getSettings()->documentsMaxDistance);
        $this->libraryImages->setOneTimeMaxDistance($conversation->getSettings()->imagesMaxDistance);

        $response = $this->chain->create()->call(
            $this->messageBagConverter->toLlmMessageBag($messages),
            [
                'model' => $useModel,
                'temperature' => $useTemperature,
            ],
        );
        assert($response instanceof TextResponse);

        // Remove maximum distances in tools after the response ... just for saftey of the request
        $this->libraryDocuments->setOneTimeMaxDistance(null);
        $this->libraryImages->setOneTimeMaxDistance(null);

        $messages[] = Message::forAssistant(
            $response->getContent(),
            $this->buildMessageContext(),
            $this->buildMessageDebug(),
        );
    }

    private function buildMessageDebug(): MessageDebug
    {
        return new MessageDebug(functions: $this->runtimeCollector->flushFunctionDebug());
    }

    private function buildMessageContext(): MessageContext
    {
        return new MessageContext(
            documents: $this->runtimeCollector->flushReferenceByType(Reference::TYPE_DOCUMENT),
            images: $this->runtimeCollector->flushReferenceByType(Reference::TYPE_IMAGE),
        );
    }
}
