<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Image;

use ChronicleKeeper\Image\Domain\Entity\Image;
use ChronicleKeeper\Settings\Domain\Entity\SystemPrompt;
use ChronicleKeeper\Shared\Infrastructure\LLMChain\LLMChainFactory;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Model\Message\Content\Image as LLMImage;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use RuntimeException;

use const PHP_EOL;

class LLMDescriber
{
    public function __construct(
        private readonly LLMChainFactory $chain,
    ) {
    }

    public function copyImageWithGeneratedDescription(Image $image, SystemPrompt $utilizePrompt): Image
    {
        return Image::create(
            $image->getTitle(),
            $image->getMimeType(),
            $image->getEncodedImage(),
            $this->getDescription($image, $utilizePrompt),
            $image->getDirectory(),
        );
    }

    public function getDescription(Image $imageToAnalyze, SystemPrompt $systemPrompt): string
    {
        $messageBag = new MessageBag(Message::forSystem($systemPrompt->getContent()));

        $userPromptText  = $systemPrompt->getContent() . PHP_EOL;
        $userPromptText .= PHP_EOL . '### Some additional information about the image.' . PHP_EOL;
        $userPromptText .= 'Image Title: ' . $imageToAnalyze->getTitle() . PHP_EOL;

        $messageBag->add(Message::ofUser(
            $userPromptText,
            new LLMImage($imageToAnalyze->getImageUrl()),
        ));

        $response = $this->chain->create()->call(
            $messageBag,
            [
                'model' => GPT::GPT_4O,
                'temperature' => 0.75,
            ],
        );

        if (! $response instanceof TextResponse) {
            throw new RuntimeException('Image analyzing is expected to return string, given is an object.');
        }

        return $response->getContent();
    }
}
