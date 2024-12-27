<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function strcmp;
use function uasort;

class SystemPromptLoader
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /** @return array<string, SystemPrompt> */
    public function load(): array
    {
        $systemPrompts = $this->createSystemPromptCollection();
        $userPrompts   = $this->loadUserPrompts();

        $combinedPrompts = $systemPrompts + $userPrompts;

        // Mutlisort the array by the purpose and then by the name
        uasort(
            $combinedPrompts,
            static function (SystemPrompt $a, SystemPrompt $b): int {
                $purposeComparison = strcmp($a->getPurpose()->value, $b->getPurpose()->value);
                if ($purposeComparison === 0) {
                    return strcmp($a->getName(), $b->getName());
                }

                return $purposeComparison;
            },
        );

        return $combinedPrompts;
    }

    /** @return array<string, SystemPrompt> */
    private function createSystemPromptCollection(): array
    {
        $event = new LoadSystemPrompts();
        $this->eventDispatcher->dispatch($event);

        return $event->getPrompts();
    }

    /** @return array<string, SystemPrompt> */
    private function loadUserPrompts(): array
    {
        $userPrompts = [];
        if ($this->fileAccess->exists('storage', 'system_prompts.json')) {
            $content     = $this->fileAccess->read('storage', 'system_prompts.json');
            $userPrompts = $this->serializer->deserialize($content, SystemPrompt::class . '[]', 'json');
        }

        return $userPrompts;
    }
}
