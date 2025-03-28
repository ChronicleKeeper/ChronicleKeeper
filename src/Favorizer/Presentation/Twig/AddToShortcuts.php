<?php

declare(strict_types=1);

namespace ChronicleKeeper\Favorizer\Presentation\Twig;

use ChronicleKeeper\Favorizer\Application\Command\StoreTargetBag;
use ChronicleKeeper\Favorizer\Application\Query\GetTargetBag;
use ChronicleKeeper\Favorizer\Domain\TargetBag;
use ChronicleKeeper\Favorizer\Domain\TargetFactory;
use ChronicleKeeper\Shared\Application\Query\QueryService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Favorizer:AddToShortcuts', 'components/favorizer/add_to_shortcuts.html.twig')]
class AddToShortcuts
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true)]
    public string $id;
    #[LiveProp(writable: true)]
    public string $type;
    #[LiveProp(writable: true)]
    public bool $asButton       = false;
    #[LiveProp(writable: true)]
    public string $extraClasses = '';

    private TargetBag $targetBag;

    public function __construct(
        private readonly QueryService $queryService,
        private readonly TargetFactory $targetFactory,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function exists(): bool
    {
        if (! isset($this->targetBag)) {
            $this->targetBag = $this->queryService->query(new GetTargetBag());
        }

        $favorizeTarget = $this->targetFactory->create($this->id, $this->type);

        return $this->targetBag->exists($favorizeTarget);
    }

    #[LiveListener('favorites_updated')]
    public function refresh(): void
    {
    }

    #[LiveAction]
    public function favorize(Request $request): void
    {
        if (! isset($this->targetBag)) {
            $this->targetBag = $this->queryService->query(new GetTargetBag());
        }

        $favorizeTarget = $this->targetFactory->create($this->id, $this->type);
        if ($this->targetBag->exists($favorizeTarget)) {
            // Remove the entry if it is already favorized
            $this->targetBag->remove($favorizeTarget);
        } else {
            // Add the entry if it is not favorized yet
            $this->targetBag[] = $favorizeTarget;
        }

        $this->bus->dispatch(new StoreTargetBag($this->targetBag));
        $this->emit('favorites_updated');
    }
}
