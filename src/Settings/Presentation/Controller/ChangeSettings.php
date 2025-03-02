<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Presentation\Controller;

use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Settings\Presentation\Form\ApplicationType;
use ChronicleKeeper\Settings\Presentation\Form\ChatbotGeneralType;
use ChronicleKeeper\Settings\Presentation\Form\ChatbotTuningType;
use ChronicleKeeper\Shared\Presentation\FlashMessages\Alert;
use ChronicleKeeper\Shared\Presentation\FlashMessages\HandleFlashMessages;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function is_string;

#[Route('/settings/{section}', name: 'settings', defaults: ['section' => 'chatbot_general'], priority: 0)]
class ChangeSettings extends AbstractController
{
    use HandleFlashMessages;

    private const array FORM_MAPPING = [
        'application' => ApplicationType::class,
        'chatbot_general' => ChatbotGeneralType::class,
        'chatbot_tuning' => ChatbotTuningType::class,
    ];

    public function __construct(
        private readonly Environment $environment,
        private readonly SettingsHandler $settingsHandler,
        private readonly RouterInterface $router,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function __invoke(Request $request, string $section): Response
    {
        $settings = $this->settingsHandler->get();

        $form = $this->formFactory->create(
            self::FORM_MAPPING[$section],
            $settings,
            ['action' => $this->router->generate('settings', ['section' => $section])],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->settingsHandler->store();

            $this->addFlashMessage($request, Alert::SUCCESS, 'Die Einstellungen wurden gespeichert.');

            return new RedirectResponse($this->router->generate('settings', ['section' => $section]));
        }

        $template = $form->getConfig()->getOption('twig_view');
        if (! is_string($template)) {
            throw new RuntimeException('The form does not have a corresponding "twig_view" configured.');
        }

        return new Response($this->environment->render(
            $template,
            ['settings' => $settings, 'form' => $form->createView()],
        ));
    }
}
