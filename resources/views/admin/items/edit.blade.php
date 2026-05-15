@extends('layouts.admin', ['title' => 'Edit '.$item->title])

@section('content')
<div class="admin-heading admin-heading--split">
    <div>
        <h1>Edit {{ $item->title }}</h1>
        <p>{{ ucfirst($item->type) }} content item</p>
    </div>
    <form method="post" action="{{ route('admin.items.destroy', $item) }}">
        @csrf
        @method('delete')
        <button class="button button--dark" type="submit">Delete</button>
    </form>
</div>

<form class="admin-form" method="post" action="{{ route('admin.items.update', $item) }}" enctype="multipart/form-data">
    @csrf
    @method('put')
    @include('admin.items.partials.form', ['item' => $item, 'types' => $types])
    <button class="button button--red" type="submit">Save Item</button>
</form>
@endsection
