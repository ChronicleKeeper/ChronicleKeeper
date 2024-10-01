<?php

declare(strict_types=1);

namespace ChronicleKeeper\Chat\Infrastructure\ValueResolver;

use ChronicleKeeper\Chat\Application\Entity\Conversation;
use ChronicleKeeper\Chat\Infrastructure\Repository\ConversationFileStorage;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Exception\UnableToReadFile;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

use function is_a;
use function is_string;

#[AutoconfigureTag('controller.argument_value_resolver', ['name' => 'chat_conversation', 'priority' => 250])]
class ConversationValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ConversationFileStorage $conversationFileStorage,
    ) {
    }

    /** @return list<Conversation> */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if ($argumentType === null || ! is_a($argumentType, Conversation::class, true)) {
            return [];
        }

        $identifier = $request->attributes->get($argument->getName());
        if (! is_string($identifier) || ! Uuid::isValid($identifier)) {
            return [];
        }

        try {
            $conversation = $this->conversationFileStorage->load($identifier) ?? throw new NotFoundHttpException();
        } catch (UnableToReadFile) {
            throw new NotFoundHttpException('Conversation "' . $identifier . '" not found.');
        }

        return [$conversation];
    }
}
