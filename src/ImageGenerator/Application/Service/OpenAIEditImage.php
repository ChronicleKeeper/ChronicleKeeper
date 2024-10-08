<?php

declare(strict_types=1);

namespace ChronicleKeeper\ImageGenerator\Application\Service;

use ChronicleKeeper\Chat\Application\Service\ChatMessageExecution;
use ChronicleKeeper\Chat\Domain\Entity\Conversation;
use ChronicleKeeper\Chat\Domain\Entity\ExtendedMessage;
use ChronicleKeeper\ImageGenerator\Application\Service\OpenAIGenerator\ResponseImage;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\PathRegistry;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\OpenAI\Platform;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function array_key_last;
use function assert;
use function base64_decode;
use function base64_encode;
use function explode;
use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecopy;
use function imagecreatefromstring;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefill;
use function imagefilledrectangle;
use function imagepng;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function mb_substr;
use function ob_get_clean;
use function ob_start;
use function sprintf;

class OpenAIEditImage
{
    public function __construct(
        private readonly Platform $platform,
        private readonly ChatMessageExecution $chatMessageExecution,
        private readonly PathRegistry $pathRegistry,
        private readonly FileAccess $fileAccess,
        private readonly SerializerInterface $serializer,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    private function editImageRequest(string $prompt): Conversation
    {
        $conversation = $this->getConversation();

        $messages     = $conversation->messages->getLLMChainMessages()->getArrayCopy();
        $imageMessage = $messages[array_key_last($messages)];
        assert($imageMessage instanceof AssistantMessage);

        $parts = explode(',', $imageMessage->content)[1];
        $parts = mb_substr($parts, 0, -1);
        $parts = $this->convertImageToRGBA($parts);

        $body = new FormDataPart([
            'prompt' => $prompt,
            'model' => 'dall-e-2', // Only available in Dall-E 2
            'response_format' => 'b64_json',
            'image' => new DataPart(base64_decode($parts), 'image.png', 'image/png'),
            'mask' => new DataPart(base64_decode($this->generateRGBAMask($parts)), 'image_mask.png', 'image/png'),
        ]);

        $url = sprintf('https://api.openai.com/v1/%s', 'images/edits');

        $response = $this->httpClient->request('POST', $url, [
            'auth_bearer' => '', // Open AI Key ... Removed for Archive :)
            'headers' => $body->getPreparedHeaders()->toArray(),
            'body' => $body->bodyToIterable(),
        ]);
        $response = $response->toArray();

        $conversation->messages[] = new ExtendedMessage(Message::ofUser($prompt));
        foreach ($response['data'] as $responseImage) {
            $image = new ResponseImage($prompt, 'image/png', $responseImage['b64_json']);

            $responseMessage          = new ExtendedMessage(Message::ofAssistant('![Generated Image](' . $image->getImageUrl() . ')'));
            $conversation->messages[] = $responseMessage;

            break;
        }

        return $conversation;
    }

    private function convertImageToRGBA(string $base64Image): string
    {
        // Decode the base64 image
        $imageData   = base64_decode($base64Image);
        $sourceImage = imagecreatefromstring($imageData);

        // Create a true color image with alpha channel
        $width     = imagesx($sourceImage);
        $height    = imagesy($sourceImage);
        $rgbaImage = imagecreatetruecolor($width, $height);
        imagealphablending($rgbaImage, false);
        imagesavealpha($rgbaImage, true);

        // Copy the source image to the RGBA image
        imagecopy($rgbaImage, $sourceImage, 0, 0, 0, 0, $width, $height);

        // Capture the output
        ob_start();
        imagepng($rgbaImage);
        $rgbaImageData = ob_get_clean();

        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($rgbaImage);

        // Return the base64 encoded RGBA image
        return base64_encode($rgbaImageData);
    }

    private function generateRGBAMask(string $base64Image): string
    {
        // Decode the base64 image
        $imageData   = base64_decode($base64Image);
        $sourceImage = imagecreatefromstring($imageData);

        // Create a true color image with alpha channel
        $width     = imagesx($sourceImage);
        $height    = imagesy($sourceImage);
        $maskImage = imagecreatetruecolor($width, $height);
        imagealphablending($maskImage, false);
        imagesavealpha($maskImage, true);

        // Fill the mask with transparent color
        $transparent = imagecolorallocatealpha($maskImage, 0, 0, 0, 127);
        imagefill($maskImage, 0, 0, $transparent);

        // Draw the mask (example: a simple rectangle)
        $maskColor = imagecolorallocatealpha($maskImage, 255, 255, 255, 0);
        imagefilledrectangle($maskImage, 10, 10, $width - 10, $height - 10, $maskColor);

        // Capture the output
        ob_start();
        imagepng($maskImage);
        $maskImageData = ob_get_clean();

        // Free up memory
        imagedestroy($sourceImage);
        imagedestroy($maskImage);

        // Return the base64 encoded RGBA mask
        return base64_encode($maskImageData);
    }
}
