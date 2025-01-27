<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\World\Domain\Entity;

use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use Symfony\Component\Uid\Uuid;

class ItemBuilder
{
    private string $id;
    private ItemType $type           = ItemType::PERSON;
    private string $name             = 'A default person';
    private string $shortDescription = 'A default description of a person.';

    public function __construct()
    {
        $this->id = Uuid::v4()->toString();
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withType(ItemType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withShortDescription(string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function build(): Item
    {
        return new Item(
            $this->id,
            $this->type,
            $this->name,
            $this->shortDescription,
        );
    }
}
