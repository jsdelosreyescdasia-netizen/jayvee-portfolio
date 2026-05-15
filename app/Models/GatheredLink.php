<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatheredLink extends Model
{
    protected $fillable = [
        'report_issuance_id',
        'title',
        'url',
        'remarks',
        'sort_order',
    ];

    public function reportIssuance()
    {
        return $this->belongsTo(ReportIssuance::class);
    }
}
