@extends('layouts.app', ['title' => 'Monthly Report'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Monthly Report</h1>
            <p>
                {{ $month->format('F Y') }}
                @if (auth()->user()->isAdmin() && $selectedEmployee)
                    · {{ $selectedEmployee->name }}
                @endif
            </p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('reports.monthly.export', array_filter(['month' => $month->format('Y-m'), 'employee_id' => optional($selectedEmployee)->id])) }}">Export CSV</a>
            <a class="btn secondary" href="{{ route('reports.weekly', array_filter(['employee_id' => optional($selectedEmployee)->id])) }}">Weekly Report</a>
        </div>
    </div>

    <form class="panel" method="GET" action="{{ route('reports.monthly') }}" style="margin-bottom: 14px;">
        <div class="grid">
            @if (auth()->user()->isAdmin() && $selectedEmployee)
                <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">
            @endif
            @if (auth()->user()->isAdmin())
                <div class="span-2">
                    <label for="employee_search">Search Employee</label>
                    <input id="employee_search" name="employee_search" value="{{ request('employee_search') }}" placeholder="Type employee name or email">
                </div>
            @endif
            <div>
                <label for="month">Month</label>
                <input id="month" type="month" name="month" value="{{ $month->format('Y-m') }}">
            </div>
            <div style="align-self: end;">
                <button class="btn" type="submit">View Month</button>
            </div>
        </div>
    </form>

    <div class="panel table-wrap">
        <table class="monthly-table">
            <thead>
                <tr>
                    <th class="month-title" colspan="8">{{ strtoupper($month->format('F Y')) }}</th>
                </tr>
                <tr>
                    <th style="width: 8%;"></th>
                    <th style="width: 12%;">Inclusive Days</th>
                    <th style="width: 18%;">Title</th>
                    <th style="width: 18%;">Task</th>
                    <th style="width: 11%;">No. of Documents</th>
                    <th style="width: 11%;">No. of Links</th>
                    <th style="width: 10%;">Other Tasks</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr @class(['leave-row' => $row['is_leave']])>
                        <td class="strong-cell">{{ $row['week_label'] }}</td>
                        <td class="center-cell strong-cell">{{ $row['date_label'] }}</td>
                        <td class="center-cell strong-cell">
                            @if ($row['report'] && $row['date_label'])
                                <a href="{{ route('reports.show', $row['report']) }}">{{ $row['title'] }}</a>
                            @else
                                {{ $row['title'] }}
                            @endif
                        </td>
                        <td class="center-cell strong-cell">{{ $row['task'] }}</td>
                        <td class="center-cell">{{ $row['documents_count'] ?: '' }}</td>
                        <td class="center-cell">{{ $row['links_count'] ?: '' }}</td>
                        <td class="center-cell">{{ $row['other_tasks'] }}</td>
                        <td>{{ $row['remarks'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="muted" style="text-align: center;">No daily reports found for this month.</td>
                    </tr>
                @endforelse

                <tr class="spacer-row"><td colspan="8"></td></tr>
                <tr>
                    <th colspan="2" style="text-align: left;">Total Working Days:</th>
                    <th class="summary-value">{{ $summary['working_days'] }}</th>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <th colspan="2" style="text-align: left;">Number of VL/SL</th>
                    <th class="summary-value">{{ $summary['leave_days'] }}</th>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <th colspan="2" style="text-align: left;">Total No. of Days Worked:</th>
                    <th class="summary-value">{{ $summary['days_worked'] }}</th>
                    <td colspan="5"></td>
                </tr>
                <tr class="spacer-row"><td colspan="8"></td></tr>
                <tr>
                    <th colspan="3" style="text-align: left;">Total Accomplishment:</th>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td></td>
                    <th style="text-align: left;">No. of Documents done:</th>
                    <th class="summary-value">{{ $summary['documents_total'] }}</th>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td></td>
                    <th style="text-align: left;">Uploading/Linking:</th>
                    <th class="summary-value">{{ $summary['links_total'] }}</th>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td></td>
                    <th style="text-align: left;">Other Tasks:</th>
                    <th class="summary-value">
                        {{ $summary['other_tasks_label'] }}
                        @if ($summary['other_tasks_total'])
                            {{ number_format($summary['other_tasks_total']) }}
                        @endif
                    </th>
                    <td colspan="5"></td>
                </tr>
                <tr class="spacer-row"><td colspan="8"></td></tr>
                <tr>
                    <td>Total Docs.</td>
                    <td></td>
                    <th class="summary-value">{{ number_format($summary['total_docs']) }}</th>
                    <td colspan="5"></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
