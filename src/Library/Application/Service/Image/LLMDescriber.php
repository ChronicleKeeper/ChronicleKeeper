<?php

declare(strict_types=1);

namespace ChronicleKeeper\Library\Application\Service\Image;

use ChronicleKeeper\Library\Domain\Entity\Image;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Content\Image as LLMImage;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use RuntimeException;

use function is_string;

use const PHP_EOL;

class LLMDescriber
{
    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly Chain $chain,
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

        $response = $this->chain->call(
            $messageBag,
            [
                'model' => Version::gpt4o()->name,
                'temperature' => $settings->getChatbotTuning()->getTemperature(),
            ],
        );

        if (! is_string($response)) {
            throw new RuntimeException('Image analyzing is expected to return string, given is an object.');
        }

        return $response;
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
