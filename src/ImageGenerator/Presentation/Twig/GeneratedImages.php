<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Twig;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorRequest;
use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('ImageGenerator:Images', template: 'components/image_generator/generated_images.html.twig')]
class GeneratedImages extends AbstractController
{
    use HandleFlashMessages;
    use DefaultActionTrait;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly OpenAIGenerator $generator,
    ) {
    }

    #[LiveProp(useSerializerForHydration: true)]
    public GeneratorRequest $generatorRequest;

    public function getImages(): array
    {
        return $this->generatorRequest->getArrayCopy();
    }

    #[LiveAction]
    public function generate(): void
    {
        $this->generatorRequest[] = $this->generator->generate($this->generatorRequest->prompt->prompt);
        $this->bus->dispatch(new StoreGeneratorRequest($this->generatorRequest));
    }

    #[LiveAction]
    public function toLibrary(): void
    {
        sleep(5);
    }
}
