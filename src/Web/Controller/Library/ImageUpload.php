<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Library;

use DZunke\NovDoc\Domain\Document\Directory;
use DZunke\NovDoc\Domain\Library\Image\Uploader;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use DZunke\NovDoc\Web\Form\Library\ImageUploadType;
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
class ImageUpload
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

            $image = $this->uploader->upload($imageUploaded, $directory);

            $this->addFlashMessage(
                $request,
                Alert::SUCCESS,
                'Das Bild mit dem Titel "' . $image->title . '" wurde erfolgreich hochgeladen.',
            );

            return new RedirectResponse($this->router->generate('library', ['directory' => $directory->id]));
        }

        return new Response($this->environment->render(
            'library/image_upload.html.twig',
            ['directory' => $directory, 'form' => $form->createView()],
        ));
    }
}