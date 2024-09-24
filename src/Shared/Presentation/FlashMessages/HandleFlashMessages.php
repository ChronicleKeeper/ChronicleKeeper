<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\FlashMessages;

use Symfony\Component\HttpFoundation\Request;

use function is_array;

trait HandleFlashMessages
{
    private function addFlashMessage(Request $request, Alert $type, string $message): void
    {
        $existingFlashMessages = $request->getSession()->get('flash_messages');
        if (! is_array($existingFlashMessages)) {
            $existingFlashMessages = [];
        }

        $existingFlashMessages[] = new FlashMessage($type, $message);

        $request->getSession()->set('flash_messages', $existingFlashMessages);
    }
}
