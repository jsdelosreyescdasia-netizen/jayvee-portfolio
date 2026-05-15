@extends('layouts.admin', ['title' => 'Dashboard'])

@section('content')
<div class="admin-heading">
    <h1>CMS Dashboard</h1>
    <p>Edit the website copy, replace images, and manage the cards shown on each page.</p>
</div>

<div class="stat-grid">
    <article><span>{{ $pageCount }}</span><strong>Editable pages</strong></article>
    @foreach(['product', 'brand', 'partner', 'service', 'contact'] as $type)
        <article><span>{{ $itemCounts[$type] ?? 0 }}</span><strong>{{ ucfirst($type) }} items</strong></article>
    @endforeach
</div>
@endsection
