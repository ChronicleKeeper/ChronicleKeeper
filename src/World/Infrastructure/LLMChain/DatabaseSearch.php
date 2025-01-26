<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Infrastructure\LLMChain;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\FindRelationsOfItem;
use ChronicleKeeper\World\Application\Query\SearchWorldItems;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\Relation;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use Symfony\Component\String\AbstractString;

use function array_map;
use function count;
use function implode;
use function Symfony\Component\String\u;

use const PHP_EOL;

#[AsTool(
    'world_items',
    <<<'MARKDOWN'
    ### Function Description
    Searches a database of world entities like countries, regions, locations, organizations, persons, events, quests,
    campaigns, or objects. Those items deliver relations to each other and associated documents and other media.
    So woprtful additional information when someone asks for those things.

    #### Input Requirements
    - Accepts a **comma-separated list of labels**, each representing a single entity.
    - Each label must be concise (e.g., `"Hubert"`, `"The Magical Stone"`, `"Honeyland"`).
    - Example combined input: `"Hubert,The Magical Stone,Honeyland"`

    #### Usage Notes
    - Integrates with `library_documents` and `library_images` for additional context.
    - Automatically analyzes user input to extract relevant labels.
    MARKDOWN,
)]
class DatabaseSearch
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly RuntimeCollector $runtimeCollector,
    ) {
    }

    /** @param string $search comma-separated list of concise labels */
    public function __invoke(string $search): string
    {
        $search = array_map(
            static fn (AbstractString $label): string => $label->trim()->toString(),
            u($search)->split(','),
        );

        $results = [];
        foreach ($search as $singleSearch) {
            $results = $this->queryService->query(new SearchWorldItems(search: $singleSearch)) + $results;
        }

        if (count($results) > 0) {
            $response = $this->formatResponseString($results);
            $this->runtimeCollector->addFunctionDebug(new FunctionDebug(
                'world_items',
                ['search' => $search],
                $response,
            ));

            return $response;
        }

        $this->runtimeCollector->addFunctionDebug(new FunctionDebug('world_items', ['search' => $search]));

        return 'No results from the item search for the following labels: ' . implode(', ', $search);
    }

    /** @param Item[] $results */
    private function formatResponseString(array $results): string
    {
        $response = 'I have found the following items that are associated to the question:' . PHP_EOL;
        foreach ($results as $result) {
            $response .= 'Name: ' . $result->getName() . PHP_EOL;
            $response .= 'Type: ' . $result->getType()->value . PHP_EOL;
            $response .= 'Short Description: ' . $result->getShortDescription() . PHP_EOL;

            /** @var Relation[] $relations */
            $relations = $this->queryService->query(new FindRelationsOfItem($result->getId()));
            if ($relations === []) {
                continue;
            }

            $response .= 'Relations:' . PHP_EOL;
            foreach ($relations as $relation) {
                $toItem    = $relation->toItem;
                $response .= '  - ' . $result->getType()->getRelationLabelTo(
                    $toItem->getType(),
                    $relation->relationType,
                ) . ' ' . $toItem->getName() . ' (' . $toItem->getType()->value . ')' . PHP_EOL;
            }

            $response .= PHP_EOL . '---' . PHP_EOL . PHP_EOL;
        }

        return $response;
    }
}
