<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializer;

class CustomSerializer implements SerializerInterface
{
    public function __construct(
        private SymfonySerializer $serializer
    ) {}

    public function decode(array $encodedEnvelope): Envelope
    {
        $message = $this->serializer->deserialize(
            $encodedEnvelope['body'],
            $encodedEnvelope['headers']['type'] ?? 'App\Message\ImportIncomingMessage',
            'json'
        );

        return new Envelope($message);
    }

    public function encode(Envelope $envelope): array
    {
        $message = $envelope->getMessage();
        $data = $this->serializer->serialize($message, 'json');

        return [
            'body' => $data,
            'headers' => [
                'type' => get_class($message)
            ]
        ];
    }
}
