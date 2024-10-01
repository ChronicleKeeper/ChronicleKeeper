<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\ValueResolver;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Uid\Uuid;

use function is_a;
use function is_string;

#[AutoconfigureTag('controller.argument_value_resolver', ['name' => 'library_image', 'priority' => 250])]
class LibraryImageResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly FilesystemImageRepository $imageRepository,
    ) {
    }

    /** @return list<Image> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if ($argumentType === null || ! is_a($argumentType, Image::class, true)) {
            return [];
        }

        $imageIdentifier = $request->attributes->get($argument->getName());
        if (! is_string($imageIdentifier) || ! Uuid::isValid($imageIdentifier)) {
            return [];
        }

        try {
            $image = $this->imageRepository->findById($imageIdentifier);
        } catch (UnableToReadFile) {
            throw new RuntimeException('Image "' . $imageIdentifier . '" not found.');
        }

        return [$image];
    }
}
