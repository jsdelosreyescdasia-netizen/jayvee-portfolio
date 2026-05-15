@extends('layouts.admin', ['title' => 'Edit '.$page->title])

@section('content')
<div class="admin-heading">
    <h1>Edit {{ $page->title }}</h1>
    <p>{{ url($page->slug === 'home' ? '/' : $page->slug) }}</p>
</div>

<form class="admin-form" method="post" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
    @csrf
    @method('put')
    <div class="form-grid">
        <label>Navigation Label<input name="nav_label" value="{{ old('nav_label', $page->nav_label) }}" required></label>
        <label>Page Title<input name="title" value="{{ old('title', $page->title) }}" required></label>
        <label>Section Title<input name="section_title" value="{{ old('section_title', $page->section_title) }}"></label>
        <label>Sort Order<input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}" min="0" required></label>
    </div>
    <label>Summary<textarea name="summary" rows="4">{{ old('summary', $page->summary) }}</textarea></label>
    <label>Body<textarea name="body" rows="7">{{ old('body', $page->body) }}</textarea></label>
    <div class="form-grid">
        <label>Button Label<input name="button_label" value="{{ old('button_label', $page->button_label) }}"></label>
        <label>Button URL<input name="button_url" value="{{ old('button_url', $page->button_url) }}"></label>
    </div>
    <label>Header Image
        <input type="file" name="hero_image" accept="image/*">
        <small>Recommended: 1600x500 JPG/PNG. This appears behind the page title in the header banner.</small>
    </label>
    @if($page->hero_image_path)
        <div class="admin-image-preview">
            <img src="{{ route('storage.public', $page->hero_image_path) }}" alt="{{ $page->title }} header image">
        </div>
    @endif
    <label>Feature Image
        <input type="file" name="feature_image" accept="image/*">
        <small>Recommended: 900x700 JPG/PNG. This appears in the main section image area for pages like About Us.</small>
    </label>
    @if($page->feature_image_path)
        <div class="admin-image-preview">
            <img src="{{ route('storage.public', $page->feature_image_path) }}" alt="{{ $page->title }} feature image">
        </div>
    @endif
    <label class="check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))> Published</label>
    <button class="button button--red" type="submit">Save Page</button>
</form>
@endsection
