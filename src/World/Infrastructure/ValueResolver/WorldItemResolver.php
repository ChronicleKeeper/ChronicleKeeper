<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Infrastructure\ValueResolver;

use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\World\Application\Query\GetWorldItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Throwable;

use function is_a;
use function is_string;

#[AutoconfigureTag('controller.argument_value_resolver', ['name' => 'world_item', 'priority' => 250])]
final class WorldItemResolver implements ValueResolverInterface
{
    public function __construct(private readonly QueryService $queryService)
    {
    }

    /** @return iterable<Item> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if ($argumentType === null || ! is_a($argumentType, Item::class, true)) {
            return [];
        }

        $identifier = $request->get($argument->getName(), $request->get('id'));
        if (! is_string($identifier) || ! Uuid::isValid($identifier)) {
            return [];
        }

        try {
            return [$this->queryService->query(new GetWorldItem($identifier))];
        } catch (Throwable $e) {
            throw new NotFoundHttpException(
                message: 'Item "' . $identifier . '" not found.',
                previous: $e,
            );
        }
    }
}
