<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Twig;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Presentation\Form\GeneratorRequestType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function assert;

#[AsLiveComponent('ImageGenerator:Prompt', template: 'components/image_generator/generator_prompt.html.twig')]
class GeneratorPrompt extends AbstractController
{
    use HandleFlashMessages;
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[LiveProp(writable: true, useSerializerForHydration: true)]
    public GeneratorRequest $initialFormData;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(GeneratorRequestType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function store(Request $request): void
    {
        $this->submitForm();
        $generatorRequest = $this->getForm()->getData();
        $this->resetForm();
        assert($generatorRequest instanceof GeneratorRequest);

        $this->bus->dispatch(new StoreGeneratorRequest($generatorRequest));

        $this->addFlashMessage(
            $request,
            Alert::SUCCESS,
            'Der Auftrag wurde erfolgreich gespeichert.',
        );
    }
}
