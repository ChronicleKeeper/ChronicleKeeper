<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use Override;
use PhpLlm\LlmChain\ChainInterface;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\PlatformInterface;

class LLMChainFactoryDouble extends LLMChainFactory
{
    /** @var array<class-string<Model>, ResponseInterface> */
    private array $knownResponses = [];
    /** @var array<string, ResponseInterface> */
    private array $knownCalls = [];

    /** @phpstan-ignore constructor.missingParentCall */
    public function __construct()
    {
    }

    #[Override]
    public function create(): ChainInterface
    {
        $knownCalls = $this->knownCalls;

        return new class ($knownCalls) implements ChainInterface {
            /** @param array<string, ResponseInterface> $knownCalls */
            public function __construct(private readonly array $knownCalls = [])
            {
            }

            /** @inheritDoc */
            public function call(MessageBagInterface $messages, array $options = []): ResponseInterface
            {
                return $this->knownCalls[$options['model']] ?? new class implements ResponseInterface {
                    public function getContent(): string
                    {
                        return 'Mocked Response';
                    }
                };
            }
        };
    }

    #[Override]
    public function createPlatform(): PlatformInterface
    {
        $knownResponses = $this->knownResponses;

        return new class ($knownResponses) implements PlatformInterface {
            /** @param array<class-string<Model>, ResponseInterface> $knownResponses */
            public function __construct(private readonly array $knownResponses = [])
            {
            }

            /** @inheritDoc */
            public function request(Model $model, mixed $input, array $options = []): ResponseInterface
            {
                return $this->knownResponses[$model::class] ?? new class implements ResponseInterface {
                    public function getContent(): string
                    {
                        return 'Mocked Response';
                    }
                };
            }
        };
    }

    /** @param class-string<Model> $model */
    public function addPlatformResponse(string $model, ResponseInterface $response): void
    {
        $this->knownResponses[$model] = $response;
    }

    public function addCallResponse(string $model, ResponseInterface $response): void
    {
        $this->knownCalls[$model] = $response;
    }
}
