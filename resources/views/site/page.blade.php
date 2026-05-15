@extends('layouts.site', ['title' => $page->title.' | Foodservice Product Showcase'])

@section('content')
@php
    $fallbackImage = asset('images/product-display.jpg');
    $publicImage = fn (?string $path) => $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)
        ? route('storage.public', $path)
        : null;
@endphp
<section
    class="page-hero page-hero--image"
    id="top"
    style="--page-hero-image: url('{{ $publicImage($page->hero_image_path) ?: $fallbackImage }}')"
>
    <div class="page-hero__content">
        <h1>{{ $page->title }}</h1>
        <div class="breadcrumb"><a href="{{ route('home') }}">HOME</a><span>/</span><span>{{ $page->nav_label }}</span></div>
    </div>
</section>

@if($page->slug === 'about-us')
    <section class="about-detail">
        <div class="about-detail__mark">
            <img src="{{ $publicImage($page->feature_image_path) ?: $fallbackImage }}" alt="Foodservice products">
        </div>
        <div class="about-detail__copy">
            <h2>{{ $page->section_title ?: $page->title }}</h2>
            @if($page->summary)
                <p>{{ $page->summary }}</p>
            @endif
            @if($page->body)
                <p>{{ $page->body }}</p>
            @endif
        </div>
    </section>

    <section class="vision-band">
        <div class="section-heading">
            <h2>Our Vision &amp; Mission</h2>
            <p>We assist you with selecting the right equipment for your requirement, provide a competitively priced top quality equipment that meet specific customer quality assurance specifications.</p>
        </div>
        <div class="vision-grid">
            @foreach($visionItems as $vision)
                <article>{{ $vision->description }}</article>
            @endforeach
        </div>
    </section>

    <section class="gallery-section">
        <div class="gallery-heading">
            <h2>Our Gallery</h2>
            <div class="gallery-filters" data-gallery-filters>
                <button class="is-active" type="button" data-gallery-filter="store set-up">Store Set-Up</button>
                <button type="button" data-gallery-filter="special events">Special Events</button>
                <button type="button" data-gallery-filter="trainings">Trainings</button>
                <button type="button" data-gallery-filter="awards & recognition">Awards &amp; Recognition</button>
            </div>
        </div>
        <div class="gallery-grid" data-gallery-grid>
            @foreach($items as $item)
                @php($itemImage = $publicImage($item->image_path) ?: $fallbackImage)
                <article class="gallery-card" data-gallery-category="{{ Str::lower($item->subtitle ?: 'Store Set-Up') }}">
                    <span>{{ $item->title }}</span>
                    <button class="gallery-zoom" type="button" data-lightbox-src="{{ $itemImage }}" data-lightbox-title="{{ $item->title }}">
                        <img src="{{ $itemImage }}" alt="{{ $item->title }}">
                    </button>
                </article>
            @endforeach
        </div>
        <p class="gallery-empty" data-gallery-empty hidden>No gallery photos in this category yet.</p>
    </section>
    <div class="lightbox" data-lightbox hidden>
        <button class="lightbox__close" type="button" aria-label="Close image" data-lightbox-close>&times;</button>
        <figure>
            <img src="" alt="" data-lightbox-image>
            <figcaption data-lightbox-caption></figcaption>
        </figure>
    </div>
@else
    <section class="content-section content-section--{{ $page->slug }}">
        <div class="section-heading">
            <h2>{{ $page->section_title ?: $page->title }}</h2>
            @if($page->summary)
                <p>{{ $page->summary }}</p>
            @endif
        </div>

        @if($page->slug === 'contact-us')
        <div class="contact-grid">
            @foreach($items as $item)
                <article class="contact-card">
                    <span class="contact-icon">{{ strtoupper(substr($item->title, 0, 1)) }}</span>
                    <strong>{{ $item->subtitle ?: $item->description }}</strong>
                </article>
            @endforeach
        </div>
        <div class="contact-layout">
            <iframe src="{{ $settings->map_embed_url }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            <form class="contact-form" method="post" action="{{ route('contact.store') }}">
                @csrf
                @if(session('contact_status'))
                    <div class="form-success">{{ session('contact_status') }}</div>
                @endif
                @if($errors->any())
                    <div class="form-error">{{ $errors->first() }}</div>
                @endif
                <label class="hidden-field">Website<input name="website" tabindex="-1" autocomplete="off"></label>
                <label>Name <span>*</span><input name="name" type="text" placeholder="Name:" value="{{ old('name') }}" required></label>
                <label>Email <span>*</span><input name="email" type="email" placeholder="Email:" value="{{ old('email') }}" required></label>
                <label>Message <span>*</span><textarea name="message" rows="6" placeholder="Message:" required>{{ old('message') }}</textarea></label>
                <div class="recaptcha-wrap">
                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                </div>
                <button class="button button--red" type="submit">Send Message</button>
            </form>
        </div>
        @else
        <div class="item-grid item-grid--{{ $page->slug }}">
            @foreach($items as $item)
                @php($itemImage = $publicImage($item->image_path) ?: $fallbackImage)
                <article class="content-card {{ $page->slug === 'products' ? 'content-card--product' : '' }}" tabindex="{{ $page->slug === 'products' ? '0' : '-1' }}">
                    <div class="card-media">
                        @if(in_array($page->slug, ['products', 'partners', 'brands', 'services']) || $item->image_path)
                            <img src="{{ $itemImage }}" alt="{{ $item->title }}">
                        @else
                            <div class="placeholder-image placeholder-image--{{ $item->type }}">{{ $item->title }}</div>
                        @endif
                    </div>
                    <div class="card-copy">
                        <h3>{{ $item->title }}</h3>
                        @if($item->subtitle)<strong>{{ $item->subtitle }}</strong>@endif
                        @if($item->description && $page->slug !== 'products')<p>{{ $item->description }}</p>@endif
                    </div>
                    @if($page->slug === 'products')
                        <div class="product-popover">
                            <h3>{{ $item->title }}</h3>
                            @if($item->description)
                                <p>{{ $item->description }}</p>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        @endif
    </section>
@endif
@if($page->slug === 'contact-us')
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@endsection
