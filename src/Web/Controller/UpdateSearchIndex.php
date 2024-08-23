<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller;

use DZunke\NovDoc\Domain\Prompts\Prompt;
use DZunke\NovDoc\Domain\Prompts\PromptBag;
use DZunke\NovDoc\Domain\SearchIndex\DocumentBag;
use DZunke\NovDoc\Domain\SearchIndex\Updater;
use PhpLlm\LlmChain\Document\Document;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

use function assert;
use function file_get_contents;
use function is_string;

#[Route('/reload', name: 'update_search_index')]
class UpdateSearchIndex
{
    public function __construct(
        private Updater $updater,
        private Environment $environment,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $documentContent = file_get_contents(__DIR__ . '/../../../var/documents/Ferdinand Feilner.txt');
        assert(is_string($documentContent));

        $this->updater->update(
            new DocumentBag(Document::fromText($documentContent)),
            new PromptBag(
                new Prompt(
                    'embedding-summary-persons-places',
                    'Erstelle eine Zusammenfassung des folgenden Textes. Benenne dabei die Charaktere und Orte um die es geht. Etwaige Decknamen sollten mit den eigentlichen Namen auch genannt werden.',
                ),
                new Prompt(
                    'embedding-summary-character-development',
                    'Erstelle eine Zusammenfassung des folgenden Textes. Eine eventuelle Charakterentwicklung sollte dabei klar werden. Etwaige Decknamen sollten mit den eigentlichen Namen auch genannt werden.',
                ),
            ),
        );

        return new Response('Index Updated!');
    }
}
