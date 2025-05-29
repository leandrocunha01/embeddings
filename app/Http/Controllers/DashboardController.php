<?php

namespace App\Http\Controllers;

use App\Facades\Qdrant;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
use Qdrant\Models\Filter\Condition\MatchInt;
use Qdrant\Models\Filter\Condition\MatchString;
use Qdrant\Models\Filter\Filter;
use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\VectorStruct;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        /*$query = Product::query();

        if ($request->filled('search')) {
            $query->whereIn('id', $this->embedding($request->input('search')));
        }

        $products = $query->get();*/
        $searchTerm = $request->input('search');
        $category = (int)$request->input('category');
        $products = null;
        if ($searchTerm) {
            $products = $this->embedding($searchTerm, $category);
        }

        return Inertia::render('Dashboard', [
            'products' => $products,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    protected function embedding(string $product, $category): array
    {
        $response = Http::post('http://127.0.0.1:8000/embed',
            ['text' => "query: $product"]
        );

        $data = $response->object();
        $qdrant = Qdrant::getClient();
        $searchRequest = (new SearchRequest(new VectorStruct($data->embedding, 'product')))
            ->setLimit(5)
            ->setParams([
                'hnsw_ef' => 128,
                'exact' => false,
            ])
            ->setWithPayload(true);
        if ($category) {
            $searchRequest->setFilter(
                (new Filter())->addShould(
                    new MatchInt('category', (int)$category)
                )
            );
        }

        $response = $qdrant->collections('products')->points()->search($searchRequest);
        $ret = [];
        foreach ($response['result'] as $value) {
            $ret[] = $value['payload']['id'];
        }
        return $response['result'];
    }

}

