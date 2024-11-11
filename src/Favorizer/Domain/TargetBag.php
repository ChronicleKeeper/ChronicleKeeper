<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain;

use ArrayObject;
use ChronicleKeeper\Favorizer\Domain\Exception\MaxTargetsInBag;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use JsonSerializable;

use function array_values;
use function count;

/** @template-extends ArrayObject<int, Target> */
class TargetBag extends ArrayObject implements JsonSerializable
{
    private const int MAX_TARGETS = 10;

    public function __construct(Target ...$targets)
    {
        parent::__construct(array_values($targets));
    }

    /** @param Target $value */
    public function append(mixed $value): void
    {
        if (count($this) > self::MAX_TARGETS - 1) {
            throw MaxTargetsInBag::reached(self::MAX_TARGETS);
        }

        parent::append($value);
    }

    /**
     * @param int|null $key
     * @param Target   $value
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        if (count($this) > self::MAX_TARGETS - 1) {
            throw MaxTargetsInBag::reached(self::MAX_TARGETS);
        }

        parent::offsetSet($key, $value);
    }

    public function exists(Target $target): bool
    {
        foreach ($this as $checkTarget) {
            if ($checkTarget->getId() === $target->getId()) {
                return true;
            }
        }

        return false;
    }

    public function remove(Target $target): void
    {
        foreach ($this as $key => $checkTarget) {
            if ($checkTarget->getId() === $target->getId()) {
                $this->offsetUnset($key);

                return;
            }
        }
    }

    public function isLimitReached(): bool
    {
        return count($this) >= self::MAX_TARGETS;
    }

    /** @return array<int, Target> */
    public function jsonSerialize(): array
    {
        return array_values($this->getArrayCopy());
    }
}
