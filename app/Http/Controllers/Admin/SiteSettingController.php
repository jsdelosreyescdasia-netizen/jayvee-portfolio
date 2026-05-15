<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::firstOrCreate([]),
        ]);
    }

    public function update(Request $request)
    {
        $settings = SiteSetting::firstOrCreate([]);
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:160'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:80'],
            'mobile' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string', 'max:255'],
            'map_embed_url' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        unset($data['logo']);

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('cms/site', 'public');
        }

        $settings->update($data);

        return to_route('admin.settings.edit')->with('status', 'Site settings updated.');
    }
}
