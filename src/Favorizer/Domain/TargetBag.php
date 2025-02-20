<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain;

use ArrayObject;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use JsonSerializable;

use function array_values;

/** @template-extends ArrayObject<int, Target> */
class TargetBag extends ArrayObject implements JsonSerializable
{
    public function __construct(Target ...$targets)
    {
        parent::__construct(array_values($targets));
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

    public function replace(Target $target): void
    {
        $this->remove($target);
        $this->append($target);
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

    /** @return array<int, Target> */
    public function jsonSerialize(): array
    {
        return array_values($this->getArrayCopy());
    }
}
