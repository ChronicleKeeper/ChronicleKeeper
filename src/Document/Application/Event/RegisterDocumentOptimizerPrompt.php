<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Application\Event;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class RegisterDocumentOptimizerPrompt
{
    private const string PROMPT = <<<'TEXT'
    You are a proof reader and will simnply correct and reformat text to plain markdown. Where it is recommended
    you will add formattings to the text. The response will be given in plain markdown without escape ticks around
    the response.
    TEXT;

    public function __invoke(LoadSystemPrompts $event): void
    {
        $event->add(SystemPrompt::createSystemPrompt(
            'b1e1eb26-9460-4722-9704-8e7b068a8b5a',
            Purpose::DOCUMENT_OPTIMIZER,
            'Bibliothek - Optimierung von Dokumenten',
            self::PROMPT,
        ));
    }
}
