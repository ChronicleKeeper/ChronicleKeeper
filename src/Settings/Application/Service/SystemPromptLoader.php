<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SystemPromptLoader
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /** @return array<string, SystemPrompt> */
    public function load(): array
    {
        $event = new LoadSystemPrompts();

        $this->eventDispatcher->dispatch($event);

        return $event->getPrompts();
    }
}
