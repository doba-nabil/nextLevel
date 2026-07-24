<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\SliderDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SliderRequest;
use App\Services\SliderService;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function __construct(private SliderService $sliderService) {}


    public function index(SliderDataTable $dataTable)
    {
        return $dataTable->render('dashboard.sliders.index');
    }

    public function create()
    {
        return view('dashboard.sliders.create-edit');
    }

    public function store(SliderRequest $request)
    {
        $images = [];
        if ($request->hasFile('image_ar')) {
            $images['image_ar'] = $request->file('image_ar');
        }
        if ($request->hasFile('image_en')) {
            $images['image_en'] = $request->file('image_en');
        }
        // Legacy support if needed, or if we want to allow single image upload
        if ($request->hasFile('image')) {
            $images['image'] = $request->file('image');
        }

        $this->sliderService->create(
            $request->validated(),
            $images
        );
        return redirect()->route('sliders.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $model = $this->sliderService->getById($id);
        return view('dashboard.sliders.create-edit', compact('model'));
    }

    public function update(SliderRequest $request, $id)
    {
        $model = $this->sliderService->getById($id);
        
        $images = [];
        if ($request->hasFile('image_ar')) {
            $images['image_ar'] = $request->file('image_ar');
        }
        if ($request->hasFile('image_en')) {
            $images['image_en'] = $request->file('image_en');
        }
        if ($request->hasFile('image')) {
            $images['image'] = $request->file('image');
        }

        $this->sliderService->update($model, $request->validated(), $images);
        return redirect()->route('sliders.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->sliderService->delete($id);

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
            $slider = $this->sliderService->getById($id);
            $slider->active = $request->input('active', !$slider->active);
            $slider->save();

            // Clear slider cache immediately
            \Illuminate\Support\Facades\Cache::forget('website_sliders');
            \Illuminate\Support\Facades\Cache::forget('website_sliders_v2');

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $slider->active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

}
