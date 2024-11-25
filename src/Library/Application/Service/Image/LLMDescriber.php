<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Image;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Settings\Application\SettingsHandler;
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
        private readonly SettingsHandler $settingsHandler,
        private readonly LLMChainFactory $chain,
    ) {
    }

    public function getDescription(Image $imageToAnalyze): string
    {
        $settings = $this->settingsHandler->get();

        $messageBag = new MessageBag(Message::forSystem($settings->getChatbotSystemPrompt()->getSystemPrompt()));

        $userPromptText = $this->getUserPromptText($imageToAnalyze);
        if ($imageToAnalyze->description !== '') {
            /**
             * If the image already has a description we will also give it as context to the
             * message, so it will taken as context
             */
            $userPromptText .= PHP_EOL . '### Some additional information about the image.' . PHP_EOL;
            $userPromptText .= $imageToAnalyze->description;
        }

        $messageBag[] = Message::ofUser(
            $userPromptText,
            new LLMImage($imageToAnalyze->getImageUrl()),
        );

        $response = $this->chain->create()->call(
            $messageBag,
            [
                'model' => GPT::GPT_4O,
                'temperature' => $settings->getChatbotTuning()->getTemperature(),
            ],
        );

        if (! $response instanceof TextResponse) {
            throw new RuntimeException('Image analyzing is expected to return string, given is an object.');
        }

        return $response->getContent();
    }

    private function getUserPromptText(Image $image): string
    {
        return <<<TEXT
        Mit der Information, dass das Bild "{$image->title}" heißt, beschreibe bis ins kleinste Detail jede relevante
        Information aus diesem Bild. Füge keine Links ein. Schlussfolgerungen möchtest du nicht machen, sondern nur den
        Inhalt beschreiben. Ziehe Informationen der Funktion library_documents andhand des Titels zu rate um das Bild
        noch besser zu bewerten.
        TEXT;
    }
}
