<?php

namespace App\MessageHandler;

use App\Message\ImportCompanyMessage;
use App\Message\ImportIncomingMessage;
use App\Message\ImportPortMessage;
use App\Message\ImportVesselMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler(fromTransport: 'async_import')]
class RouterConsumerHandler
{
    public function __construct(
        private MessageBusInterface $bus,
        private LoggerInterface $logger
    ) {}

    public function __invoke(ImportIncomingMessage $message): void
    {
        try {
            $data = json_decode($message->getPayload(), true);

            if (!isset($data['data'])) {
                $this->logger->warning('Invalid message structure: missing data field');
                return;
            }

            $importData = $data['data'];

            if (isset($importData['vessels']) && is_array($importData['vessels'])) {
                foreach ($importData['vessels'] as $vesselData) {
                    $this->bus->dispatch(new ImportVesselMessage($vesselData));
                }
                $this->logger->info('Dispatched ' . count($importData['vessels']) . ' vessel messages');
            }

            if (isset($importData['ports']) && is_array($importData['ports'])) {
                foreach ($importData['ports'] as $portData) {
                    $this->bus->dispatch(new ImportPortMessage($portData));
                }
                $this->logger->info('Dispatched ' . count($importData['ports']) . ' port messages');
            }

            if (isset($importData['companies']) && is_array($importData['companies'])) {
                foreach ($importData['companies'] as $companyData) {
                    $this->bus->dispatch(new ImportCompanyMessage($companyData));
                }
                $this->logger->info('Dispatched ' . count($importData['companies']) . ' company messages');
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to process import message: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => substr($message->getPayload(), 0, 500)
            ]);

            throw $e;
        }
    }
}
