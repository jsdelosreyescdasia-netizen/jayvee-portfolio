@extends('layouts.admin', ['title' => 'Site Settings'])

@section('content')
<div class="admin-heading">
    <h1>Site Settings</h1>
    <p>Update company identity and contact details used across the site.</p>
</div>

<form class="admin-form" method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('put')
    <div class="form-grid">
        <label>Company Name<input name="company_name" value="{{ old('company_name', $settings->company_name) }}" required></label>
        <label>Tagline<input name="tagline" value="{{ old('tagline', $settings->tagline) }}"></label>
    </div>
    <div class="form-grid">
        <label>Email<input type="email" name="email" value="{{ old('email', $settings->email) }}"></label>
        <label>Phone<input name="phone" value="{{ old('phone', $settings->phone) }}"></label>
        <label>Mobile<input name="mobile" value="{{ old('mobile', $settings->mobile) }}"></label>
    </div>
    <label>Address<input name="address" value="{{ old('address', $settings->address) }}"></label>
    <label>Map Embed URL<input name="map_embed_url" value="{{ old('map_embed_url', $settings->map_embed_url) }}"></label>
    @if($settings->logo_path)
        <img class="admin-preview admin-preview--logo" src="{{ route('storage.public', $settings->logo_path) }}" alt="Current logo">
    @endif
    <label>Logo<input type="file" name="logo" accept="image/*"></label>
    <button class="button button--red" type="submit">Save Settings</button>
</form>
@endsection
