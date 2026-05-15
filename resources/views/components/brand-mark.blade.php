@props(['settings'])

@if($settings->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings->logo_path))
    <img class="brand-mark" src="{{ route('storage.public', $settings->logo_path) }}" alt="{{ $settings->company_name }} logo">
@else
    <img class="brand-mark" src="{{ asset('images/site-logo.png') }}" alt="{{ $settings->company_name }} logo">
@endif
