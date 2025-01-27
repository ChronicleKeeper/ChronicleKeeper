<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Document\Application\Query\GetDocument;
use ChronicleKeeper\Image\Application\Query\GetImage;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use ChronicleKeeper\World\Application\Command\StoreWorldItem;
use ChronicleKeeper\World\Domain\Entity\Item;
use ChronicleKeeper\World\Domain\ValueObject\ConversationReference;
use ChronicleKeeper\World\Domain\ValueObject\DocumentReference;
use ChronicleKeeper\World\Domain\ValueObject\ImageReference;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

use function str_replace;
use function str_starts_with;

#[Route(
    '/world/item/{id}/add_relations',
    name: 'world_item_add_relations',
    requirements: ['id' => Requirement::UUID],
)]
final class WorldItemMediaAdd extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, Item $item): Response
    {
        if ($request->isMethod('POST')) {
            $this->addMediaReferencesToItem($request->get('media', []), $item);

            $this->bus->dispatch(new StoreWorldItem($item));

            $this->addFlashMessage($request, Alert::SUCCESS, 'Die Medien wurden erfolgreich verlinkt.');

            return $this->redirectToRoute('world_item_view', ['id' => $item->getId()]);
        }

        return $this->render(
            'world/item_media_add.html.twig',
            ['item' => $item],
        );
    }

    /** @param array<string> $mediaReferences */
    private function addMediaReferencesToItem(array $mediaReferences, Item $item): void
    {
        foreach ($mediaReferences as $media) {
            if (str_starts_with($media, 'image_')) {
                $identifier = str_replace('image_', '', $media);
                $image      = $this->queryService->query(new GetImage($identifier));
                $item->addMediaReference(new ImageReference($item, $image));

                continue;
            }

            if (str_starts_with($media, 'document_')) {
                $identifier = str_replace('document_', '', $media);
                $document   = $this->queryService->query(new GetDocument($identifier));
                $item->addMediaReference(new DocumentReference($item, $document));

                continue;
            }

            if (! str_starts_with($media, 'conversation_')) {
                throw new RuntimeException('Unknown media type.');
            }

            $identifier   = str_replace('conversation_', '', $media);
            $conversation = $this->queryService->query(new FindConversationByIdParameters($identifier));
            $item->addMediaReference(new ConversationReference($item, $conversation));
        }
    }
}
