<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\ValueResolver;

use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

use function is_a;
use function is_string;

#[AutoconfigureTag('controller.argument_value_resolver', ['name' => 'library_document', 'priority' => 250])]
class LibraryDocumentResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly FilesystemDocumentRepository $documentRepository,
    ) {
    }

    /** @return iterable<Document> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if ($argumentType === null || ! is_a($argumentType, Document::class, true)) {
            return [];
        }

        $documentIdentifier = $request->attributes->get($argument->getName());
        if (! is_string($documentIdentifier) || ! Uuid::isValid($documentIdentifier)) {
            return [];
        }

        try {
            $document = $this->documentRepository->findById($documentIdentifier);
        } catch (UnableToReadFile) {
            throw new NotFoundHttpException('Document "' . $documentIdentifier . '" not found.');
        }

        return [$document];
    }
}
