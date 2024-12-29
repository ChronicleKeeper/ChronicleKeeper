<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Event;

use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Settings\Domain\Event\LoadSystemPrompts;
use ChronicleKeeper\Settings\Domain\ValueObject\SystemPrompt\Purpose;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class RegisterImageDescriperPrompt
{
    private const string PROMPT = <<<'TEXT'
    Beschreibe bis ins kleinste Detail jede relevante Information aus diesem Bild. Füge keine Links ein.
    Schlussfolgerungen möchtest du nicht machen, sondern nur den Inhalt beschreiben. Ziehe Informationen
    der Funktion library_documents andhand des Titels zu rate um das Bild noch besser zu bewerten.
    TEXT;

    public function __invoke(LoadSystemPrompts $event): void
    {
        $event->add(SystemPrompt::createSystemPrompt(
            'e0642877-c910-4d33-9011-ba7d1affba1b',
            Purpose::IMAGE_UPLOAD,
            'Bibliothek - Bildbeschreibung beim Hochladen',
            self::PROMPT,
        ));
    }
}
