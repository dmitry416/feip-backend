<?php

namespace App\MessageHandler;

use App\Entity\Port;
use App\Message\ImportPortMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsMessageHandler(fromTransport: 'async_ports')]
class PortConsumerHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {}

    public function __invoke(ImportPortMessage $message): void
    {
        $portData = $message->getPortData();

        try {
            if (!isset($portData['code'], $portData['name'], $portData['country'])) {
                $this->logger->warning('Missing required fields for port', $portData);
                return;
            }

            $port = $this->entityManager
                ->getRepository(Port::class)
                ->findOneBy(['code' => $portData['code']]);

            if (!$port) {
                $port = new Port();
                $port->setCode($portData['code']);
            }

            $port->setName($portData['name']);
            $port->setCountry($portData['country']);

            $errors = $this->validator->validate($port);
            if (count($errors) > 0) {
                $this->logger->warning('Port validation failed', [
                    'code' => $portData['code'],
                    'errors' => (string) $errors
                ]);
                return;
            }

            $this->entityManager->persist($port);
            $this->entityManager->flush();

            $this->logger->info('Port saved successfully', ['code' => $portData['code']]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to save port: ' . $e->getMessage(), [
                'data' => $portData,
                'exception' => $e
            ]);

            throw $e;
        }
    }
}
