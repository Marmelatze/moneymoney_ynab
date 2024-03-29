<?php

namespace App\MoneyMoney;

use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Symfony\Component\Serializer\SerializerInterface;

class Serializer implements SerializerInterface
{
    private SymfonySerializer $serializer;

    public function __construct(array $normalizers, array $encoders)
    {
        $this->serializer = new SymfonySerializer($normalizers, $encoders);
    }

    public function serialize($data, $format, array $context = []): string
    {
        return $this->serializer->serialize($data, $format, $context);
    }

    public function deserialize($data, $type, $format, array $context = []): mixed
    {
        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        return $this->serializer->normalize($object, $format, $context);
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return $this->serializer->denormalize($data, $type, $format, $context);
    }
}
