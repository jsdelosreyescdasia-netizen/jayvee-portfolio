<div class="form-grid">
    <label>Type
        <select name="type" required>
            @foreach($types as $type)
                <option value="{{ $type }}" @selected(old('type', $item->type) === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
    </label>
    <label>Sort Order<input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" min="0" required></label>
</div>
<div class="form-grid">
    <label>Title<input name="title" value="{{ old('title', $item->title) }}" required></label>
    <label>Subtitle<input name="subtitle" value="{{ old('subtitle', $item->subtitle) }}"></label>
</div>
<label>Description<textarea name="description" rows="5">{{ old('description', $item->description) }}</textarea></label>
<label>URL<input name="url" value="{{ old('url', $item->url) }}"></label>
@if($item->image_path)
    <img class="admin-preview" src="{{ route('storage.public', $item->image_path) }}" alt="{{ $item->title }}">
@endif
@php
    $imageHelp = match(old('type', $item->type)) {
        'hero_slide' => 'Recommended: 1600x700 JPG/PNG. Use a clear store, training, event, or equipment banner.',
        'product' => 'Recommended: transparent PNG or clean product cutout, at least 900x700.',
        'partner', 'gallery', 'service' => 'Recommended: 900x600 JPG/PNG with good lighting.',
        'brand' => 'Recommended: transparent logo PNG, at least 600px wide.',
        'contact_cta', 'review' => 'Recommended: 800x600 JPG/PNG.',
        default => 'Recommended: JPG/PNG under 4MB.',
    };
@endphp
<label>Image<input type="file" name="image" accept="image/*"><small>{{ $imageHelp }}</small></label>
<label class="check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $item->is_published ?? true))> Published</label>
