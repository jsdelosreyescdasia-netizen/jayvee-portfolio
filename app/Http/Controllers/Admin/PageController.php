<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', [
            'pages' => Page::orderBy('sort_order')->get(),
        ]);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'nav_label' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:160'],
            'section_title' => ['nullable', 'string', 'max:160'],
            'summary' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'max:4096'],
            'feature_image' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        unset($data['hero_image'], $data['feature_image']);

        if ($request->hasFile('hero_image')) {
            if ($page->hero_image_path) {
                Storage::disk('public')->delete($page->hero_image_path);
            }

            $data['hero_image_path'] = $request->file('hero_image')->store('cms/pages', 'public');
        }

        if ($request->hasFile('feature_image')) {
            if ($page->feature_image_path) {
                Storage::disk('public')->delete($page->feature_image_path);
            }

            $data['feature_image_path'] = $request->file('feature_image')->store('cms/pages', 'public');
        }

        $page->update($data);

        return to_route('admin.pages.edit', $page)->with('status', 'Page updated.');
    }
}
