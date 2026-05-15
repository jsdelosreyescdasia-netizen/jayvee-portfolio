<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class PublicStorageController extends Controller
{
    public function __invoke(string $path)
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }
}
