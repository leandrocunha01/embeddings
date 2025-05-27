<?php

namespace App\Console\Commands;

use App\Facades\Qdrant;
use App\Models\Exams;
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
        // $createCollection = new CreateCollection();
        // $createCollection->addVector(new VectorParams(384, VectorParams::DISTANCE_COSINE), 'exams');
        // $response = $client->collections('exams')->create($createCollection);

        Exams::query()->chunk(100, function ($exams) use ($client) {
            $points = new PointsStruct();
            foreach ($exams as $exam) {
                $cleanString = str_replace(["\n", "\r"], '', $exam->interpretation);
                $response = Http::post('http://127.0.0.1:8000/embed',
                    ['text' => "$exam->name; $cleanString"]
                );

                $data = $response->object();

                $points->addPoint(
                    new PointStruct(
                        (int)$exam->id,
                        new VectorStruct($data->embedding, 'exams'),
                        [
                            'id' => $exam->id,
                            'name' => $exam->name,
                            'interpretation' => $exam->interpretation,
                        ]
                    )
                );
            }
            try {
                $client->collections('exams')->points()->upsert($points);
            }catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        );
    }
}
