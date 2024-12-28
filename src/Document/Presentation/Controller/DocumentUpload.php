<?php

declare(strict_types=1);

namespace ChronicleKeeper\Document\Presentation\Controller;

use ChronicleKeeper\Document\Application\Service\Exception\PDFHasEmptyContent;
use ChronicleKeeper\Document\Application\Service\Importer;
use ChronicleKeeper\Document\Presentation\Form\DocumentUploadType;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

use function assert;
use function is_bool;

#[Route(
    '/library/directory/{directory}/upload_document',
    name: 'library_document_upload',
    requirements: ['directory' => Requirement::UUID],
)]
class DocumentUpload extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly Importer $importer,
    ) {
    }

    public function __invoke(Request $request, Directory $directory): Response
    {
        $form = $this->formFactory->create(DocumentUploadType::class, ['optimize' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $documentUploaded = $form->get('document')->getData();
                assert($documentUploaded instanceof UploadedFile);

                $optimize = $form->get('optimize')->getData();
                assert(is_bool($optimize));

                $systemPrompt = $form->get('utilize_prompt')->getData();
                assert($systemPrompt instanceof SystemPrompt);

                $document = $this->importer->import($documentUploaded, $directory, $optimize, $systemPrompt);

                $this->addFlashMessage(
                    $request,
                    Alert::SUCCESS,
                    'Das Dokument mit dem Titel "' . $document->getTitle() . '" wurde erfolgreich hochgeladen. Bitte überprüfe den Text nach einer Optimierung.',
                );

                return $this->redirectToRoute('library_document_view', ['document' => $document->getId()]);
            } catch (PDFHasEmptyContent) {
                $this->addFlashMessage(
                    $request,
                    Alert::WARNING,
                    'Der Inhalt der PDF ist leider nicht lesbar. Bitte versuche es mit einem anderen Dokument.',
                );
            }
        }

        return $this->render(
            'document/document_upload.html.twig',
            ['directory' => $directory, 'form' => $form->createView()],
        );
    }
}
