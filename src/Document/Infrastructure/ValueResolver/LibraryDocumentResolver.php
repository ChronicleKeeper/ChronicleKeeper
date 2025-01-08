<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\ValueResolver;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Throwable;

use function is_a;
use function is_string;

#[AutoconfigureTag('controller.argument_value_resolver', ['name' => 'library_document', 'priority' => 250])]
class LibraryDocumentResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @return iterable<Document> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if ($argumentType === null || ! is_a($argumentType, Document::class, true)) {
            return [];
        }

        $documentIdentifier = $request->get($argument->getName());
        if (! is_string($documentIdentifier) || ! Uuid::isValid($documentIdentifier)) {
            return [];
        }

        try {
            $document = $this->queryService->query(new GetDocument($documentIdentifier));
        } catch (Throwable $e) {
            throw new NotFoundHttpException(
                message: 'Document "' . $documentIdentifier . '" not found.',
                previous: $e,
            );
        }

        return [$document];
    }
}
