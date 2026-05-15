<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString();
        $employees = $this->employeeOptions($request);
        $selectedEmployee = $this->selectedEmployee($request, allowAll: true);

        if ($request->user()->isAdmin() && ! $selectedEmployee) {
            $employeeRows = User::query()
                ->where('is_admin', false)
                ->when($search, function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->withCount('dailyReports')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            $reports = DailyReport::query()->whereRaw('1 = 0')->paginate(10);

            return view('reports.index', compact('reports', 'search', 'employees', 'selectedEmployee', 'employeeRows'));
        }

        $employeeRows = null;

        $reports = DailyReport::query()
            ->when(! $request->user()->isAdmin(), fn ($query) => $query->whereBelongsTo($request->user()))
            ->when($request->user()->isAdmin() && $selectedEmployee, fn ($query) => $query->whereBelongsTo($selectedEmployee))
            ->with('user')
            ->withCount('issuances')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('prepared_by', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('issuances', function ($query) use ($search) {
                            $query->where('issuance_no', 'like', "%{$search}%")
                                ->orWhereHas('gatheredLinks', function ($query) use ($search) {
                                    $query->where('title', 'like', "%{$search}%")
                                        ->orWhere('remarks', 'like', "%{$search}%");
                                });
                        });
                    });
            })
            ->latest('report_date')
            ->paginate(10)
            ->withQueryString();

        return view('reports.index', compact('reports', 'search', 'employees', 'selectedEmployee', 'employeeRows'));
    }

    public function create()
    {
        return redirect()->route('reports.import');
    }

    public function import()
    {
        $report = new DailyReport([
            'report_date' => today(),
            'title' => 'Latest Audit',
            'time_consumed' => '7.5 hrs',
        ]);

        return view('reports.import', compact('report'));
    }

    public function weekly(Request $request)
    {
        $employees = $this->employeeOptions($request);
        $selectedEmployee = $this->selectedEmployee($request);
        $weekStart = $this->requestedWeekStart($request, $selectedEmployee);
        $weekEnd = $weekStart->addDays(6);
        $rows = $selectedEmployee ? $this->weeklyRows($weekStart, $selectedEmployee) : [];
        $weekNumber = $this->requestedWeekNumber($request, $weekStart);

        return view('reports.weekly', compact('rows', 'weekStart', 'weekEnd', 'weekNumber', 'employees', 'selectedEmployee'));
    }

    public function monthly(Request $request)
    {
        $employees = $this->employeeOptions($request);
        $selectedEmployee = $this->selectedEmployee($request);
        $month = $this->requestedMonth($request);
        $rows = $selectedEmployee ? $this->monthlyRows($month, $selectedEmployee) : [];
        $summary = $this->monthlySummary($rows);

        return view('reports.monthly', compact('month', 'rows', 'summary', 'employees', 'selectedEmployee'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedReport($request);

        $report = DB::transaction(function () use ($validated) {
            $report = DailyReport::create($validated['report']);
            $this->syncIssuances($report, $validated['issuances']);

            return $report;
        });

        return redirect()->route('reports.show', $report)->with('success', 'Daily report created.');
    }

    public function storeImport(Request $request)
    {
        $validated = $request->validate([
            'report_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'work_done' => ['nullable', 'string', 'max:255'],
            'other_task' => ['nullable', 'string', 'max:255'],
            'other_task_title' => ['nullable', 'string', 'max:255'],
            'other_task_work_done' => ['nullable', 'string', 'max:255'],
            'time_consumed' => ['nullable', 'string', 'max:255'],
            'meeting_note' => ['nullable', 'string', 'max:255'],
            'leave_duration' => ['nullable', 'numeric', 'in:0.5,1'],
            'pasted_rows' => ['nullable', 'string'],
            'linking_sections' => ['nullable', 'array'],
            'linking_sections.*.title' => ['nullable', 'string', 'max:255'],
            'linking_sections.*.rows' => ['nullable', 'string'],
            'other_task_rows' => ['nullable', 'string'],
        ]);

        $this->ensureReportDateIsAvailable($validated['report_date']);

        $issuances = $this->parseLinkingSections($validated);
        $otherTaskCount = filled($validated['other_task_rows'] ?? null)
            ? $this->countOtherTaskRows($validated['other_task_rows'])
            : 0;

        if ($issuances === [] && $otherTaskCount === 0 && ! $this->hasLeave($validated)) {
            return back()
                ->withErrors(['pasted_rows' => 'Paste Linking rows, Other Task rows, or select a leave duration before importing.'])
                ->withInput();
        }

        if ($otherTaskCount > 0) {
            $validated['other_task'] = (string) $otherTaskCount;
        }

        $report = DB::transaction(function () use ($validated, $issuances) {
            $report = DailyReport::create($this->normalizeReportData($validated));
            $this->syncIssuances($report, $issuances);

            return $report;
        });

        $count = collect($issuances)->sum(fn ($issuance) => count($issuance['links']));
        $message = 'Imported ' . $count . ' gathered entries under ' . count($issuances) . ' issuance number(s).';

        if ($otherTaskCount > 0) {
            $message .= ' Counted ' . $otherTaskCount . ' other task item(s).';
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', $message);
    }

    public function show(DailyReport $report)
    {
        $this->ensureReportCanBeViewed($report);
        $report->load('issuances.gatheredLinks');

        return view('reports.show', compact('report'));
    }

    public function edit(DailyReport $report)
    {
        $this->ensureReportBelongsToUser($report);
        $report->load('issuances.gatheredLinks');

        return view('reports.edit', compact('report'));
    }

    public function update(Request $request, DailyReport $report)
    {
        $this->ensureReportBelongsToUser($report);
        $validated = $this->validatedReport($request, $report);

        DB::transaction(function () use ($report, $validated) {
            $report->update($validated['report']);
            $report->issuances()->delete();
            $this->syncIssuances($report, $validated['issuances']);
        });

        return redirect()->route('reports.show', $report)->with('success', 'Daily report updated.');
    }

    public function destroy(DailyReport $report)
    {
        $this->ensureReportBelongsToUser($report);
        $report->delete();

        return redirect()->route('reports.index')->with('success', 'Daily report deleted.');
    }

    public function export(DailyReport $report): StreamedResponse
    {
        $this->ensureReportCanBeViewed($report);
        $report->load('issuances.gatheredLinks');
        $filename = 'daily-report-' . $report->report_date->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $file = fopen('php://output', 'w');
            $hasOtherTask = filled($report->other_task);

            fputcsv($file, [$report->report_date->format('F d, Y')]);
            fputcsv($file, array_filter([
                'title',
                'Work done',
                'List of Documents',
                'No. of Links',
                $hasOtherTask ? 'Other Task no. of Documents' : null,
                'Remarks',
                'Time Consumed',
            ]));

            foreach ($report->outputRows() as $row) {
                fputcsv($file, array_filter([
                    $row['category'],
                    $this->escapeCsvValue($row['work_done']),
                    $this->escapeCsvValue($row['document']),
                    $row['links_count'],
                    $hasOtherTask ? $this->escapeCsvValue($row['other_task']) : null,
                    $this->escapeCsvValue($row['remarks']),
                    '',
                ], fn ($value) => $value !== null));
            }

            fputcsv($file, array_filter([
                '',
                '',
                $report->issuances->count(),
                $report->foundGatheredCount(),
                $hasOtherTask ? $this->escapeCsvValue($report->other_task) : null,
                '',
                $this->escapeCsvValue($report->time_consumed),
            ], fn ($value) => $value !== null));
            if ($report->meeting_note) {
                fputcsv($file, $hasOtherTask
                    ? ['', '', $this->escapeCsvValue($report->meeting_note), '', '', '', '']
                    : ['', '', $this->escapeCsvValue($report->meeting_note), '', '', '']);
            }
            fputcsv($file, []);
            fputcsv($file, [$this->escapeCsvValue($report->title . ' ' . $report->report_date->format('F d, Y'))]);
            fputcsv($file, ['Issuance No.', 'Date', 'Gathered Links', 'Remarks']);

            foreach ($report->issuances as $issuance) {
                foreach ($issuance->gatheredLinks as $index => $link) {
                    fputcsv($file, [
                        $index === 0 ? $issuance->issuance_no : '',
                        $index === 0 && $issuance->issuance_date ? $issuance->issuance_date->format('F d, Y') : '',
                        $this->escapeCsvValue($link->title),
                        $this->escapeCsvValue($link->remarks),
                    ]);
                }
            }

            fclose($file);
        }, $filename);
    }

    public function exportWeekly(Request $request): StreamedResponse
    {
        $selectedEmployee = $this->selectedEmployee($request);
        $weekStart = $this->requestedWeekStart($request, $selectedEmployee);
        $weekEnd = $weekStart->addDays(6);
        $rows = $selectedEmployee ? $this->weeklyRows($weekStart, $selectedEmployee) : [];
        $weekNumber = $this->requestedWeekNumber($request, $weekStart);
        $filename = 'weekly-report-' . $weekStart->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows, $weekNumber) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['WEEK ' . $weekNumber, 'TITLE', 'WORK DONE', 'No. of Documents', 'No. of Links', 'Other task/list of documents', 'Remarks']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row['date_label'],
                    $this->escapeCsvValue($row['title']),
                    $this->escapeCsvValue($row['work_done']),
                    $row['documents_count'] ?: '',
                    $row['links_count'] ?: '',
                    $this->escapeCsvValue($row['other_tasks']),
                    $this->escapeCsvValue($row['remarks']),
                ]);
            }

            fputcsv($file, [
                '',
                '',
                '',
                collect($rows)->sum('documents_count'),
                collect($rows)->sum('links_count'),
                '',
                '',
            ]);

            fclose($file);
        }, $filename);
    }

    public function exportMonthly(Request $request): StreamedResponse
    {
        $selectedEmployee = $this->selectedEmployee($request);
        $month = $this->requestedMonth($request);
        $rows = $selectedEmployee ? $this->monthlyRows($month, $selectedEmployee) : [];
        $summary = $this->monthlySummary($rows);
        $filename = 'monthly-report-' . $month->format('Y-m') . '.csv';

        return response()->streamDownload(function () use ($month, $rows, $summary) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [$month->format('F Y')]);
            fputcsv($file, ['', 'Inclusive Days', 'Title', 'Task', 'No. of Documents', 'No. of Links', 'Other Tasks', 'Remarks']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row['week_label'],
                    $row['date_label'],
                    $this->escapeCsvValue($row['title']),
                    $this->escapeCsvValue($row['task']),
                    $row['documents_count'] ?: '',
                    $row['links_count'] ?: '',
                    $this->escapeCsvValue($row['other_tasks']),
                    $this->escapeCsvValue($row['remarks']),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Total Working Days:', '', $summary['working_days']]);
            fputcsv($file, ['Number of VL/SL', '', $summary['leave_days']]);
            fputcsv($file, ['Total No. of Days Worked:', '', $summary['days_worked']]);
            fputcsv($file, []);
            fputcsv($file, ['Total Accomplishment:']);
            fputcsv($file, ['', 'No. of Documents done:', $summary['documents_total']]);
            fputcsv($file, ['', 'Uploading/Linking:', $summary['links_total']]);
            fputcsv($file, ['', 'Other Tasks:', $this->escapeCsvValue($summary['other_tasks_label']), $summary['other_tasks_total'] ?: '']);
            fputcsv($file, []);
            fputcsv($file, ['Total Docs.', '', $summary['total_docs']]);

            fclose($file);
        }, $filename);
    }

    private function validatedReport(Request $request, ?DailyReport $report = null): array
    {
        $request->merge([
            'issuances' => $this->cleanIssuanceInput($request->input('issuances', [])),
        ]);

        $validated = $request->validate([
            'report_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'work_done' => ['nullable', 'string', 'max:255'],
            'other_task' => ['nullable', 'string', 'max:255'],
            'other_task_title' => ['nullable', 'string', 'max:255'],
            'other_task_work_done' => ['nullable', 'string', 'max:255'],
            'time_consumed' => ['nullable', 'string', 'max:255'],
            'meeting_note' => ['nullable', 'string', 'max:255'],
            'leave_duration' => ['nullable', 'numeric', 'in:0.5,1'],
            'issuances' => ['nullable', 'array'],
            'issuances.*.issuance_no' => ['required', 'string', 'max:255'],
            'issuances.*.issuance_date' => ['nullable', 'date'],
            'issuances.*.links' => ['required', 'array', 'min:1'],
            'issuances.*.links.*.title' => ['required', 'string'],
            'issuances.*.links.*.remarks' => ['nullable', 'string'],
        ]);

        $this->ensureReportDateIsAvailable($validated['report_date'], $report);

        $issuances = $validated['issuances'] ?? [];

        if ($issuances === [] && ! filled($validated['other_task'] ?? null) && ! $this->hasLeave($validated)) {
            throw ValidationException::withMessages([
                'issuances' => 'Add at least one issuance, add an other task count, or select a leave duration.',
            ]);
        }

        return [
            'report' => $this->normalizeReportData($validated),
            'issuances' => $issuances,
        ];
    }

    private function selectedEmployee(Request $request, bool $allowAll = false): ?User
    {
        if (! $request->user()->isAdmin()) {
            return $request->user();
        }

        $employeeSearch = trim($request->string('employee_search')->toString());

        if ($employeeSearch !== '') {
            return User::query()
                ->where('is_admin', false)
                ->where(function ($query) use ($employeeSearch) {
                    $query->where('name', 'like', "%{$employeeSearch}%")
                        ->orWhere('email', 'like', "%{$employeeSearch}%");
                })
                ->orderBy('name')
                ->first();
        }

        if ($allowAll && ! $request->filled('employee_id')) {
            return null;
        }

        if ($request->filled('employee_id')) {
            return User::query()
                ->whereKey($request->integer('employee_id'))
                ->where('is_admin', false)
                ->first();
        }

        return User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->first();
    }

    private function employeeOptions(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            return collect();
        }

        return User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function requestedWeekStart(Request $request, ?User $employee): CarbonImmutable
    {
        $date = $request->date('week_start')
            ?? ($employee ? DailyReport::query()->whereBelongsTo($employee)->latest('report_date')->value('report_date') : null)
            ?? today();

        return CarbonImmutable::parse($date)->startOfWeek();
    }

    private function requestedWeekNumber(Request $request, CarbonImmutable $weekStart): int
    {
        return $this->monthWeekNumber($weekStart);
    }

    private function requestedMonth(Request $request): CarbonImmutable
    {
        $month = $request->string('month')->toString();

        if ($month === '') {
            return CarbonImmutable::now()->startOfMonth();
        }

        return CarbonImmutable::parse($month . '-01')->startOfMonth();
    }

    private function weeklyRows(CarbonImmutable $weekStart, User $employee): array
    {
        $weekEnd = $weekStart->addDays(6);
        $reports = DailyReport::query()
            ->whereBelongsTo($employee)
            ->with('issuances.gatheredLinks')
            ->whereDate('report_date', '>=', $weekStart->toDateString())
            ->whereDate('report_date', '<=', $weekEnd->toDateString())
            ->get()
            ->keyBy(fn (DailyReport $report) => $report->report_date->toDateString());

        return collect(range(0, 6))->flatMap(function (int $offset) use ($weekStart, $reports) {
            $date = $weekStart->addDays($offset);
            $report = $reports->get($date->toDateString());

            if (! $report) {
                return [[
                    'date' => $date,
                    'date_label' => $date->format('M d/Y'),
                    'title' => '',
                    'work_done' => '',
                    'documents_count' => 0,
                    'links_count' => 0,
                    'other_tasks' => '',
                    'remarks' => '',
                    'report' => null,
                ]];
            }

            $hasLinking = $report->issuances->isNotEmpty();
            $hasOtherTask = filled($report->other_task);
            $hasLeave = $report->leaveDuration() > 0;
            $rows = [];

            if ($hasLinking) {
                $sectionGroups = $this->linkingSectionGroups($report);

                foreach ($sectionGroups as $sectionIndex => $section) {
                    $rows[] = [
                        'date' => $date,
                        'date_label' => $sectionIndex === 0 ? $date->format('M d/Y') : '',
                        'title' => $section['title'],
                        'work_done' => $hasOtherTask || $this->hasLinkingSections($report) ? 'Linking' : $report->work_done,
                        'documents_count' => $section['documents_count'],
                        'links_count' => $section['links_count'],
                        'other_tasks' => '',
                        'remarks' => $sectionIndex === 0 ? ($report->meeting_note ?? '') : '',
                        'report' => $report,
                    ];
                }
            } elseif (! $hasOtherTask && ! $hasLeave) {
                $rows[] = [
                    'date' => $date,
                    'date_label' => $date->format('M d/Y'),
                    'title' => $report->title,
                    'work_done' => $report->work_done,
                    'documents_count' => 0,
                    'links_count' => 0,
                    'other_tasks' => '',
                    'remarks' => $report->meeting_note ?? '',
                    'report' => $report,
                ];
            }

            if ($hasOtherTask) {
                $rows[] = [
                    'date' => $date,
                    'date_label' => $rows === [] ? $date->format('M d/Y') : '',
                    'title' => $report->other_task_title ?: $report->title,
                    'work_done' => $report->other_task_work_done ?: $report->work_done,
                    'documents_count' => 0,
                    'links_count' => 0,
                    'other_tasks' => $report->other_task,
                    'remarks' => '',
                    'report' => $report,
                ];
            }

            if ($hasLeave) {
                $rows[] = [
                    'date' => $date,
                    'date_label' => $rows === [] ? $date->format('M d/Y') : '',
                    'title' => 'Leave',
                    'work_done' => $report->leaveLabel(),
                    'documents_count' => 0,
                    'links_count' => 0,
                    'other_tasks' => '',
                    'remarks' => $this->formatLeaveDuration($report->leaveDuration()),
                    'report' => $report,
                    'is_leave_row' => true,
                ];
            }

            return $rows;
        })->filter(fn (array $row) => $row['date']->isWeekday() || $row['report'])->values()->all();
    }

    private function linkingTitle(DailyReport $report): string
    {
        return $report->issuances
            ->map(fn ($issuance) => $issuance->outputCategory())
            ->unique()
            ->implode(', ');
    }

    private function linkingSectionGroups(DailyReport $report): array
    {
        if (! $this->hasLinkingSections($report)) {
            return [[
                'title' => $report->title,
                'documents_count' => $report->issuances->count(),
                'links_count' => $report->foundGatheredCount(),
            ]];
        }

        return $report->issuances
            ->groupBy(fn ($issuance) => $issuance->outputCategory())
            ->map(fn ($issuances, $title) => [
                'title' => $title,
                'documents_count' => $issuances->count(),
                'links_count' => $issuances->sum(fn ($issuance) => $issuance->foundGatheredCount()),
            ])
            ->values()
            ->all();
    }

    private function hasLinkingSections(DailyReport $report): bool
    {
        return $report->issuances->contains(fn ($issuance) => filled($issuance->section_title));
    }

    private function monthWeekNumber(CarbonImmutable $weekStart): int
    {
        $firstDay = $weekStart->startOfMonth();
        $firstWeekStart = $firstDay->startOfWeek();

        return intdiv((int) $firstWeekStart->diffInDays($weekStart), 7) + 1;
    }

    private function monthlyRows(CarbonImmutable $month, User $employee): array
    {
        $monthEnd = $month->endOfMonth();
        $firstWeekStart = $month->startOfWeek();
        $lastWeekStart = $monthEnd->startOfWeek();
        $weeks = collect();

        for ($weekStart = $firstWeekStart; $weekStart <= $lastWeekStart; $weekStart = $weekStart->addWeek()) {
            $weeks->push($weekStart);
        }

        return $weeks->flatMap(function (CarbonImmutable $weekStart) use ($month, $employee) {
            $weekNumber = $this->monthWeekNumberForMonth($weekStart, $month);
            $weekHasLabel = false;

            return collect($this->weeklyRows($weekStart, $employee))
                ->filter(fn (array $row) => $row['report'] && $row['date']->isSameMonth($month))
                ->map(function (array $row) use (&$weekHasLabel, $weekNumber) {
                    $report = $row['report'];
                    $isLeave = $this->isLeaveReport($report);
                    $isLeaveRow = (bool) ($row['is_leave_row'] ?? false);
                    $weekLabel = $weekHasLabel ? '' : 'Week '.$weekNumber;
                    $weekHasLabel = true;

                    return [
                        'week_number' => $weekNumber,
                        'week_label' => $weekLabel,
                        'date' => $row['date'],
                        'date_label' => $row['date_label'] === '' ? '' : $row['date']->format('F/d/Y'),
                        'title' => $row['title'],
                        'task' => $row['work_done'],
                        'documents_count' => ($row['is_leave_row'] ?? false) ? 0 : (int) $row['documents_count'],
                        'links_count' => ($row['is_leave_row'] ?? false) ? 0 : (int) $row['links_count'],
                        'other_tasks' => $row['other_tasks'],
                        'remarks' => $row['remarks'],
                        'is_leave' => $report->leaveDuration() > 0 ? $isLeaveRow : $isLeave,
                        'leave_duration' => $isLeaveRow ? $report->leaveDuration() : 0,
                        'report' => $report,
                    ];
                });
        })->values()->all();
    }

    private function monthlySummary(array $rows): array
    {
        $reportDays = collect($rows)
            ->filter(fn (array $row) => $row['date_label'] !== '')
            ->unique(fn (array $row) => $row['date']->toDateString());
        $leaveDays = collect($rows)->sum('leave_duration');
        $otherTasksTotal = collect($rows)->sum(fn (array $row) => is_numeric($row['other_tasks']) ? (float) $row['other_tasks'] : 0);
        $otherTasksLabel = collect($rows)
            ->reject(fn (array $row) => $row['is_leave'])
            ->pluck('task')
            ->filter(fn ($task) => $task && ! str_contains(strtolower((string) $task), 'link'))
            ->unique()
            ->implode(', ');

        return [
            'working_days' => $reportDays->count(),
            'leave_days' => $this->formatDecimal($leaveDays),
            'days_worked' => $this->formatDecimal(max(0, $reportDays->count() - $leaveDays)),
            'documents_total' => collect($rows)->sum('documents_count'),
            'links_total' => collect($rows)->sum('links_count'),
            'other_tasks_total' => $otherTasksTotal,
            'other_tasks_label' => $otherTasksLabel,
            'total_docs' => collect($rows)->sum('documents_count') + collect($rows)->sum('links_count') + $otherTasksTotal,
        ];
    }

    private function monthWeekNumberForMonth(CarbonImmutable $date, CarbonImmutable $month): int
    {
        $firstWeekStart = $month->startOfMonth()->startOfWeek();

        return intdiv((int) $firstWeekStart->diffInDays($date->startOfWeek()), 7) + 1;
    }

    private function isLeaveReport(DailyReport $report): bool
    {
        if ($report->leaveDuration() > 0) {
            return true;
        }

        $text = strtolower($report->title.' '.$report->work_done);

        return str_contains($text, 'leave') || str_contains($text, 'vl') || str_contains($text, 'sl');
    }

    private function normalizeReportData(array $validated): array
    {
        $report = collect($validated)->only([
            'report_date',
            'title',
            'work_done',
            'other_task',
            'other_task_title',
            'other_task_work_done',
            'time_consumed',
            'meeting_note',
            'leave_duration',
        ])->all();
        $report['user_id'] = auth()->id();
        $report['prepared_by'] = auth()->user()->name;
        $report['notes'] = null;
        $report['work_done'] = ($report['work_done'] ?? null) ?: 'Linking';
        $report['time_consumed'] = ($report['time_consumed'] ?? null) ?: '7.5 hrs';
        $report['leave_duration'] = $report['leave_duration'] ?? null;

        return $report;
    }

    private function hasLeave(array $validated): bool
    {
        return (float) ($validated['leave_duration'] ?? 0) > 0;
    }

    private function formatLeaveDuration(float $duration): string
    {
        return $duration >= 1 ? '1 day' : '0.5 day';
    }

    private function formatDecimal(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
    }

    private function ensureReportBelongsToUser(DailyReport $report): void
    {
        abort_unless($report->user_id === auth()->id(), 404);
    }

    private function ensureReportCanBeViewed(DailyReport $report): void
    {
        abort_unless(auth()->user()->isAdmin() || $report->user_id === auth()->id(), 404);
    }

    private function syncIssuances(DailyReport $report, array $issuances): void
    {
        foreach ($issuances as $issuanceIndex => $issuanceData) {
            $issuance = $report->issuances()->create([
                'section_title' => $issuanceData['section_title'] ?? null,
                'issuance_no' => $issuanceData['issuance_no'],
                'issuance_date' => $issuanceData['issuance_date'] ?? null,
                'sort_order' => $issuanceIndex,
            ]);

            foreach ($issuanceData['links'] as $linkIndex => $linkData) {
                $issuance->gatheredLinks()->create([
                    'title' => $linkData['title'],
                    'url' => null,
                    'remarks' => $linkData['remarks'] ?? null,
                    'sort_order' => $linkIndex,
                ]);
            }
        }
    }

    private function ensureReportDateIsAvailable(string $date, ?DailyReport $ignoreReport = null): void
    {
        $exists = DailyReport::query()
            ->whereBelongsTo(auth()->user())
            ->whereDate('report_date', $date)
            ->when($ignoreReport, fn ($query) => $query->whereKeyNot($ignoreReport->getKey()))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'report_date' => 'A report already exists for this date. Choose a different report date, or edit the existing report.',
            ]);
        }
    }

    private function parsePastedRows(string $pastedRows): array
    {
        $issuances = [];
        $currentKey = null;
        $currentDate = null;

        foreach (preg_split("/\r\n|\n|\r/", trim($pastedRows)) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $columns = str_getcsv($line, "\t");

            if (count($columns) < 3) {
                $columns = str_getcsv($line);
            }

            $columns = array_pad(array_map(fn ($value) => trim((string) $value), $columns), 4, '');
            [$issuanceNo, $date, $gatheredText, $remarks] = array_slice($columns, 0, 4);

            if ($this->isHeaderOrTitleRow($issuanceNo, $date, $gatheredText, $remarks)) {
                continue;
            }

            if ($issuanceNo !== '') {
                $currentKey = $issuanceNo;
                $currentDate = $this->normalizeDate($date);

                $issuances[$currentKey] ??= [
                    'issuance_no' => $currentKey,
                    'issuance_date' => $currentDate,
                    'links' => [],
                ];

                if ($issuances[$currentKey]['issuance_date'] === null && $currentDate !== null) {
                    $issuances[$currentKey]['issuance_date'] = $currentDate;
                }
            } elseif ($date !== '' && $currentKey !== null && $currentDate === null) {
                $currentDate = $this->normalizeDate($date);
                $issuances[$currentKey]['issuance_date'] = $currentDate;
            }

            if ($currentKey === null || $gatheredText === '') {
                continue;
            }

            $issuances[$currentKey]['links'][] = [
                'title' => $gatheredText,
                'remarks' => $remarks,
            ];
        }

        return array_values(array_filter($issuances, fn ($issuance) => count($issuance['links']) > 0));
    }

    private function parseLinkingSections(array $validated): array
    {
        $sections = collect($validated['linking_sections'] ?? [])
            ->filter(fn ($section) => filled($section['rows'] ?? null))
            ->values();

        if ($sections->isEmpty() && filled($validated['pasted_rows'] ?? null)) {
            $sections = collect([[
                'title' => $validated['title'] ?? null,
                'rows' => $validated['pasted_rows'],
            ]]);
        }

        return $sections
            ->flatMap(function (array $section) {
                $sectionTitle = trim((string) ($section['title'] ?? ''));

                return collect($this->parsePastedRows((string) ($section['rows'] ?? '')))
                    ->map(function (array $issuance) use ($sectionTitle) {
                        $issuance['section_title'] = $sectionTitle !== '' ? $sectionTitle : null;

                        return $issuance;
                    });
            })
            ->values()
            ->all();
    }

    private function cleanIssuanceInput(array $issuances): array
    {
        return collect($issuances)
            ->map(function ($issuance) {
                $links = collect($issuance['links'] ?? [])
                    ->filter(fn ($link) => filled($link['title'] ?? null) || filled($link['remarks'] ?? null))
                    ->values()
                    ->all();

                return [
                    'issuance_no' => trim((string) ($issuance['issuance_no'] ?? '')),
                    'issuance_date' => $issuance['issuance_date'] ?? null,
                    'links' => $links,
                ];
            })
            ->filter(fn ($issuance) => filled($issuance['issuance_no']) || count($issuance['links']) > 0)
            ->values()
            ->all();
    }

    private function countOtherTaskRows(string $pastedRows): int
    {
        return collect(preg_split("/\r\n|\n|\r/", trim($pastedRows)))
            ->map(fn ($line) => trim((string) $line))
            ->filter(fn ($line) => $line !== '')
            ->filter(function (string $line) {
                $columns = str_getcsv($line, "\t");

                if (count($columns) < 2) {
                    $columns = str_getcsv($line);
                }

                $columns = array_pad(array_map(fn ($value) => trim((string) $value), $columns), 4, '');
                $rowText = strtolower(implode(' ', $columns));

                if (str_contains($rowText, 'issuance number') || str_contains($rowText, 'title date remarks')) {
                    return false;
                }

                return collect($columns)->contains(fn ($value) => $value !== '' && $value !== '-');
            })
            ->count();
    }

    private function isHeaderOrTitleRow(string $issuanceNo, string $date, string $gatheredText, string $remarks): bool
    {
        $rowText = strtolower(trim($issuanceNo . ' ' . $date . ' ' . $gatheredText . ' ' . $remarks));

        if ($rowText === '') {
            return true;
        }

        return str_contains($rowText, 'issuance no')
            || str_contains($rowText, 'gathered links')
            || str_contains($rowText, 'gathered words')
            || str_starts_with($rowText, 'latest audit');
    }

    private function normalizeDate(?string $date): ?string
    {
        $date = trim((string) $date);

        if ($date === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function escapeCsvValue($value): string
    {
        $value = (string) ($value ?? '');

        if (preg_match('/^\s*[=+\-@]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
