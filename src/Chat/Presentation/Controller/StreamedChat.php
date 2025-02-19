<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Command\StoreConversation;
use ChronicleKeeper\Chat\Application\Command\StoreTemporaryConversation;
use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Chat\Presentation\Twig\Chat\ExtendedMessageBagToViewConverter;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Response\StreamResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

use function assert;
use function flush;
use function json_encode;
use function ob_flush;

use const JSON_THROW_ON_ERROR;

class StreamedChat extends AbstractController
{
    public function __construct(
        private readonly LLMChainFactory $chain,
        private readonly QueryService $queryService,
        private readonly ExtendedMessageBagToViewConverter $messageBagToViewConverter,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[Route(
        '/chat/stream/{conversationId}',
        name: 'chat_stream',
        requirements: ['conversationId' => Requirement::UUID],
        defaults: ['conversationId' => null],
        priority: 150,
    )]
    public function streamedChat(string|null $conversationId): Response
    {
        if ($conversationId === null) {
            $conversation = $this->queryService->query(new GetTemporaryConversationParameters());
        } else {
            $conversation = $this->queryService->query(new FindConversationByIdParameters($conversationId));
        }

        return $this->render(
            'chat/stream.html.twig',
            [
                'conversation' => $conversation,
                'isTemporary' => $conversationId === null,
                'messages' => $this->messageBagToViewConverter->convert($conversation->getMessages()),
            ],
        );
    }

    #[Route('/chat/stream/message', name: 'chat_stream_message', methods: ['GET'], priority: 160)]
    public function message(Request $request): StreamedResponse
    {
        $message        = (string) $request->query->get('message');
        $conversationId = (string) $request->query->get('conversation');

        $isTemporary  = $conversationId === '';
        $conversation = $conversationId !== '' ?
            $this->queryService->query(new FindConversationByIdParameters($conversationId)) :
            $this->queryService->query(new GetTemporaryConversationParameters());

        assert($conversation instanceof Conversation);

        return new StreamedResponse(function () use ($message, $isTemporary, $conversation): void {
            echo ":\n\n";
            ob_flush();
            flush();

            $messages   = $conversation->getMessages();
            $messages[] = new ExtendedMessage(message: Message::ofUser($message));

            $response = $this->chain->create()->call(
                $messages->getLLMChainMessages(),
                [
                    'model' => $conversation->getSettings()->version,
                    'temperature' => $conversation->getSettings()->temperature,
                    'stream' => true,
                ],
            );
            assert($response instanceof StreamResponse);

            $fullResponse = '';
            foreach ($response->getContent() as $chunk) {
                $fullResponse .= $chunk;
                echo 'data: ' . json_encode([
                    'type' => 'chunk',
                    'chunk' => $chunk,
                ], JSON_THROW_ON_ERROR) . "\n\n";
                ob_flush();
                flush();
            }

            // Add the messages to conversation and persist
            $messages[] = $fullMessage = new ExtendedMessage(message: Message::ofAssistant($fullResponse));

            // Send completion message
            echo 'data: ' . json_encode([
                'type' => 'complete',
                'id' => $fullMessage->id,
            ], JSON_THROW_ON_ERROR) . "\n\n";
            ob_flush();
            flush();

            // And do some stuff, like storing, after the connection from frontend was closed
            if ($isTemporary) {
                $this->bus->dispatch(new StoreTemporaryConversation($conversation));
            } else {
                $this->bus->dispatch(new StoreConversation($conversation));
            }
        }, Response::HTTP_OK, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
