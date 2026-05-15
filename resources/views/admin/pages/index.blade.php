@extends('layouts.admin', ['title' => 'Pages'])

@section('content')
<div class="admin-heading">
    <h1>Pages</h1>
    <p>Update headings, copy, order, and page images.</p>
</div>

<div class="admin-table">
    @foreach($pages as $page)
        <div class="admin-row">
            <span>{{ $page->sort_order }}</span>
            <strong>{{ $page->title }}</strong>
            <span>{{ $page->slug }}</span>
            <a class="button button--small" href="{{ route('admin.pages.edit', $page) }}">Edit</a>
        </div>
    @endforeach
</div>
@endsection
