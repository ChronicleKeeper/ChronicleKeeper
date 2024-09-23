<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Library\Infrastructure\LLMChain\Tool;

use DZunke\NovDoc\Library\Domain\Entity\Image;
use DZunke\NovDoc\Library\Infrastructure\Repository\FilesystemVectorImageRepository;
use DZunke\NovDoc\Settings\Application\SettingsHandler;
use DZunke\NovDoc\Shared\Infrastructure\LLMChain\ToolUsageCollector;
use PhpLlm\LlmChain\EmbeddingModel;
use PhpLlm\LlmChain\ToolBox\AsTool;

use function count;

use const PHP_EOL;

#[AsTool(
    'novalis_images',
    description: <<<'TEXT'
    Delivers images and pictures from the world of Novalis. For additional background information to the background
    utilize function "novalis_background". The images and pictures delivered here will help you describing locations,
    situations and persons from the world of novalis. Feel free to utilize those information if someone is asking for
    information about locations, situations or persons.
    TEXT,
)]
final class NovalisImages
{
    /** @var list<Image> */
    private array $referencedImages = [];

    public function __construct(
        private readonly FilesystemVectorImageRepository $vectorImageRepository,
        private readonly EmbeddingModel $embeddings,
        private readonly SettingsHandler $settingsHandler,
        private readonly ToolUsageCollector $collector,
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

        $result  = 'You will just output text from the following information. Do not display an image.' . PHP_EOL;
        $result .= 'I have found the following pictures and images that are associated to the world of Novalis:' . PHP_EOL;
        foreach ($results as $image) {
            $result .= '# Image Name: ' . $image->image->title . PHP_EOL;
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
