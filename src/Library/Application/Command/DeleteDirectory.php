<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Command;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use Webmozart\Assert\Assert;

readonly class DeleteDirectory
{
    public function __construct(
        public Directory $directory,
    ) {
        Assert::notSame(
            RootDirectory::ID,
            $directory->getId(),
            'The root directory can not be deleted.',
        );
    }
}
