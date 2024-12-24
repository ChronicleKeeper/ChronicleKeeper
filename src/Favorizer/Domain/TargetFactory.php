<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Domain;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Favorizer\Domain\Exception\UnknownMedium;
use ChronicleKeeper\Favorizer\Domain\ValueObject\ChatConversationTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryDocumentTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\LibraryImageTarget;
use ChronicleKeeper\Favorizer\Domain\ValueObject\Target;
use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemImageRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;

use function Symfony\Component\String\u;

class TargetFactory
{
    public function __construct(
        private readonly FilesystemImageRepository $filesystemImageRepository,
        private readonly QueryService $queryService,
    ) {
    }

    public function create(string $id, string $type): Target
    {
        if ($type === Document::class) {
            return $this->createFromDocument($this->queryService->query(new GetDocument($id)));
        }

        if ($type === Image::class) {
            return $this->createFromImage(
                $this->filesystemImageRepository->findById($id) ?? throw UnknownMedium::notFound($id, $type),
            );
        }

        if ($type === Conversation::class) {
            return $this->createFromConversation($this->queryService->query(new FindConversationByIdParameters($id)));
        }

        throw UnknownMedium::forType($type);
    }

    private function createFromDocument(Document $document): Target
    {
        return new LibraryDocumentTarget($document->getId(), u($document->getTitle())->truncate(20, '…')->toString());
    }

    private function createFromImage(Image $image): Target
    {
        return new LibraryImageTarget($image->id, u($image->title)->truncate(20, '…')->toString());
    }

    private function createFromConversation(Conversation $conversation): Target
    {
        return new ChatConversationTarget($conversation->id, u($conversation->title)->truncate(20, '…')->toString());
    }
}
