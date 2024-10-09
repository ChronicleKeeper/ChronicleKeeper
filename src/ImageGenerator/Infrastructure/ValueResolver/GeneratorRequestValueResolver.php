<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Infrastructure\ValueResolver;

use ChronicleKeeper\ImageGenerator\Application\Query\GetGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

use function is_a;
use function is_string;

#[AutoconfigureTag('controller.argument_value_resolver', ['name' => 'image_generator_generator_request', 'priority' => 250])]
class GeneratorRequestValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @return list<GeneratorRequest> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if ($argumentType === null || ! is_a($argumentType, GeneratorRequest::class, true)) {
            return [];
        }

        $identifier = $request->attributes->get($argument->getName());
        if (! is_string($identifier) || ! Uuid::isValid($identifier)) {
            return [];
        }

        try {
            $generatorRequest = $this->queryService->query(new GetGeneratorRequest($identifier)) ?? throw new NotFoundHttpException();
        } catch (UnableToReadFile) {
            throw new NotFoundHttpException('Generator Request "' . $identifier . '" not found.');
        }

        return [$generatorRequest];
    }
}
