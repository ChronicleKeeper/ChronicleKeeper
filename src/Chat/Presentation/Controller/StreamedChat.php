<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Presentation\Twig\Chat\ExtendedMessageBagToViewConverter;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

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
    ) {
    }

    #[Route(
        '/chat/stream/{conversation}',
        name: 'chat_stream',
        defaults: ['conversation' => null],
        priority: 150,
    )]
    public function streamedChat(string|null $conversation): Response
    {
        if ($conversation === null) {
            $conversation = $this->queryService->query(new GetTemporaryConversationParameters());
        } else {
            $conversation = $this->queryService->query(new FindConversationByIdParameters($conversation));
        }

        return $this->render(
            'chat/stream.html.twig',
            [
                'conversation' => $conversation,
                'messages' => $this->messageBagToViewConverter->convert($conversation->getMessages()),
            ],
        );
    }

    #[Route('/chat/stream/message', name: 'chat_stream_message', methods: ['GET'], priority: 160)]
    public function message(Request $request): StreamedResponse
    {
        $message = (string) $request->query->get('message');

        return new StreamedResponse(function () use ($message): void {
            echo ":\n\n";
            ob_flush();
            flush();

            $response = $this->chain->create()->call(
                new MessageBag(Message::forSystem('You are a thoughtful philosopher.'), Message::ofUser($message)),
                [
                    'model' => 'gpt-4o-mini',
                    'temperature' => 1.0,
                    'stream' => true,
                ],
            );

            foreach ($response->getContent() as $chunk) {
                echo 'data: ' . json_encode(['chunk' => $chunk], JSON_THROW_ON_ERROR) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
