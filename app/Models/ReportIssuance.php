<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportIssuance extends Model
{
    protected $fillable = [
        'daily_report_id',
        'section_title',
        'issuance_no',
        'issuance_date',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'issuance_date' => 'date',
        ];
    }

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function gatheredLinks()
    {
        return $this->hasMany(GatheredLink::class)->orderBy('sort_order');
    }

    public function foundGatheredCount(): int
    {
        return $this->gatheredLinks
            ->reject(fn (GatheredLink $link) => str_contains(strtolower((string) $link->remarks), 'not found'))
            ->count();
    }

    public function outputCategory(): string
    {
        if (filled($this->section_title)) {
            return $this->section_title;
        }

        if (str_starts_with(strtolower($this->issuance_no), 'sec')) {
            return 'Sec';
        }

        return 'Audit';
    }
}
