<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Application\Service;

use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageContext;
use ChronicleKeeper\Chat\Domain\ValueObject\MessageDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Document\Infrastructure\LLMChain\DocumentSearch;
use ChronicleKeeper\Library\Infrastructure\LLMChain\Tool\ImageSearch;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Response\TextResponse;

use function assert;

class SingleChatMessageExecution
{
    public function __construct(
        private readonly LLMChainFactory $chain,
        private readonly DocumentSearch $libraryDocuments,
        private readonly ImageSearch $libraryImages,
        private readonly RuntimeCollector $runtimeCollector,
    ) {
    }

    public function execute(
        string $message,
        Conversation $conversation,
        string $useModel = 'gpt-4o',
        float $useTemperature = 1.0,
    ): void {
        $messages   = $conversation->getMessages();
        $messages[] = new ExtendedMessage(message: Message::ofUser($message));

        // Set Maximum distances in tools
        $this->libraryDocuments->setOneTimeMaxDistance($conversation->getSettings()->documentsMaxDistance);
        $this->libraryImages->setOneTimeMaxDistance($conversation->getSettings()->imagesMaxDistance);

        $response = $this->chain->create()->call(
            $messages->getLLMChainMessages(),
            [
                'model' => $useModel,
                'temperature' => $useTemperature,
            ],
        );
        assert($response instanceof TextResponse);

        // Remove maximum distances in tools after the response ... just for saftey of the request
        $this->libraryDocuments->setOneTimeMaxDistance(null);
        $this->libraryImages->setOneTimeMaxDistance(null);

        $response          = new ExtendedMessage(message: Message::ofAssistant($response->getContent()));
        $response->context = $this->buildMessageContext();
        $response->debug   = $this->buildMessageDebug();

        $messages[] = $response;
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
