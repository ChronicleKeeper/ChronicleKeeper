<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\ValueResolver;

use DZunke\NovDoc\Library\Domain\Entity\Image;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemImageRepository;
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

    /** @return iterable<Image> */
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

        $image = $this->imageRepository->findById($imageIdentifier);
        if ($image === null) {
            throw new RuntimeException('Image "' . $imageIdentifier . '" not found.');
        }

        return [$image];
    }
}
