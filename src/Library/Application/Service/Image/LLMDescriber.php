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

        $response = $this->chain->call(
            new MessageBag(
                Message::forSystem($settings->getChatbotSystemPrompt()->getSystemPrompt()),
                Message::ofUser(
                    $this->getUserPromptText($imageToAnalyze),
                    new LLMImage($imageToAnalyze->getImageUrl()),
                ),
            ),
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
        Inhalt beschreiben. Ziehe Informationen der Funktion novalis_background andhand des Titels zu rate um das Bild
        noch besser zu bewerten.
        TEXT;
    }
}
