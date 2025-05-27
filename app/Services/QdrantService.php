<?php

namespace App\Services;

use Qdrant\Config;
use Qdrant\Http\Builder;
use Qdrant\Qdrant;

class QdrantService
{
    protected $client;

    /**
     * @param $client
     */
    public function __construct()
    {
        $config = new Config(config('qdrant.url'));
        $config->setApiKey(config('qdrant.api_key'));

        $transport = (new Builder())->build($config);
        $this->client = new Qdrant($transport);
    }

    public function getClient(): Qdrant
    {
        return $this->client;
    }

}
