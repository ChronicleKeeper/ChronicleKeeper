<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\ValueResolver;

use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

use function is_a;
use function is_string;

#[AutoconfigureTag('controller.argument_value_resolver', ['name' => 'library_directory', 'priority' => 250])]
class LibraryDirectoryResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    /** @return iterable<Directory> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if ($argumentType === null || ! is_a($argumentType, Directory::class, true)) {
            return [];
        }

        $directoryIdentifier = $request->attributes->get($argument->getName());
        if (! is_string($directoryIdentifier) || ! Uuid::isValid($directoryIdentifier)) {
            return [];
        }

        try {
            $directory = $this->directoryRepository->findById($directoryIdentifier) ?? throw new NotFoundHttpException();
        } catch (UnableToReadFile) {
            throw new RuntimeException('Directory "' . $directoryIdentifier . '" not found.');
        }

        return [$directory];
    }
}
