<?php

namespace App\Console\Commands;

use App\Facades\Qdrant;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use OpenAI;
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
    public function buildEmbeddingsAndSaveOnQdrant(\Qdrant\Qdrant $client): void
    {
        $openai = OpenAI::client(env('OPENIA_KEY'));;

        $equipments = DB::select('
                select
                    e.id,
                    e.code,
                       description,
                       serial_number,
                       manufacture_date,
                       latest_collector_update,
                       corporate_name,
                       address,
                       client_fantasy_name,
                       consultant,
                       responsible_contact
                from equipments e
                         join business_units b on e.business_unit_id = b.id
                         join agreements a on a.id = b.agreement_id;
        ');

        $i = 0;
        foreach ($equipments as $equipment) {
            $points = new PointsStruct();

            $preEmbedding = $equipment->code . ' ' . $equipment->description . ' ' . $equipment->serial_number . ' '
                . $equipment->manufacture_date . ' ' . $equipment->latest_collector_update . ' ' . $equipment->corporate_name
                . ' ' . $equipment->address . ' ' . $equipment->client_fantasy_name . ' ' . $equipment->consultant . ' '
                . $equipment->responsible_contact;

            $response = $openai->embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $preEmbedding,
            ]);

            $embedding = array_values($response->embeddings[0]->embedding);
            $points->addPoint(
                new PointStruct(
                    (int)$equipment->id,
                    new VectorStruct($embedding, 'default'),
                    (array)$equipment
                )
            );

            $client->collections('equipments')->points()->upsert($points);
        }
    }

    /**
     * @param \Qdrant\Qdrant $client
     * @return void
     */
    public function createCollectionOnQdrant(\Qdrant\Qdrant $client): void
    {
        $createCollection = new CreateCollection();
        $createCollection->addVector(new VectorParams(1536, VectorParams::DISTANCE_COSINE), 'default');
        $client->collections('equipments')->create($createCollection);
    }

    private function classifyProducts()
    {
    }
}
