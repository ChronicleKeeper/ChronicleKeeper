<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Query;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\SerializerInterface;

final class FindUserPromptsQuery implements Query
{
    public function __construct(
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /** @return SystemPrompt[] */
    public function query(QueryParameters $parameters): array
    {
        if ($this->fileAccess->exists('storage', 'system_prompts.json')) {
            $content = $this->fileAccess->read('storage', 'system_prompts.json');

            return $this->serializer->deserialize($content, SystemPrompt::class . '[]', 'json');
        }

        return [];
    }
}
