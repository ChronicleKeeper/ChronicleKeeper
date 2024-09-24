<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service;

class ImportSettings
{
    public function __construct(
        public readonly bool $overwriteSettings = true,
        public readonly bool $overwriteLibrary = false,
        public readonly bool $pruneLibrary = false,
    ) {
    }

    /** @param array{overwrite_settings?: bool, overwrite_library?: bool, prune_library?: bool} $settings */
    public static function fromArray(array $settings): ImportSettings
    {
        return new ImportSettings(
            $settings['overwrite_settings'] ?? true,
            $settings['overwrite_library'] ?? false,
            $settings['prune_library'] ?? false,
        );
    }
}
