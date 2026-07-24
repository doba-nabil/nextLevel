<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BannerDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Services\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    public function __construct(private BannerService $bannerService) {}

    public function index(BannerDataTable $dataTable)
    {
        return $dataTable->render('dashboard.banners.index');
    }

    public function create()
    {
        return view('dashboard.banners.create-edit');
    }

    public function store(BannerRequest $request)
    {
        $this->bannerService->create(
            $request->validated(),
            $request->file('image_ar'),
            $request->file('image_en')
        );
        $this->clearBannerCache();
        return redirect()->route('banners.index')->with('success', __('admin.save_success'));
    }

    public function edit($id)
    {
        $model = $this->bannerService->getById($id);
        return view('dashboard.banners.create-edit', compact('model'));
    }

    public function update(BannerRequest $request, $id)
    {
        $model = $this->bannerService->getById($id);
        $this->bannerService->update(
            $model, 
            $request->validated(), 
            $request->file('image_ar'),
            $request->file('image_en')
        );
        $this->clearBannerCache();
        return redirect()->route('banners.index')->with('success', __('admin.update_success'));
    }

    public function destroy($id)
    {
        try {
            $this->bannerService->delete($id);
            $this->clearBannerCache();

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
            $banner = $this->bannerService->getById($id);
            $banner->active = $request->input('active', !$banner->active);
            $banner->save();
            $this->clearBannerCache();

            return response()->json([
                'status' => 'success',
                'message' => __('admin.update_success'),
                'active' => $banner->active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.update_error')
            ], 500);
        }
    }

    /**
     * Clear banner cache for all locales
     */
    private function clearBannerCache(): void
    {
        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            Cache::forget("website_banners_{$locale}");
        }
    }
}

