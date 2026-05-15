<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Models\Page;
use App\Models\SiteSetting;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'settings' => SiteSetting::firstOrCreate([]),
            'pageCount' => Page::count(),
            'itemCounts' => ContentItem::query()
                ->selectRaw('type, count(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type'),
        ]);
    }
}
