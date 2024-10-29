<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain\Entity;

use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use Symfony\Component\Uid\Uuid;

class Favorite
{
    public function __construct(
        private readonly string $id,
        private readonly Target $target,
    ) {
    }

    public static function create(Target $target): self
    {
        return new self(
            Uuid::v4()->toString(),
            $target,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTarget(): Target
    {
        return $this->target;
    }
}
