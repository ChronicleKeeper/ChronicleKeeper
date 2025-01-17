<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\ValueResolver;

use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Shared\Application\Query\QueryService;
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
        private readonly QueryService $queryService,
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
            $image = $this->queryService->query(new GetImage($imageIdentifier));
        } catch (UnableToReadFile) {
            throw new RuntimeException('Image "' . $imageIdentifier . '" not found.');
        }

        return [$image];
    }
}
