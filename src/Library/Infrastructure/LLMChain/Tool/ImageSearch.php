<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Chat\Domain\ValueObject\FunctionDebug;
use ChronicleKeeper\Chat\Domain\ValueObject\Reference;
use ChronicleKeeper\Chat\Infrastructure\LLMChain\RuntimeCollector;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\EmbeddingCalculator;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

use function count;

use const PHP_EOL;

#[AsTool(
    'library_images',
    description: <<<'TEXT'
    Delivers images and pictures from the role-playing game world. For additional background information, consider using
    the "library_documents" function. The images and pictures delivered here will help you describe locations,
    situations, and characters from the game universe.
    TEXT,
)]
class ImageSearch
{
    private float|null $maxDistance = null;

    public function __construct(
        private readonly FilesystemVectorImageRepository $vectorImageRepository,
        private readonly SettingsHandler $settingsHandler,
        private readonly RouterInterface $router,
        private readonly RuntimeCollector $runtimeCollector,
        private readonly EmbeddingCalculator $embeddingCalculator,
    ) {
    }

    public function setOneTimeMaxDistance(float|null $maxDistance): void
    {
        $this->maxDistance = $maxDistance;
    }

    /** @param string $search Contains the user's question or request related to the game world. */
    public function __invoke(string $search): string
    {
        $maxResults = $this->settingsHandler->get()->getChatbotGeneral()->getMaxImageResponses();
        $results    = $this->vectorImageRepository->findSimilar(
            $this->embeddingCalculator->getSingleEmbedding($search),
            maxDistance: $this->maxDistance,
            maxResults: $maxResults,
        );

        if (count($results) === 0) {
            $this->runtimeCollector->addFunctionDebug(
                new FunctionDebug(
                    tool: 'library_images',
                    arguments: ['search' => $search, 'maxDistance' => $this->maxDistance, 'maxResults' => $maxResults],
                    result: [],
                ),
            );

            return 'There are no matching images.';
        }

        $debugResponse = [];

        $result  = 'You will embed the found images to your responses as markdown only if the description of the image fits the question.' . PHP_EOL;
        $result .= 'I have found the following pictures and images that are associated to the question:' . PHP_EOL;
        foreach ($results as $image) {
            $libraryImage = $image['vector']->image;

            $imageUrl = $this->router->generate(
                'library_image_download',
                ['image' => $libraryImage->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            $result .= '# Image Name: ' . $libraryImage->getTitle() . PHP_EOL;
            $result .= 'Direct Link to the image: ' . $imageUrl . PHP_EOL;
            $result .= 'The image is described as the following: ' . PHP_EOL;
            $result .= $image['vector']->content . PHP_EOL . PHP_EOL;

            $this->runtimeCollector->addReference(Reference::forImage($libraryImage));

            $debugResponse[] = [
                'image' => $libraryImage->getDirectory()->flattenHierarchyTitle()
                    . '/' . $libraryImage->getTitle(),
                'distance' => $image['distance'],
                'content' => $image['vector']->content,
            ];
        }

        $this->runtimeCollector->addFunctionDebug(
            new FunctionDebug(
                tool: 'library_images',
                arguments: ['search' => $search, 'maxDistance' => $this->maxDistance, 'maxResults' => $maxResults],
                result: $debugResponse,
            ),
        );

        return $result;
    }
}