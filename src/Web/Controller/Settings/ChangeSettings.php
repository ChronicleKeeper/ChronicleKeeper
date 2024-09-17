<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Web\Controller\Settings;

use DZunke\NovDoc\Domain\Settings\SettingsHandler;
use DZunke\NovDoc\Web\FlashMessages\Alert;
use DZunke\NovDoc\Web\FlashMessages\HandleFlashMessages;
use DZunke\NovDoc\Web\Form\Settings\CalendarGeneralType;
use DZunke\NovDoc\Web\Form\Settings\CalendarHolidayType;
use DZunke\NovDoc\Web\Form\Settings\CalendarMoonType;
use DZunke\NovDoc\Web\Form\Settings\ChatbotGeneralType;
use DZunke\NovDoc\Web\Form\Settings\ChatbotSystemPromptType;
use DZunke\NovDoc\Web\Form\Settings\ChatbotTuningType;
use RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function is_string;

#[Route('/settings/{section}', name: 'settings', defaults: ['section' => 'chatbot_general'])]
class ChangeSettings
{
    use HandleFlashMessages;

    private const FORM_MAPPING = [
        'chatbot_general' => ChatbotGeneralType::class,
        'chatbot_system_prompt' => ChatbotSystemPromptType::class,
        'chatbot_tuning' => ChatbotTuningType::class,
        'calendar_general' => CalendarGeneralType::class,
        'calendar_holiday' => CalendarHolidayType::class,
        'calendar_moon' => CalendarMoonType::class,
    ];

    public function __construct(
        private Environment $environment,
        private SettingsHandler $settingsHandler,
        private RouterInterface $router,
        private FormFactoryInterface $formFactory,
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
