<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ContentItemController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'product');

        return view('admin.items.index', [
            'type' => $type,
            'types' => ContentItem::TYPES,
            'items' => ContentItem::where('type', $type)->orderBy('sort_order')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $item = new ContentItem([
            'type' => $request->query('type', 'product'),
            'is_published' => true,
        ]);

        return view('admin.items.create', ['item' => $item, 'types' => ContentItem::TYPES]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->storeImage($request, $data);
        ContentItem::create($data);

        return to_route('admin.items.index', ['type' => $data['type']])->with('status', 'Content item created.');
    }

    public function edit(ContentItem $item)
    {
        return view('admin.items.edit', ['item' => $item, 'types' => ContentItem::TYPES]);
    }

    public function update(Request $request, ContentItem $item)
    {
        $data = $this->validated($request);
        $this->storeImage($request, $data, $item->image_path);
        $item->update($data);

        return to_route('admin.items.edit', $item)->with('status', 'Content item updated.');
    }

    public function destroy(ContentItem $item)
    {
        $type = $item->type;

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return to_route('admin.items.index', ['type' => $type])->with('status', 'Content item deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(ContentItem::TYPES)],
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        unset($data['image']);

        return $data;
    }

    private function storeImage(Request $request, array &$data, ?string $oldPath = null): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $data['image_path'] = $request->file('image')->store('cms/items', 'public');
    }
}
