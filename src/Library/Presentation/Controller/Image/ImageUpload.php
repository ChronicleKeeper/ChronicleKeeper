<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Presentation\Controller\Image;

use ChronicleKeeper\Library\Application\Service\Image\Uploader;
use ChronicleKeeper\Library\Domain\Entity\Directory;
use ChronicleKeeper\Library\Presentation\Form\ImageUploadType;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
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

#[Route(
    '/library/directory/{directory}/upload_image',
    name: 'library_image_upload',
    requirements: ['directory' => Requirement::UUID],
)]
class ImageUpload extends AbstractController
{
    use HandleFlashMessages;

    public function __construct(
        private readonly Environment $environment,
        private readonly FormFactoryInterface $formFactory,
        private readonly Uploader $uploader,
        private readonly RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request, Directory $directory): Response
    {
        $form = $this->formFactory->create(ImageUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageUploaded = $form->get('image')->getData();
            assert($imageUploaded instanceof UploadedFile);

            $utilizePrompt = $form->get('utilize_prompt')->getData();
            assert($utilizePrompt instanceof SystemPrompt);

            $image = $this->uploader->upload($imageUploaded, $utilizePrompt, $directory);

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Das Bild mit dem Titel "' . $image->getTitle() . '" wurde erfolgreich hochgeladen. Bitte überprüfe die inhaltliche Beschreibung.',
            );

            return new RedirectResponse($this->router->generate('library_image_view', ['image' => $image->getId()]));
        }

        return new Response($this->environment->render(
            'library/image_upload.html.twig',
            ['directory' => $directory, 'form' => $form->createView()],
        ));
    }
}
