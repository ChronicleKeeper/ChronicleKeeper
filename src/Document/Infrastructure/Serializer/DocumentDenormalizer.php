<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Infrastructure\Serializer;

use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Webmozart\Assert\Assert;

use function array_keys;
use function assert;
use function is_string;

#[Autoconfigure(lazy: true)]
class DocumentDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    /** @var array<string, Document> */
    private array $cachedEntries = [];

    private DenormalizerInterface $denormalizer;

    public function __construct(
        private readonly QueryService $queryService,
    ) {
    }

    public function setDenormalizer(DenormalizerInterface $denormalizer): void
    {
        $this->denormalizer = $denormalizer;
    }

    /** @inheritDoc */
    public function denormalize(mixed $data, string $type, string|null $format = null, array $context = []): Document
    {
        if (is_string($data)) {
            $document = $this->cachedEntries[$data] ?? $this->queryService->query(new GetDocument($data));
            assert($document instanceof Document);
            $this->cachedEntries[$document->getId()] = $document;

            return $document;
        }

        Assert::isArray($data);
        Assert::same(['id', 'title', 'content', 'directory', 'last_updated'], array_keys($data));
        Assert::uuid($data['id']);

        if (isset($this->cachedEntries[$data['id']])) {
            return $this->cachedEntries[$data['id']];
        }

        $directory = $this->denormalizer->denormalize(
            $data['directory'],
            Directory::class,
            $format,
            $context,
        );

        $document = new Document(
            $data['id'],
            $data['title'],
            $data['content'],
            $directory,
            new DateTimeImmutable($data['last_updated']),
        );

        $this->cachedEntries[$document->getId()] = $document;

        return $document;
    }

    /** @inheritDoc */
    public function supportsDenormalization(
        mixed $data,
        string $type,
        string|null $format = null,
        array $context = [],
    ): bool {
        return $type === Document::class;
    }

    /** @inheritDoc */
    public function getSupportedTypes(string|null $format): array
    {
        return [Document::class => true];
    }
}
