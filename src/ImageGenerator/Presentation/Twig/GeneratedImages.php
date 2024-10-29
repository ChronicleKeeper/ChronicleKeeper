<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Presentation\Twig;

use ChronicleKeeper\ImageGenerator\Application\Command\StoreGeneratorResult;
use ChronicleKeeper\ImageGenerator\Application\Command\StoreImageToLibrary;
use ChronicleKeeper\ImageGenerator\Application\Query\FindAllImagesOfRequest;
use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorRequest;
use ChronicleKeeper\ImageGenerator\Domain\Entity\GeneratorResult;
use ChronicleKeeper\ImageGenerator\Domain\ValueObject\OptimizedPrompt;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

use function assert;

#[AsLiveComponent('ImageGenerator:Images', template: 'components/image_generator/generated_images.html.twig')]
class GeneratedImages extends AbstractController
{
    use HandleFlashMessages;
    use DefaultActionTrait;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly OpenAIGenerator $generator,
        private readonly QueryService $queryService,
    ) {
    }

    #[LiveProp(useSerializerForHydration: true)]
    public GeneratorRequest $generatorRequest;

    /** @return list<GeneratorResult> */
    public function getImages(): array
    {
        return $this->queryService->query(new FindAllImagesOfRequest($this->generatorRequest->id));
    }

    #[LiveAction]
    public function generate(): void
    {
        $prompt = $this->generatorRequest->prompt;
        assert($prompt instanceof OptimizedPrompt); // Ensured by request storage

        $this->bus->dispatch(new StoreGeneratorResult(
            $this->generatorRequest->id,
            $this->generator->generate($prompt->prompt),
        ));
    }

    #[LiveAction]
    public function toLibrary(
        #[LiveArg]
        string $requestId,
        #[LiveArg]
        string $imageId,
    ): void {
        $this->bus->dispatch(new StoreImageToLibrary($requestId, $imageId));
    }
}
