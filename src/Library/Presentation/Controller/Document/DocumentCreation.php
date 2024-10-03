<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Document;

use ChronicleKeeper\Chat\Application\Query\FindConversationByIdParameters;
use ChronicleKeeper\Chat\Application\Query\GetTemporaryConversationParameters;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\ExtendedMessage;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Domain\Entity\Document;
use ChronicleKeeper\Library\Domain\RootDirectory;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDirectoryRepository;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemDocumentRepository;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use PhpLlm\LlmChain\Message\AssistantMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function array_filter;
use function is_string;
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

    public function __construct(
        private readonly Environment $environment,
        private readonly RouterInterface $router,
        private readonly FilesystemDocumentRepository $documentRepository,
        private readonly FilesystemDirectoryRepository $directoryRepository,
        private readonly QueryService $queryService,
    ) {
    }

    public function __invoke(Request $request, Directory $directory): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $title   = $request->get('title', '');
            $content = $request->get('content', '');

            $storeDirectory = $request->getPayload()->get('directory');

            if (is_string($storeDirectory)) {
                $storeDirectory = $this->directoryRepository->findById($storeDirectory) ?? $directory;
            } else {
                $storeDirectory = $directory;
            }

            if (is_string($title) && is_string($content)) {
                $document            = new Document($title, $content);
                $document->directory = $storeDirectory;

                $this->documentRepository->store($document);

                $this->addFlashMessage(
                    $request,
                    Alert::SUCCESS,
                    'Das Dokument wurde erstellt, damit es in der Suche aktiv ist kannst du den Suchindex aktualisieren.',
                );

                return new RedirectResponse($this->router->generate('library', ['directory' => $directory->id]));
            }
        }

        return new Response($this->environment->render(
            'library/document_create.html.twig',
            ['directory' => $directory, 'template_content' => $this->getTemplateContentFromChatMessagesBag($request)],
        ));
    }

    private function getTemplateContentFromChatMessagesBag(Request $request): string
    {
        if (
            ! $request->isMethod(Request::METHOD_GET)
            || ! $request->query->has('conversation')
            || ! $request->query->has('conversation_message')
        ) {
            return '';
        }

        $conversation = $this->queryService->query(
            new FindConversationByIdParameters((string) $request->query->get('conversation')),
        );
        if ($conversation === null) {
            $conversation = $this->queryService->query(new GetTemporaryConversationParameters());
        }

        $chatMessageToTemplateFrom = (string) $request->query->get('conversation_message');

        $latestMessages            = $conversation->messages->getArrayCopy();
        $foundMessagesByIdentifier = array_filter(
            $latestMessages,
            static fn (ExtendedMessage $message) => $message->id === $chatMessageToTemplateFrom,
        );

        $foundMessageByIdentifier = reset($foundMessagesByIdentifier);

        if (! $foundMessageByIdentifier instanceof ExtendedMessage) {
            return '';
        }

        if (! $foundMessageByIdentifier->message instanceof AssistantMessage) {
            return '';
        }

        return (string) $foundMessageByIdentifier->message->content;
    }
}
