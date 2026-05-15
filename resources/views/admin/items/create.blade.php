@extends('layouts.admin', ['title' => 'Create Item'])

@section('content')
<div class="admin-heading">
    <h1>Create Content Item</h1>
</div>

<form class="admin-form" method="post" action="{{ route('admin.items.store') }}" enctype="multipart/form-data">
    @csrf
    @include('admin.items.partials.form', ['item' => $item, 'types' => $types])
    <button class="button button--red" type="submit">Create Item</button>
</form>
@endsection
