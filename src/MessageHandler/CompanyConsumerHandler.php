<?php

namespace App\MessageHandler;

use App\Entity\Company;
use App\Message\ImportCompanyMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsMessageHandler(fromTransport: 'async_companies')]
class CompanyConsumerHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private LoggerInterface $logger
    ) {}

    public function __invoke(ImportCompanyMessage $message): void
    {
        $companyData = $message->getCompanyData();

        try {
            if (!isset($companyData['tax_id'], $companyData['name'])) {
                $this->logger->warning('Missing required fields for company', $companyData);
                return;
            }

            $company = $this->entityManager
                ->getRepository(Company::class)
                ->findOneBy(['taxId' => $companyData['tax_id']]);

            if (!$company) {
                $company = new Company();
                $company->setTaxId($companyData['tax_id']);
            }

            $company->setName($companyData['name']);

            $errors = $this->validator->validate($company);
            if (count($errors) > 0) {
                $this->logger->warning('Company validation failed', [
                    'tax_id' => $companyData['tax_id'],
                    'errors' => (string) $errors
                ]);
                return;
            }

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            $this->logger->info('Company saved successfully', ['tax_id' => $companyData['tax_id']]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to save company: ' . $e->getMessage(), [
                'data' => $companyData,
                'exception' => $e
            ]);

            throw $e;
        }
    }
}
