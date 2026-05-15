<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'CMS Admin' }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="admin-body">
    @php
        $navItems = [
            ['label' => 'Dashboard', 'route' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
            ['label' => 'Messages', 'route' => route('admin.messages.index'), 'active' => request()->routeIs('admin.messages.*')],
            ['label' => 'Site Settings', 'route' => route('admin.settings.edit'), 'active' => request()->routeIs('admin.settings.*')],
            ['label' => 'Pages', 'route' => route('admin.pages.index'), 'active' => request()->routeIs('admin.pages.*')],
            ['label' => 'Hero Slides', 'route' => route('admin.items.index', ['type' => 'hero_slide']), 'active' => request('type') === 'hero_slide'],
            ['label' => 'Products', 'route' => route('admin.items.index', ['type' => 'product']), 'active' => request('type') === 'product'],
            ['label' => 'Brands', 'route' => route('admin.items.index', ['type' => 'brand']), 'active' => request('type') === 'brand'],
            ['label' => 'Partners', 'route' => route('admin.items.index', ['type' => 'partner']), 'active' => request('type') === 'partner'],
            ['label' => 'Services', 'route' => route('admin.items.index', ['type' => 'service']), 'active' => request('type') === 'service'],
            ['label' => 'Vision & Mission', 'route' => route('admin.items.index', ['type' => 'vision']), 'active' => request('type') === 'vision'],
            ['label' => 'Gallery', 'route' => route('admin.items.index', ['type' => 'gallery']), 'active' => request('type') === 'gallery'],
            ['label' => 'Contact CTA', 'route' => route('admin.items.index', ['type' => 'contact_cta']), 'active' => request('type') === 'contact_cta'],
            ['label' => 'Reviews', 'route' => route('admin.items.index', ['type' => 'review']), 'active' => request('type') === 'review'],
            ['label' => 'Contact Cards', 'route' => route('admin.items.index', ['type' => 'contact']), 'active' => request('type') === 'contact'],
        ];
    @endphp
    <aside class="admin-sidebar">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}">
            <span>Showcase</span>
            <strong>CMS</strong>
        </a>
        <nav class="admin-nav" aria-label="Admin navigation">
            @foreach($navItems as $item)
                <a class="{{ $item['active'] ? 'is-active' : '' }}" href="{{ $item['route'] }}">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('home') }}" target="_blank">View Website</a>
        </nav>
        <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </aside>
    <main class="admin-main">
        @if(session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="notice notice--error">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
