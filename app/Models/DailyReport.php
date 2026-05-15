<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = [
        'user_id',
        'report_date',
        'title',
        'work_done',
        'other_task',
        'other_task_title',
        'other_task_work_done',
        'prepared_by',
        'notes',
        'time_consumed',
        'meeting_note',
        'leave_duration',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'leave_duration' => 'float',
        ];
    }

    public function issuances()
    {
        return $this->hasMany(ReportIssuance::class)->orderBy('sort_order');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function foundGatheredCount(): int
    {
        return $this->issuances->sum(fn (ReportIssuance $issuance) => $issuance->foundGatheredCount());
    }

    public function outputRows(): array
    {
        $seenCategories = [];

        $rows = $this->issuances->map(function (ReportIssuance $issuance) use (&$seenCategories) {
            $category = $issuance->outputCategory();
            $showCategory = ! in_array($category, $seenCategories, true);

            if ($showCategory) {
                $seenCategories[] = $category;
            }

            return [
                'category' => $showCategory ? $category : '',
                'work_done' => $showCategory ? (filled($this->other_task) ? 'Linking' : ($this->work_done ?: 'Linking')) : '',
                'document' => $issuance->issuance_no,
                'links_count' => $issuance->foundGatheredCount(),
                'other_task' => '',
                'remarks' => '',
            ];
        });

        if (filled($this->other_task)) {
            $rows->push([
                'category' => $this->other_task_title ?: $this->title,
                'work_done' => $this->other_task_work_done ?: ($this->work_done ?: ''),
                'document' => '',
                'links_count' => '',
                'other_task' => $this->other_task,
                'remarks' => '',
            ]);
        }

        if ($this->leaveDuration() > 0) {
            $rows->push([
                'category' => 'Leave',
                'work_done' => $this->leaveLabel(),
                'document' => '',
                'links_count' => '',
                'other_task' => '',
                'remarks' => '',
            ]);
        }

        return $rows->all();
    }

    public function leaveDuration(): float
    {
        return (float) ($this->leave_duration ?? 0);
    }

    public function leaveLabel(): string
    {
        return $this->leaveDuration() >= 1 ? 'Whole Day Leave' : 'Half Day Leave';
    }
}
