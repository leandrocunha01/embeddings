<?php

namespace App\Facades;
use App\Services\QdrantService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Qdrant\Qdrant getClient()
 */
class Qdrant extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QdrantService::class;
    }
}
