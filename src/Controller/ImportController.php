<?php

namespace App\Controller;

use App\Message\ImportIncomingMessage;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ImportController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $bus,
        private LoggerInterface $logger
    ) {}

    #[Route('/api/import', name: 'api_import', methods: ['POST'])]
    public function import(Request $request): JsonResponse
    {
        $messageId = Uuid::v4()->toString();

        try {
            $content = $request->getContent();

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Invalid JSON', ['error' => json_last_error_msg()]);
                return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            if (!isset($data['data'])) {
                $this->logger->error('Missing data field');
                return $this->json([
                    'error' => 'Missing data field',
                    'message_id' => $messageId
                ], Response::HTTP_BAD_REQUEST);
            }

            $message = new ImportIncomingMessage($content);
            $this->bus->dispatch($message);

            $this->logger->info('Message dispatched', ['message_id' => $messageId]);

            return $this->json([
                'status' => 'accepted',
                'message_id' => $messageId
            ], Response::HTTP_ACCEPTED);

        } catch (\Exception $e) {
            $this->logger->error('Failed to process request', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->json([
                'error' => 'Failed to process request',
                'message_id' => $messageId
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
