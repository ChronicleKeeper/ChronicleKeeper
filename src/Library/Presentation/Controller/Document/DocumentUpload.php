<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Document;

use ChronicleKeeper\Library\Application\Service\Document\Importer;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Presentation\Form\DocumentUploadType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

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
        private readonly Environment $environment,
        private readonly FormFactoryInterface $formFactory,
        private readonly RouterInterface $router,
        private readonly Importer $importer,
    ) {
    }

    public function __invoke(Request $request, Directory $directory): Response
    {
        $form = $this->formFactory->create(DocumentUploadType::class, ['optimize' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $documentUploaded = $form->get('document')->getData();
            assert($documentUploaded instanceof UploadedFile);

            $optimize = $form->get('optimize')->getData();
            assert(is_bool($optimize));

            $document = $this->importer->import($documentUploaded, $directory, $optimize);

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Das Dokument mit dem Titel "' . $document->title . '" wurde erfolgreich hochgeladen.',
            );

            return new RedirectResponse($this->router->generate(
                'library_document_view',
                ['document' => $document->id],
            ));
        }

        return new Response($this->environment->render(
            'library/document_upload.html.twig',
            ['directory' => $directory, 'form' => $form->createView()],
        ));
    }
}
