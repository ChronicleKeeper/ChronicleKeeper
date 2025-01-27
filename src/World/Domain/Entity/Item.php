<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Domain\Entity;

use ChronicleKeeper\Shared\Domain\Entity\AggregateRoot;
use ChronicleKeeper\World\Domain\Event\ItemChangedDescription;
use ChronicleKeeper\World\Domain\Event\ItemCreated;
use ChronicleKeeper\World\Domain\Event\ItemRenamed;
use ChronicleKeeper\World\Domain\ValueObject\ItemType;
use ChronicleKeeper\World\Domain\ValueObject\MediaReference;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

class Item extends AggregateRoot
{
    /** @param array<MediaReference> $mediaReferences */
    public function __construct(
        private readonly string $id,
        private readonly ItemType $type,
        private string $name,
        private string $shortDescription,
        private array $mediaReferences = [],
    ) {
        Assert::uuid($id, 'The identifier of the item has to be an UUID.');
    }

    public static function create(
        ItemType $type,
        string $name,
        string $shortDescription,
    ): Item {
        $item = new self(
            Uuid::v4()->toString(),
            $type,
            $name,
            $shortDescription,
        );
        $item->record(new ItemCreated($item));

        return $item;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): ItemType
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /** @return array<MediaReference> */
    public function getMediaReferences(): array
    {
        return $this->mediaReferences;
    }

    public function rename(string $newName): void
    {
        if ($newName === $this->name) {
            return;
        }

        $this->record(new ItemRenamed($this, $this->name));
        $this->name = $newName;
    }

    public function changeShortDescription(string $newShortDescription): void
    {
        if ($newShortDescription === $this->shortDescription) {
            return;
        }

        $this->record(new ItemChangedDescription($this, $this->shortDescription));
        $this->shortDescription = $newShortDescription;
    }

    public function addMediaReference(MediaReference $reference): void
    {
        $this->mediaReferences[] = $reference;
    }
}
