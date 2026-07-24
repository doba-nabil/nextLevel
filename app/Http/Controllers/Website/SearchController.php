<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\EducationalLevel;
use App\Models\MarriageType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->get('keyword');
        $products = Product::where('active', true)
            ->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('description', 'LIKE', "%{$keyword}%");
            })
            ->paginate(12);

        return view('website.search.search', compact('products', 'keyword'));
    }
}
