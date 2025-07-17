<?php

declare(strict_types=1);

namespace ChronicleKeeper\Test\Chat\Application\Service;

use ChronicleKeeper\Chat\Application\Service\ExtendedMessageBagToViewConverter;
use ChronicleKeeper\Settings\Application\SettingsHandler;
use ChronicleKeeper\Test\Chat\Domain\Entity\ExtendedMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\AssistantMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\SystemMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\LLMChain\UserMessageBuilder;
use ChronicleKeeper\Test\Chat\Domain\Entity\MessageBagBuilder;
use ChronicleKeeper\Test\Settings\Domain\ValueObject\SettingsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_column;

#[CoversClass(ExtendedMessageBagToViewConverter::class)]
#[Small]
final class ExtendedMessageBagToViewConverterTest extends TestCase
{
    #[Test]
    public function itCanConvertAMessageBagToViewData(): void
    {
        $messageBag               = (new MessageBagBuilder())
            ->withMessages(
                (new ExtendedMessageBuilder())
                    ->withMessage((new SystemMessageBuilder())->build())
                    ->build(),
                $userMessage      = (new ExtendedMessageBuilder())
                    ->withMessage((new UserMessageBuilder())->withContent('My user message')->build())
                    ->build(),
                $assistantMessage = (new ExtendedMessageBuilder())
                    ->withMessage((new AssistantMessageBuilder())->withContent('My assistant response')->build())
                    ->build(),
            )
            ->build();

        $settingsHandler = self::createStub(SettingsHandler::class);
        $settingsHandler->method('get')->willReturn((new SettingsBuilder())->build());

        $converter = new ExtendedMessageBagToViewConverter($settingsHandler);
        $viewData  = $converter->convert($messageBag);

        self::assertCount(2, $viewData);
        self::assertSame(['Der Unbekannte', 'Chronicle Keeper'], array_column($viewData, 'role'));
        self::assertSame(['My user message', 'My assistant response'], array_column($viewData, 'message'));

        $extendedMessages = array_column($viewData, 'extended');
        self::assertSame([$userMessage, $assistantMessage], $extendedMessages);
    }
}
