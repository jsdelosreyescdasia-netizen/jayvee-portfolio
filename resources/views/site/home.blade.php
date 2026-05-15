@extends('layouts.site', ['title' => 'Foodservice Product Showcase'])

@section('content')
@php
    $fallbackImage = asset('images/product-display.jpg');
    $publicImage = fn (?string $path) => $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)
        ? route('storage.public', $path)
        : null;
@endphp
<section class="home-hero" id="top" data-scroll-section="home" data-hero-slider>
    <div class="hero-track">
        @forelse($heroSlides as $slide)
            @php($slideTitle = preg_match('/corporation/i', $slide->title) ? 'Foodservice Product Showcase' : $slide->title)
            <article class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-hero-slide>
                <img class="hero-slide__image" src="{{ $publicImage($slide->image_path) ?: $publicImage($home->hero_image_path) ?: $fallbackImage }}" alt="{{ $slideTitle }}">
                <div class="hero-caption">
                    <h1>{{ $slideTitle }}</h1>
                    <p>{{ $slide->subtitle ?: $slide->description }}</p>
                </div>
            </article>
        @empty
            <article class="hero-slide is-active" data-hero-slide>
                <img class="hero-slide__image" src="{{ $publicImage($home->hero_image_path) ?: $fallbackImage }}" alt="{{ $home->section_title }}">
                <div class="hero-caption">
                    <h1>Foodservice Product Showcase</h1>
                    <p>{{ $settings->tagline ?: $home->summary }}</p>
                </div>
            </article>
        @endforelse
    </div>

    @if($heroSlides->count() > 1)
        <button class="hero-arrow hero-arrow--prev" type="button" aria-label="Previous slide" data-hero-prev>&lt;</button>
        <button class="hero-arrow hero-arrow--next" type="button" aria-label="Next slide" data-hero-next>&gt;</button>
        <div class="hero-dots" aria-label="Hero slide navigation">
            @foreach($heroSlides as $slide)
                <button class="{{ $loop->first ? 'is-active' : '' }}" type="button" aria-label="Show slide {{ $loop->iteration }}" data-hero-dot="{{ $loop->index }}"></button>
            @endforeach
        </div>
    @endif
</section>

<section class="about-band" id="about-us" data-scroll-section="about-us">
    <div class="about-emblem about-emblem--image">
        <img src="{{ $publicImage($about->feature_image_path) ?: $fallbackImage }}" alt="Foodservice products">
    </div>
    <div class="about-copy">
        <h2>{{ $about->section_title }}</h2>
        <p>{{ $about->summary }}</p>
        @if($about->button_url)
            <a class="button button--dark" href="{{ route('page', 'about-us') }}">{{ $about->button_label }}</a>
        @endif
    </div>
</section>

<section class="home-showcase home-showcase--light" id="partners" data-scroll-section="partners">
    <div class="section-heading">
        <h2>Business Clients</h2>
        <p>{{ $partnersPage->summary }}</p>
    </div>
    <div class="home-card-grid home-card-grid--clients">
        @foreach($partners as $partner)
            <article class="home-card">
                <div class="home-card__media">
                    <img src="{{ $publicImage($partner->image_path) ?: $fallbackImage }}" alt="{{ $partner->title }}">
                </div>
                <h3>{{ $partner->title }}</h3>
            </article>
        @endforeach
    </div>
    <a class="button button--dark section-button" href="{{ route('page', 'partners') }}">Browse More Partners</a>
</section>

<section class="home-showcase" id="brands" data-scroll-section="brands">
    <div class="section-heading">
        <h2>{{ $brandsPage->section_title }}</h2>
        <p>{{ $brandsPage->summary }}</p>
    </div>
    <div class="home-card-grid home-card-grid--brands">
        @foreach($brands as $brand)
            <article class="home-card home-card--logo">
                <div class="home-card__media">
                    <img src="{{ $publicImage($brand->image_path) ?: $fallbackImage }}" alt="{{ $brand->title }}">
                </div>
            </article>
        @endforeach
    </div>
    <a class="button button--dark section-button" href="{{ route('page', 'brands') }}">Browse More Brands</a>
</section>

<section class="home-showcase home-showcase--light" id="products" data-scroll-section="products">
    <div class="section-heading">
        <h2>{{ $productsPage->section_title }}</h2>
    </div>
    <div class="home-card-grid home-card-grid--products">
        @foreach($products as $product)
            <article class="home-card home-card--product" tabindex="0">
                <div class="home-card__media">
                    <img src="{{ $publicImage($product->image_path) ?: $fallbackImage }}" alt="{{ $product->title }}">
                </div>
                <div class="product-popover">
                    <h3>{{ $product->title }}</h3>
                    @if($product->description)
                        <p>{{ $product->description }}</p>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    <a class="button button--dark section-button" href="{{ route('page', 'products') }}">Browse More Products</a>
</section>

<section class="home-showcase" id="services" data-scroll-section="services">
    <div class="section-heading">
        <h2>Our Services</h2>
    </div>
    <div class="service-feature-grid">
        @foreach($services as $service)
            <article class="service-feature">
                <div class="service-feature__media">
                    <img src="{{ $publicImage($service->image_path) ?: $fallbackImage }}" alt="{{ $service->title }}">
                </div>
                <h3>{{ $service->title }}</h3>
            </article>
        @endforeach
    </div>
</section>

<section class="contact-cta" id="contact-us" data-scroll-section="contact-us">
    <div class="contact-cta__image">
        <img src="{{ $publicImage($contactCta?->image_path) ?: $fallbackImage }}" alt="{{ $contactCta?->title ?: 'Get in Touch with Us!' }}">
    </div>
    <div class="contact-cta__copy">
        <h2>{{ $contactCta?->title ?: 'Get in Touch with Us!' }}</h2>
        <p>{{ $contactCta?->description ?: $contactPage->summary }}</p>
        <p>Have questions or need more information about our services? Feel free to reach out. We'd love to hear from you.</p>
        <a class="button button--dark" href="{{ route('page', 'contact-us') }}">{{ $contactCta?->subtitle ?: 'Contact us today and let us know how we can assist you!' }}</a>
    </div>
</section>

<section class="reviews-section">
    <div class="section-heading">
        <h2>Clients Reviews</h2>
    </div>
    <div class="reviews-grid">
        @foreach($reviews as $review)
            <article class="review-card">
                <p>{{ $review->description }}</p>
                <div class="review-person">
                    <img src="{{ $publicImage($review->image_path) ?: $fallbackImage }}" alt="{{ $review->title }}">
                    <div>
                        <strong>{{ $review->title }}</strong>
                        <small>{{ $review->subtitle ?: 'Unknown' }}</small>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
