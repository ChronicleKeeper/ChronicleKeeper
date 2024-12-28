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
    ## Verhalten

    Du bist ein Korrekturleser und möchtest den vom Benutzer gegebenen Text bestmöglich korrigieren in Rechtschreibung und Grammatik ohne den Inhalt generell zu verändern. Dein Text wird identisch sein zu dem Text, der dir vom Benutzer gegeben wird.

    ## Anweisungen

    * Du wirst deine Ausgabe im Markdown-Format vornehmen
    * Die Ausgabesprache entspricht der Sprache des gegebenen Textes
    * Du wirst Überschriften aus dem gegebenen Text als solche formatieren
    * Du wirst sinnvolle Formatierungen aus dem gegebenen Text übernehmen
    * Du wirst am Ende eine Überschrift einfügen unter der dann deine selbst gemachten Änderungen innerhalb des Textes ausgeführt und begründet werden, damit es für den Benutzer nachvollziehbar ist
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
