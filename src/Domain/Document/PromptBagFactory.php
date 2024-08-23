<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Document;

use DZunke\NovDoc\Domain\Prompts\Prompt;
use DZunke\NovDoc\Domain\Prompts\PromptBag;

class PromptBagFactory
{
    public static function create(): PromptBag
    {
        return new PromptBag(
            new Prompt(
                'embedding-summary-persons-places',
                'Erstelle eine Zusammenfassung des folgenden Textes. Benenne dabei die Charaktere und Orte um die es geht. Etwaige Decknamen sollten mit den eigentlichen Namen auch genannt werden.',
            ),
            /* new Prompt(
            'embedding-summary-character-development',
            'Erstelle eine Zusammenfassung des folgenden Textes. Eine eventuelle Charakterentwicklung sollte dabei klar werden. Etwaige Decknamen sollten mit den eigentlichen Namen auch genannt werden.',
            ), */
        );
    }
}
