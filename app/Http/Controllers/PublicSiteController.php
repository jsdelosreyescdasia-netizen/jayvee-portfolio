<?php

namespace App\Http\Controllers;

use App\Models\ContentItem;
use App\Models\Page;
use App\Models\SiteSetting;

class PublicSiteController extends Controller
{
    public function home()
    {
        return view('site.home', $this->sharedData() + [
            'home' => Page::where('slug', 'home')->firstOrFail(),
            'about' => Page::where('slug', 'about-us')->firstOrFail(),
            'partnersPage' => Page::where('slug', 'partners')->firstOrFail(),
            'brandsPage' => Page::where('slug', 'brands')->firstOrFail(),
            'productsPage' => Page::where('slug', 'products')->firstOrFail(),
            'servicesPage' => Page::where('slug', 'services')->firstOrFail(),
            'contactPage' => Page::where('slug', 'contact-us')->firstOrFail(),
            'heroSlides' => $this->items('hero_slide'),
            'partners' => $this->items('partner')->take(4),
            'brands' => $this->items('brand')->take(4),
            'products' => $this->items('product')->take(6),
            'services' => $this->items('service')->take(2),
            'contactCta' => $this->items('contact_cta')->first(),
            'reviews' => $this->items('review')->take(2),
        ]);
    }

    public function page(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $items = match ($slug) {
            'about-us' => $this->items('gallery'),
            'partners' => $this->items('partner'),
            'brands' => $this->items('brand'),
            'products' => $this->items('product'),
            'services' => $this->items('service'),
            'contact-us' => $this->items('contact'),
            default => collect(),
        };

        return view('site.page', $this->sharedData() + [
            'page' => $page,
            'items' => $items,
            'visionItems' => $slug === 'about-us' ? $this->items('vision') : collect(),
        ]);
    }

    private function sharedData(): array
    {
        return [
            'settings' => SiteSetting::firstOrCreate([]),
            'pages' => Page::where('is_published', true)->orderBy('sort_order')->get(),
        ];
    }

    private function items(string $type)
    {
        return ContentItem::where('type', $type)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();
    }
}
