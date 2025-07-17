<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Shared\Infrastructure\LLMChain;

use PhpLlm\LlmChain\Platform\Response\Metadata\Metadata;
use PhpLlm\LlmChain\Platform\Response\RawResponseInterface;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\Response\ResponsePromise;

class ResponsePromiseFactory
{
    public static function create(ResponseInterface|null $response): ResponsePromise
    {
        $response ??= new class implements ResponseInterface {
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

        return new ResponsePromise(
            static fn () => $response,
            new readonly class ($response) implements RawResponseInterface
            {
                public function __construct(private ResponseInterface $response)
                {
                }

                /** @return array<string,mixed> */
                public function getRawData(): array
                {
                    return [];
                }

                public function getRawObject(): object
                {
                    return $this->response;
                }
            },
            [],
        );
    }
}
