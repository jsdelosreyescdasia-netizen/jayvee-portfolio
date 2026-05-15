@extends('layouts.app', ['title' => 'Weekly Report'])

@php
    $documentsTotal = collect($rows)->sum('documents_count');
    $linksTotal = collect($rows)->sum('links_count');
    $otherTasksTotal = collect($rows)->sum(fn ($row) => is_numeric($row['other_tasks']) ? (float) $row['other_tasks'] : 0);
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h1>Weekly Report</h1>
            <p>
                {{ $weekStart->format('F d, Y') }} to {{ $weekEnd->format('F d, Y') }}
                @if (auth()->user()->isAdmin() && $selectedEmployee)
                    · {{ $selectedEmployee->name }}
                @endif
            </p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('reports.weekly.export', array_filter(['week_start' => $weekStart->toDateString(), 'employee_id' => optional($selectedEmployee)->id])) }}">Export CSV</a>
            <a class="btn secondary" href="{{ route('reports.index', array_filter(['employee_id' => optional($selectedEmployee)->id])) }}">Daily Reports</a>
        </div>
    </div>

    <form class="panel" method="GET" action="{{ route('reports.weekly') }}" style="margin-bottom: 14px;">
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
                <label for="week_start">Week Date</label>
                <input id="week_start" type="date" name="week_start" value="{{ $weekStart->toDateString() }}">
            </div>
            <div style="align-self: end;">
                <button class="btn" type="submit">View Week</button>
            </div>
        </div>
    </form>

    <div class="panel table-wrap">
        <table class="weekly-table">
            <thead>
                <tr>
                    <th class="month-title" colspan="7">{{ $weekStart->format('F_Y') }}</th>
                </tr>
                <tr>
                    <th>WEEK {{ $weekNumber }}</th>
                    <th>TITLE</th>
                    <th>WORK DONE</th>
                    <th>No. of Documents</th>
                    <th>No. of Links</th>
                    <th>Other task/list of documents</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td class="date-cell">{{ $row['date_label'] }}</td>
                        <td class="center-cell">
                            @if ($row['report'])
                                <a href="{{ route('reports.show', $row['report']) }}">{{ $row['title'] }}</a>
                            @else
                                {{ $row['title'] }}
                            @endif
                        </td>
                        <td class="center-cell strong-cell">{{ $row['work_done'] }}</td>
                        <td class="center-cell">{{ $row['documents_count'] ?: '' }}</td>
                        <td class="center-cell">{{ $row['links_count'] ?: '' }}</td>
                        <td class="center-cell">{{ $row['other_tasks'] }}</td>
                        <td>{{ $row['remarks'] }}</td>
                    </tr>
                @endforeach
                <tr class="weekly-total-row">
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>{{ $documentsTotal }}</th>
                    <th>{{ $linksTotal }}</th>
                    <th>{{ $otherTasksTotal > 0 ? $otherTasksTotal : '' }}</th>
                    <th></th>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
