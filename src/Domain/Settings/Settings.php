<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Domain\Settings;

use DZunke\NovDoc\Domain\SystemPrompt;

use function array_key_exists;
use function count;

/** @phpstan-type SettingsArray = array{
 *     currentDate: string,
 *     systemPrompt: string,
 *     max_document_responses: int,
 *     chatbot_name?: string,
 *     chatter_name?: string,
 * }
 */
class Settings
{
    public function __construct(
        public string $currentDate = '26. Arthan des 1262. Zyklus',
        public string $systemPrompt = SystemPrompt::GAMEMASTER,
        public int $maxDocumentResponses = 4,
        public string $chatbotName = 'Rostbart',
        public string $chatterName = 'Elias',
    ) {
    }

    /** @param SettingsArray $settingsArr */
    public static function fromArray(array $settingsArr): Settings
    {
        return new Settings(
            $settingsArr['currentDate'],
            $settingsArr['systemPrompt'],
            $settingsArr['max_document_responses'],
            $settingsArr['chatbot_name'] ?? 'Rostbart',
            $settingsArr['chatter_name'] ?? 'Elias',
        );
    }

    /**
     * @param mixed[] $array
     *
     * @phpstan-return ($array is SettingsArray ? true : false)
     */
    public static function isSettingsArr(array $array): bool
    {
        return count($array) >= 3
            && array_key_exists('currentDate', $array)
            && array_key_exists('systemPrompt', $array)
            && array_key_exists('max_document_responses', $array);
    }

    /** @return SettingsArray */
    public function toArray(): array
    {
        return [
            'currentDate' => $this->currentDate,
            'systemPrompt' => $this->systemPrompt,
            'max_document_responses' => $this->maxDocumentResponses,
            'chatbot_name' => $this->chatbotName,
            'chatter_name' => $this->chatterName,
        ];
    }
}
