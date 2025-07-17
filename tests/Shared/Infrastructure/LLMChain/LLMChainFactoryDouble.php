<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use Override;
use PhpLlm\LlmChain\Chain\ChainInterface;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\PlatformInterface;
use PhpLlm\LlmChain\Platform\Response\Metadata\Metadata;
use PhpLlm\LlmChain\Platform\Response\RawResponseInterface;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\Response\ResponsePromise;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\Vector\Vector;

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

        return new readonly class ($knownCalls) implements ChainInterface {
            /** @param array<string, ResponseInterface> $knownCalls */
            public function __construct(private array $knownCalls = [])
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

                    public function getMetadata(): Metadata
                    {
                        return new Metadata();
                    }

                    public function getRawResponse(): RawResponseInterface|null
                    {
                        return null;
                    }

                    public function setRawResponse(RawResponseInterface $rawResponse): void
                    {
                    }
                };
            }
        };
    }

    #[Override]
    public function createPlatform(): PlatformInterface
    {
        $knownResponses = $this->knownResponses;

        return new readonly class ($knownResponses) implements PlatformInterface {
            /** @param array<class-string<Model>, ResponseInterface> $knownResponses */
            public function __construct(private array $knownResponses = [])
            {
            }

            /** @inheritDoc */
            public function request(Model $model, mixed $input, array $options = []): ResponsePromise
            {
                // Provide a default VectorResponse for Embeddings model
                if ($model instanceof Embeddings && ! isset($this->knownResponses[$model::class])) {
                    return ResponsePromiseFactory::create(new VectorResponse(new Vector([0.1, 0.2, 0.3])));
                }

                return ResponsePromiseFactory::create($this->knownResponses[$model::class] ?? null);
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
