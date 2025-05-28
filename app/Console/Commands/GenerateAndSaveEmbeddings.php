<?php

namespace App\Console\Commands;

use App\Facades\Qdrant;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\VectorStruct;

class GenerateAndSaveEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-and-save-embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and save embeddings, on Qdrant DB.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = Qdrant::getClient();
        #$createCollection = new CreateCollection();
        #$createCollection->addVector(new VectorParams(384, VectorParams::DISTANCE_COSINE), 'product');
        #$response = $client->collections('products')->create($createCollection);
        #dd($response);
        Product::query()->chunk(100, function ($products) use ($client) {
            $points = new PointsStruct();
            foreach ($products as $product) {
                $response = Http::post('http://127.0.0.1:8000/embed',
                    ['text' => "passage: $product->description"]
                );

                $data = $response->object();

                $points->addPoint(
                    new PointStruct(
                        (int)$product->id,
                        new VectorStruct($data->embedding, 'product'),
                        [
                            'id' => $product->id,
                            'name' => $product->name,
                            'description' => $product->description,
                        ]
                    )
                );
            }
            try {
                $client->collections('products')->points()->upsert($points);
            }catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        );
    }
}
