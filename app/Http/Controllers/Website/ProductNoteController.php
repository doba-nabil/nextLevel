<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\ProductNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductNoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'content' => 'required|string|max:1000',
        ]);

        ProductNote::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'content' => $request->content,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => __('website.note_added_successfully') ?? 'Note added successfully',
        ]);
    }
}
