<?php

namespace App\MessageHandler;

use App\Entity\Vessel;
use App\Message\ImportVesselMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsMessageHandler(fromTransport: 'async_vessels')]
class VesselConsumerHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {}

    public function __invoke(ImportVesselMessage $message): void
    {
        $vesselData = $message->getVesselData();

        try {
            if (!isset($vesselData['imo'], $vesselData['name'], $vesselData['flag'])) {
                $this->logger->warning('Missing required fields for vessel', $vesselData);
                return;
            }

            $vessel = $this->entityManager
                ->getRepository(Vessel::class)
                ->findOneBy(['imo' => $vesselData['imo']]);

            if (!$vessel) {
                $vessel = new Vessel();
                $vessel->setImo($vesselData['imo']);
            }

            $vessel->setName($vesselData['name']);
            $vessel->setFlag($vesselData['flag']);

            $errors = $this->validator->validate($vessel);
            if (count($errors) > 0) {
                $this->logger->warning('Vessel validation failed', [
                    'imo' => $vesselData['imo'],
                    'errors' => (string) $errors
                ]);
                return;
            }

            $this->entityManager->persist($vessel);
            $this->entityManager->flush();

            $this->logger->info('Vessel saved successfully', ['imo' => $vesselData['imo']]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to save vessel: ' . $e->getMessage(), [
                'data' => $vesselData,
                'exception' => $e
            ]);

            throw $e;
        }
    }
}
