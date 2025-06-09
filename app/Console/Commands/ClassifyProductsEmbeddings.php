<?php

namespace App\Console\Commands;

use App\Facades\Qdrant;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\VectorStruct;

class ClassifyProductsEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:classify-products-embeddings {option}';

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

        $argument = $this->argument('option');

        if ($argument == 'create-collection') {
            $this->createCollectionOnQdrant($client);
            return;
        }

        if ($argument == 'create-categories') {
            $this->buildEmbeddingsAndSaveOnQdrant($client);
            return;
        }

        $this->error('Use os seguintes opções: create-collection ou create-categories.');

    }

    /**
     * @param Qdrant $client
     * @return void
     */
    public function buildEmbeddingsAndSaveOnQdrant(Qdrant $client): void
    {
        Category::query()->chunk(100, function ($categories) use ($client) {
            $points = new PointsStruct();
            foreach ($categories as $category) {
                $inputText = $category->name . ". " . $category->description;
                $response = Http::post('http://127.0.0.1:8000/embed',
                    ['text' => "passage: $inputText"]
                );

                $data = $response->object();

                $points->addPoint(
                    new PointStruct(
                        (int)$category->id,
                        new VectorStruct($data->embedding, 'category'),
                        [
                            'id' => $category->id,
                            'name' => $category->name,
                            'description' => $category->description,
                        ]
                    )
                );
            }
            try {
                $client->collections('categories')->points()->upsert($points);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        );
    }

    /**
     * @param \Qdrant\Qdrant $client
     * @return void
     */
    public function createCollectionOnQdrant(\Qdrant\Qdrant $client): void
    {
        $createCollection = new CreateCollection();
        $createCollection->addVector(new VectorParams(384, VectorParams::DISTANCE_COSINE), 'category');
        $client->collections('categories')->create($createCollection);
    }

    private function classifyProducts()
    {
    }
}
