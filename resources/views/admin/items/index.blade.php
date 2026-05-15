@extends('layouts.admin', ['title' => ucfirst($type).' Items'])

@section('content')
<div class="admin-heading admin-heading--split">
    <div>
        <h1>{{ ucfirst($type) }} Items</h1>
        <p>Add, edit, reorder, or replace pictures for this section.</p>
    </div>
    <a class="button button--red" href="{{ route('admin.items.create', ['type' => $type]) }}">Add Item</a>
</div>

<div class="tabs">
    @foreach($types as $tab)
        <a class="{{ $tab === $type ? 'is-active' : '' }}" href="{{ route('admin.items.index', ['type' => $tab]) }}">{{ ucfirst($tab) }}</a>
    @endforeach
</div>

<div class="admin-table">
    @foreach($items as $item)
        <div class="admin-row">
            <span>{{ $item->sort_order }}</span>
            <strong>{{ $item->title }}</strong>
            <span>{{ $item->subtitle }}</span>
            <a class="button button--small" href="{{ route('admin.items.edit', $item) }}">Edit</a>
        </div>
    @endforeach
</div>
@endsection
