<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Image\Domain\Entity;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\VectorStorage\VectorImage;
use ChronicleKeeper\Test\Library\Domain\Entity\ImageBuilder;
use Symfony\Component\Uid\Uuid;

class VectorImageBuilder
{
    private string $id;
    private Image $image;
    private string $content;
    private string $vectorContentHash;
    /** @var list<float> */
    private array $vector;

    public function __construct()
    {
        $this->id                = Uuid::v4()->toString();
        $this->image             = (new ImageBuilder())->build();
        $this->content           = 'Default Content';
        $this->vectorContentHash = 'Default Vector Content Hash';
        $this->vector            = [1.0, 2.0, 3.0];
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withImage(Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function withContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function withVectorContentHash(string $vectorContentHash): self
    {
        $this->vectorContentHash = $vectorContentHash;

        return $this;
    }

    /** @param list<float> $vector */
    public function withVector(array $vector): self
    {
        $this->vector = $vector;

        return $this;
    }

    public function build(): VectorImage
    {
        $vectorImage     = new VectorImage(
            $this->image,
            $this->content,
            $this->vectorContentHash,
            $this->vector,
        );
        $vectorImage->id = $this->id;

        return $vectorImage;
    }
}
