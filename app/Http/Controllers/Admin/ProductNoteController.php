<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ProductNoteDataTable;
use App\Http\Controllers\Controller;
use App\Models\ProductNote;
use Illuminate\Http\Request;

class ProductNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductNoteDataTable $dataTable)
    {
        return $dataTable->render('dashboard.product_notes.index');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $note = ProductNote::with(['product', 'user'])->findOrFail($id);
        
        return view('dashboard.product_notes.partials.note_detail', compact('note'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $note = ProductNote::findOrFail($id);
            $note->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.delete_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.delete_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }
}
