<?php
namespace App\Ynab;

/**
 * Class YnabApiFactory
 */
class YnabApiFactory
{
    /**
     * @var Serializer
     */
    private Serializer $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function create(string $token): YnabApi
    {
        return new YnabApi($this->serializer, $token);
    }
}