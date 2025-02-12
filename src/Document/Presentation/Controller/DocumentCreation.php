<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Presentation\Controller;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\Document\Application\Command\StoreDocument;
use ChronicleKeeper\Document\Domain\Entity\Document;
use ChronicleKeeper\Document\Presentation\Form\DocumentType;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use ChronicleKeeper\Shared\Presentation\Twig\Form\HandleFooterButtonGroup;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

use function array_filter;
use function assert;
use function reset;

#[Route(
    '/library/directory/{directory}/create_document',
    name: 'library_document_create',
    requirements: ['directory' => Requirement::UUID],
    defaults: ['directory' => RootDirectory::ID],
)]
class DocumentCreation extends AbstractController
{
    use HandleFlashMessages;
    use HandleFooterButtonGroup;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(Request $request, Directory $directory): Response
    {
        $form = $this->createForm(DocumentType::class, $this->getDocumentFromChatMessagesBag($request));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->getData();
            assert($document instanceof Document);

            $this->bus->dispatch(new StoreDocument($document));
            $this->addFlashMessage($request, Alert::SUCCESS, 'Das Dokument wurde erfolgreich erstellt.');

            return $this->redirectFromFooter(
                $request,
                $this->generateUrl('library', ['directory' => $document->getDirectory()->getId()]),
                $this->generateUrl('library_document_view', ['document' => $document->getId()]),
            );
        }

        return $this->render(
            'document/document_create.html.twig',
            ['form' => $form->createView()],
        );
    }

    private function getDocumentFromChatMessagesBag(Request $request): Document|null
    {
        if (
            ! $request->isMethod(Request::METHOD_GET)
            || ! $request->query->has('conversation')
            || ! $request->query->has('conversation_message')
        ) {
            return null;
        }

        $conversation = $this->queryService->query(
            new FindConversationByIdParameters((string) $request->query->get('conversation')),
        );

        if (! $conversation instanceof Conversation) {
            $conversation = $this->queryService->query(new GetTemporaryConversationParameters());
        }

        $chatMessageToTemplateFrom = (string) $request->query->get('conversation_message');

        $latestMessages            = $conversation->getMessages()->getArrayCopy();
        $foundMessagesByIdentifier = array_filter(
            $latestMessages,
            static fn (ExtendedMessage $message) => $message->id === $chatMessageToTemplateFrom,
        );

        $foundMessageByIdentifier = reset($foundMessagesByIdentifier);

        if (! $foundMessageByIdentifier instanceof ExtendedMessage) {
            return null;
        }

        if (! $foundMessageByIdentifier->message instanceof AssistantMessage) {
            return null;
        }

        return Document::create(
            $conversation->getTitle(),
            (string) $foundMessageByIdentifier->message->content,
        );
    }
}
