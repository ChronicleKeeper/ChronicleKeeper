<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

use function array_values;
use function assert;
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
final class LibraryImages
{
    /** @var array<string, Image> */
    private array $referencedImages = [];
    private float|null $maxDistance = null;

    public function __construct(
        private readonly FilesystemVectorImageRepository $vectorImageRepository,
        private readonly LLMChainFactory $embeddings,
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolUsageCollector $collector,
        private readonly RouterInterface $router,
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

        $vector = $this->embeddings->createPlatform()->request(
            model: $this->embeddings->createEmbeddingsModel(),
            input: $search,
        );
        assert($vector instanceof VectorResponse);
        $vector = $vector->getContent()[0];

        $results = $this->vectorImageRepository->findSimilar(
            $vector->getData(),
            maxDistance: $this->maxDistance,
            maxResults: $maxResults,
        );

        $this->referencedImages = [];
        if (count($results) === 0) {
            $this->collector->called(
                'library_images',
                [
                    'arguments' => ['search' => $search, 'maxDistance' => $this->maxDistance, 'maxResults' => $maxResults],
                    'responses' => [],
                ],
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
                ['image' => $libraryImage->id],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            $result .= '# Image Name: ' . $libraryImage->title . PHP_EOL;
            $result .= 'Direct Link to the image: ' . $imageUrl . PHP_EOL;
            $result .= 'The image is described as the following: ' . PHP_EOL;
            $result .= $image['vector']->content . PHP_EOL . PHP_EOL;

            $this->referencedImages[$libraryImage->id] = $libraryImage;

            $debugResponse[] = [
                'image' => $libraryImage->directory->flattenHierarchyTitle()
                    . '/' . $libraryImage->title,
                'distance' => $image['distance'],
                'content' => $image['vector']->content,
            ];
        }

        $this->collector->called(
            'library_images',
            [
                'arguments' => ['search' => $search, 'maxDistance' => $this->maxDistance, 'maxResults' => $maxResults],
                'responses' => $debugResponse,
            ],
        );

        return $result;
    }

    /** @return list<Image> */
    public function getReferencedImages(): array
    {
        $images                 = array_values($this->referencedImages);
        $this->referencedImages = [];

        return $images;
    }
}
