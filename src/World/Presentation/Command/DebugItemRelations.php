<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Command;

use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ValueError;

use function array_map;
use function count;
use function implode;

#[AsCommand(
    name: 'debug:world:item-relations',
    description: 'The command allows to check if the possible relation types are build correctly.',
)]
final class DebugItemRelations extends Command
{
    protected function configure(): void
    {
        $this->addArgument(
            'item_types',
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'The types of the item to check relations for.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('World Debugging of World Item Type Relations');

        $itemTypes = ItemType::cases();

        $searchedTypes = $input->getArgument('item_types');
        if ($searchedTypes !== []) {
            try {
                $itemTypes = array_map(
                    static fn (string $type) => ItemType::from($type),
                    $searchedTypes,
                );
            } catch (ValueError) {
                $possibleTypes = implode(
                    ', ',
                    array_map(static fn (ItemType $type) => $type->value, ItemType::cases()),
                );

                $io->error('Invalid item type provided. Choose from the following available types: ' . $possibleTypes);

                return self::FAILURE;
            }
        }

        $io->info(
            'Checking the following types: '
            . implode(', ', array_map(static fn (ItemType $type) => $type->value, $itemTypes)),
        );

        $builtRelationTypesTo  = ItemType::getRelationTypesTo();
        $typesWithoutRelations = [];
        foreach ($itemTypes as $itemType) {
            if (! isset($builtRelationTypesTo[$itemType->value])) {
                $typesWithoutRelations[] = $itemType->value;
                continue;
            }

            $table = new Table($output);
            $table->setHeaderTitle($itemType->getLabel());
            $table->setColumnWidths([20, 40]);
            $table->setStyle('box');

            foreach ($builtRelationTypesTo[$itemType->value] as $relationSourceName => $relationSourceTypes) {
                $table->addRow([
                    new TableCell(
                        ItemType::from($relationSourceName)->getLabel(),
                        [
                            'colspan' => 2,
                            'style' => new TableCellStyle(['fg' => 'green']),
                        ],
                    ),
                ]);
                foreach ($relationSourceTypes as $relationTargetType => $relationTargetLabel) {
                    $table->addRow([$relationTargetType, $relationTargetLabel]);
                }
            }

            $table->render();
            $io->newLine(2);
        }

        if (count($typesWithoutRelations) > 0) {
            $io->warning(
                'The following item types do not have any relations defined: '
                . implode(', ', $typesWithoutRelations),
            );

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
