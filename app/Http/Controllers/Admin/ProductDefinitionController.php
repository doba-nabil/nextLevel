<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ProductDefinitionDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductDefinitionRequest;
use App\Services\ProductDefinitionService;
use Illuminate\Http\Request;

class ProductDefinitionController extends Controller
{
    public function __construct(private ProductDefinitionService $productDefinitionService) {}


    public function index(ProductDefinitionDataTable $dataTable)
    {
        return $dataTable->render('dashboard.product_definitions.index');
    }

    public function create()
    {
        return view('dashboard.product_definitions.create');
    }

    public function store(ProductDefinitionRequest $request)
    {
        $this->productDefinitionService->create(
            $request->validated()
        );
        return redirect()->route('product_definitions.index')->with('success',__('admin.save_success'));
    }

    public function edit($id)
    {
        $definition = $this->productDefinitionService->getById($id);
        return view('dashboard.product_definitions.edit', compact('definition'));
    }

    public function update(ProductDefinitionRequest $request, $id)
    {
        $definition = $this->productDefinitionService->getById($id);
        $this->productDefinitionService->update($definition, $request->validated());
        return redirect()->route('product_definitions.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->productDefinitionService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => __('admin.delete_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.delete_error')
            ], 500);
        }
    }

    public function toggleActive(Request $request, $id)
    {
        try {
            $definition = $this->productDefinitionService->getById($id);
            $definition->active = $request->input('active', !$definition->active);
            $definition->save();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $definition->active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

}
