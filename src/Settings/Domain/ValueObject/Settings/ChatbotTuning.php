<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Domain\ValueObject\Settings;

/**
 * @phpstan-type ChatbotTuningArray = array{
 *     temperature: float,
 *     images_max_distance: float,
 *     documents_max_distance: float
 * }
 */
class ChatbotTuning
{
    public function __construct(
        private readonly float $temperature = 0.7,
        private readonly float $imagesMaxDistance = 0.7,
        private readonly float $documentsMaxDistance = 0.85,
    ) {
    }

    /** @param ChatbotTuningArray $array */
    public static function fromArray(array $array): ChatbotTuning
    {
        return new ChatbotTuning(
            $array['temperature'],
            $array['images_max_distance'],
            $array['documents_max_distance'],
        );
    }

    /** @return ChatbotTuningArray */
    public function toArray(): array
    {
        return [
            'temperature' => $this->temperature,
            'images_max_distance' => $this->imagesMaxDistance,
            'documents_max_distance' => $this->documentsMaxDistance,
        ];
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getImagesMaxDistance(): float
    {
        return $this->imagesMaxDistance;
    }

    public function getDocumentsMaxDistance(): float
    {
        return $this->documentsMaxDistance;
    }
}
