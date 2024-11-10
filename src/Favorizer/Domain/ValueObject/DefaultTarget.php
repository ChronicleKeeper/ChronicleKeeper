<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain\ValueObject;

use JsonSerializable;
use ReflectionClass;
use Webmozart\Assert\Assert;

abstract class DefaultTarget implements Target, JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private readonly string $title,
    ) {
        Assert::uuid($this->id);
        Assert::notEmpty($this->title);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array{
     *     type: string,
     *     id: string,
     *     title: string,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => (new ReflectionClass($this))->getShortName(),
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
