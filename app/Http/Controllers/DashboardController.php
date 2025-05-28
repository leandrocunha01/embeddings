<?php

namespace App\Http\Controllers;

use App\Facades\Qdrant;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
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

        return Inertia::render('Dashboard', [
            'products' => $this->embedding($request->input('search')),
            'filters' => $request->only('search'),
        ]);
    }

    protected function embedding(string $product): array
    {
        $response = Http::post('http://127.0.0.1:8000/embed',
            ['text' => "query: $product"]
        );

        $data = $response->object();

        $searchRequest = (new SearchRequest(new VectorStruct($data->embedding, 'product')))
            ->setLimit(5)
            ->setWithPayload(true);

        $qdrant = Qdrant::getClient();
        $response = $qdrant->collections('products')->points()->search($searchRequest);
        $ret = [];
        foreach ($response['result'] as $value) {
            $ret[] = $value['payload']['id'];
        }
        return $response['result'];
    }

}

