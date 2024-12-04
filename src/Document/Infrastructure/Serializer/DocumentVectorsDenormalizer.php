<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\Serializer;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Domain\Entity\VectorDocument;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

use function array_keys;

#[Autoconfigure(lazy: true)]
class DocumentVectorsDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    /** @inheritDoc */
    public function denormalize(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): VectorDocument {
        Assert::isArray($data);
        Assert::same(['id', 'documentId', 'content', 'vectorContentHash', 'vector'], array_keys($data));

        $document = $this->queryService->query(new GetDocument($data['documentId']));

        $vectorDocument     = new VectorDocument(
            $document,
            $data['content'],
            $data['vectorContentHash'],
            $data['vector'],
        );
        $vectorDocument->id = $data['id'];

        return $vectorDocument;
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === VectorDocument::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [VectorDocument::class => true];
    }
}
