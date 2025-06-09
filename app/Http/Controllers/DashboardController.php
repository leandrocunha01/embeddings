<?php

namespace App\Http\Controllers;

use App\Facades\Qdrant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
use Qdrant\Models\Filter\Condition\MatchInt;
use Qdrant\Models\Filter\Filter;
use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\VectorStruct;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $searchTerm = $request->input('search');
        $category = (int)$request->input('category');
        $products = $searchTerm ? $this->searchEmbedding('products', 'product', $searchTerm, 5, $category) : null;

        return Inertia::render('Dashboard', [
            'products' => $products,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function classify(Request $request): Response
    {
        $searchTerm = $request->input('search');
        $categories = $searchTerm ? $this->searchEmbedding('categories', 'category', $searchTerm, 1) : null;

        return Inertia::render('Classify', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    protected function getEmbedding(string $text): array
    {
        $response = Http::post('http://127.0.0.1:8000/embed', [
            'text' => "query: $text"
        ]);

        return $response->object()->embedding ?? [];
    }

    protected function searchEmbedding(string $collection, string $vectorName, string $query, int $limit, ?int $category = null): array
    {
        $embedding = $this->getEmbedding($query);

        $searchRequest = (new SearchRequest(new VectorStruct($embedding, $vectorName)))
            ->setLimit($limit)
            ->setParams([
                'hnsw_ef' => 128,
                'exact' => false,
            ])
            ->setWithPayload(true);

        if ($category && $collection === 'products') {
            $searchRequest->setFilter(
                (new Filter())->addShould(
                    new MatchInt('category', $category)
                )
            );
        }

        $qdrant = Qdrant::getClient();
        $response = $qdrant->collections($collection)->points()->search($searchRequest);

        return $response['result'] ?? [];
    }
}
