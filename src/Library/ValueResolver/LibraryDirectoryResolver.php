<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\ValueResolver;

use DZunke\NovDoc\Library\Domain\Entity\Directory;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
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

        $directory = $this->directoryRepository->findById($directoryIdentifier);
        if ($directory === null) {
            throw new RuntimeException('Directory "' . $directoryIdentifier . '" not found.');
        }

        return [$directory];
    }
}
