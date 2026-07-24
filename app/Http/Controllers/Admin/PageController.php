<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\AddonDataTable;
use App\DataTables\PageDataTable;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Traits\slugGenerator;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use  slugGenerator;
    public function index(PageDataTable $dataTable)
    {
        return $dataTable->render('dashboard.pages.index');
    }

    public function create()
    {
        return view('dashboard.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title.en' => 'required|string|max:255',
            'title.ar' => 'required|string|max:255',
            'content.en' => 'required|string',
            'content.ar' => 'required|string',
        ]);
        $request['slug'] = $this->generateSlug($request->all());
        Page::create($request->all());

        return redirect()->route('pages.index')->with('success', __('admin.save_success'));
    }

    public function edit(Page $page)
    {
        return view('dashboard.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title.en' => 'required|string|max:255',
            'title.ar' => 'required|string|max:255',
            'content.en' => 'required|string',
            'content.ar' => 'required|string',
        ]);
        if($page->slug == 'about-us'){
            $request['slug'] = $this->generateSlug($request->all());
        }
        $page->update($request->all());

        return redirect()->route('pages.index')->with('success', __('admin.update_success'));
    }

    public function destroy(Page $page)
    {
        try {
            $page->delete();

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
}

