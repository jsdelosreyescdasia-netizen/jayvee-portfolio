<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentItem extends Model
{
    public const TYPES = ['hero_slide', 'partner', 'brand', 'product', 'service', 'contact', 'vision', 'gallery', 'contact_cta', 'review'];

    protected $fillable = [
        'type',
        'title',
        'subtitle',
        'description',
        'image_path',
        'url',
        'sort_order',
        'is_published',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
