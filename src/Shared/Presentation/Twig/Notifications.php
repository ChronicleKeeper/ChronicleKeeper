<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Twig;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Notifications', 'components/shared/notifications.html.twig')]
class Notifications
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    /** @var list<array{type: string, message: string}> */
    #[LiveProp(writable: true)]
    public array $notifications = [];

    #[LiveListener('notification')]
    public function addNotification(
        #[LiveArg]
        string $type,
        #[LiveArg]
        string $message,
    ): void {
        $this->notifications[] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}
