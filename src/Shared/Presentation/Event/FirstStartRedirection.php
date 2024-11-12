<?php

declare(strict_types=1);

namespace ChronicleKeeper\Shared\Presentation\Event;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use function in_array;

#[AsEventListener(RequestEvent::class)]
class FirstStartRedirection
{
    private const array IGNORE_ROUTES = ['first_start', 'application_import', 'settings', '_wdt', '_profiler'];

    public function __construct(
        private readonly SettingsHandler $settingsHandler,
        private readonly string $environment,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($this->environment === 'test' || $this->settingsHandler->get()->getApplication()->hasOpenAIApiKey()) {
            /**
             * The API key is already set, so we exit here as there is no welcome anymore.
             * Or we are in test environment which do not need to have a first start.
             */
            return;
        }

        $request      = $event->getRequest();
        $currentRoute = $request->attributes->get('_route');

        if ($currentRoute === null) {
            // The current route can not be identified, so we exit here.
            return;
        }

        if (in_array($currentRoute, self::IGNORE_ROUTES, true)) {
            // The current route is in the ignore list, so we exit here.
            return;
        }

        // Redirect to the first start page.
        $event->setResponse(new RedirectResponse('/first_start'));
    }
}
