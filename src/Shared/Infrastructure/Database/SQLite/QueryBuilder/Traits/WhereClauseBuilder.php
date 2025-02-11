<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Infrastructure\Database\SQLite\QueryBuilder\Traits;

use InvalidArgumentException;

use function implode;
use function in_array;
use function is_array;
use function sprintf;
use function strtoupper;
use function trim;

trait WhereClauseBuilder
{
    /** @var list<string> */
    private array $conditions = [];
    /** @var array<string, mixed> */
    private array $parameters = [];
    private int $paramCounter = 0;

    public function where(string $column, string $operator, mixed $value): self
    {
        $this->validateOperator($operator);

        $paramName = $this->generateParamName($column);

        if ($operator === 'IN' && is_array($value)) {
            $this->addInCondition($column, $paramName, $value);
        } elseif ($operator === 'NOT IN' && is_array($value)) {
            $this->addInCondition($column, $paramName, $value, true);
        } else {
            $this->addSimpleCondition($column, $operator, $paramName, $value);
        }

        return $this;
    }

    /** @param list<array{0: string, 1: string, 2: mixed}> $conditions */
    public function orWhere(array $conditions): self
    {
        $orParts = [];
        foreach ($conditions as [$column, $operator, $value]) {
            $this->validateOperator($operator);
            $paramName                    = $this->generateParamName($column);
            $orParts[]                    = sprintf('%s %s :%s', $column, $operator, $paramName);
            $this->parameters[$paramName] = $value;
        }

        if ($orParts !== []) {
            $this->conditions[] = '(' . implode(' OR ', $orParts) . ')';
        }

        return $this;
    }

    private function validateOperator(string $operator): void
    {
        $validOperators = ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'IN', 'NOT IN', 'MATCH'];
        if (! in_array(strtoupper(trim($operator)), $validOperators, true)) {
            throw new InvalidArgumentException('Invalid operator: ' . $operator);
        }
    }

    private function generateParamName(string $column): string
    {
        return sprintf('%s_%d', $column, ++$this->paramCounter);
    }

    /** @param array<int, string> $values */
    private function addInCondition(string $column, string $paramName, array $values, bool $not = false): void
    {
        $placeholders = [];
        foreach ($values as $i => $value) {
            $itemParam                    = sprintf('%s_%d', $paramName, $i);
            $placeholders[]               = ':' . $itemParam;
            $this->parameters[$itemParam] = $value;
        }

        if ($not === true) {
            $this->conditions[] = sprintf('%s NOT IN (%s)', $column, implode(',', $placeholders));

            return;
        }

        $this->conditions[] = sprintf('%s IN (%s)', $column, implode(',', $placeholders));
    }

    private function addSimpleCondition(string $column, string $operator, string $paramName, mixed $value): void
    {
        $this->conditions[]           = sprintf('%s %s :%s', $column, $operator, $paramName);
        $this->parameters[$paramName] = $value;
    }

    protected function getWhereClause(): string
    {
        if ($this->conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $this->conditions);
    }
}
