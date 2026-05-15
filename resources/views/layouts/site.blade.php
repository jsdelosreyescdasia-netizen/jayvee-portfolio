<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Foodservice Product Showcase' }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="site-body">
    <header class="site-header">
        <nav class="nav-shell">
            <a class="brand" href="{{ route('home') }}">
                <x-brand-mark :settings="$settings" />
                <span>Foodservice Product Showcase</span>
            </a>
            <button class="nav-toggle" type="button" aria-label="Open menu" data-nav-toggle>
                <span></span><span></span><span></span>
            </button>
            <div class="nav-links" data-nav-links>
                @foreach($pages as $navPage)
                    @php
                        $isHome = request()->routeIs('home');
                        $navHref = $navPage->slug === 'home' ? route('home') : route('page', $navPage->slug);
                    @endphp
                    <a class="{{ request()->path() === $navPage->slug || ($isHome && $navPage->slug === 'home') ? 'is-active' : '' }}"
                       href="{{ $navHref }}"
                       data-nav-section="{{ $navPage->slug }}">
                        {{ $navPage->nav_label }}
                    </a>
                @endforeach
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-logo">
                <x-brand-mark :settings="$settings" />
            </div>

            <section class="footer-contact" aria-labelledby="footer-contact-title">
                <h2 id="footer-contact-title">Get In Touch</h2>
                <p><span aria-hidden="true">&#9679;</span>{{ $settings->address }}</p>
                <p><span aria-hidden="true">&#9742;</span>{{ $settings->phone }}</p>
                @if($settings->mobile)
                    <p><span aria-hidden="true">&#9633;</span>{{ $settings->mobile }}</p>
                @endif
                <p><span aria-hidden="true">&#9993;</span>{{ $settings->email }}</p>
            </section>

            <section class="footer-links" aria-labelledby="footer-links-title">
                <h2 id="footer-links-title">Quick Links</h2>
                @foreach($pages->whereIn('slug', ['about-us', 'contact-us', 'services', 'products', 'brands']) as $footerPage)
                    <a href="{{ route('page', $footerPage->slug) }}"><span aria-hidden="true">&gt;</span>{{ $footerPage->title }}</a>
                @endforeach
            </section>
        </div>

        <div class="footer-bottom">
            <span>&copy; <a href="{{ route('home') }}">Foodservice Product Showcase</a></span>
            <strong>All Right Reserved.</strong>
        </div>
    </footer>

    <a class="back-top" href="#top" aria-label="Back to top">^</a>
</body>
</html>
