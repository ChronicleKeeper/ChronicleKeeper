<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Infrastructure\LLMChain\Tool;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\EmbeddingModel;
use PhpLlm\LlmChain\ToolBox\AsTool;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

use function count;

use const PHP_EOL;

#[AsTool(
    'novalis_images',
    description: <<<'TEXT'
    Delivers images and pictures from the world of Novalis. For additional background information to the background
    utilize function "novalis_background". The images and pictures delivered here will help you describing locations,
    situations and persons from the world of novalis. Feel free to utilize those information if someone is asking for
    information about locations, situations or persons. Found images are embedded as markdown by you.
    TEXT,
)]
final class LibraryImages
{
    /** @var list<Image> */
    private array $referencedImages = [];

    public function __construct(
        private readonly FilesystemVectorImageRepository $vectorImageRepository,
        private readonly EmbeddingModel $embeddings,
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolUsageCollector $collector,
        private readonly RouterInterface $router,
    ) {
    }

    /** @param string $search Contains the question or message the user has sent in reference to novalis. */
    public function __invoke(string $search): string
    {
        $this->collector->called('novalis_images', ['search' => $search]);

        $vector  = $this->embeddings->create($search);
        $results = $this->vectorImageRepository->findSimilar(
            $vector->getData(),
            maxResults: $this->settingsHandler->get()->getChatbotGeneral()->getMaxImageResponses(),
        );

        $this->referencedImages = [];
        if (count($results) === 0) {
            return 'There are no matching images.';
        }

        $result  = 'You will embed the found images to your responses as markdown only if the description of the image fits the question.' . PHP_EOL;
        $result .= 'I have found the following pictures and images that are associated to the world of Novalis:' . PHP_EOL;
        foreach ($results as $image) {
            $imageUrl = $this->router->generate(
                'library_image_download',
                ['image' => $image->image->id],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            $result .= '# Image Name: ' . $image->image->title . PHP_EOL;
            $result .= 'Direct Link to the image: ' . $imageUrl . PHP_EOL;
            $result .= 'The image is described as the following: ' . PHP_EOL;
            $result .= $image->image->description;

            $this->referencedImages[] = $image->image;
        }

        return $result;
    }

    /** @return list<Image> */
    public function getReferencedImages(): array
    {
        $images                 = $this->referencedImages;
        $this->referencedImages = [];

        return $images;
    }
}
