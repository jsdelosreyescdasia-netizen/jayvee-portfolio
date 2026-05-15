<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'nav_label',
        'title',
        'section_title',
        'summary',
        'body',
        'hero_image_path',
        'feature_image_path',
        'button_label',
        'button_url',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}
