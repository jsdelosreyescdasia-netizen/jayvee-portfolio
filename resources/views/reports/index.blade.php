@extends('layouts.app', ['title' => 'Daily Reports'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Daily Reports</h1>
            <p>{{ auth()->user()->isAdmin() ? 'Review employee daily report outputs.' : 'Track issuance numbers, gathered words, phrases, citations, and remarks by report date.' }}</p>
        </div>
        @unless (auth()->user()->isAdmin())
            <div class="actions">
                <a class="btn" href="{{ route('reports.import') }}">Paste From Excel</a>
            </div>
        @endunless
    </div>

    <form class="panel" method="GET" action="{{ route('reports.index') }}" style="margin-bottom: 14px;">
        <div class="grid">
            <div class="span-2">
                <label for="search">Search</label>
                <input id="search" name="search" value="{{ $search }}" placeholder="Employee, email, issuance no., phrase, citation, remarks">
            </div>
            <div style="align-self: end;">
                <button class="btn" type="submit">Search</button>
            </div>
            <div style="align-self: end;">
                <a class="btn secondary" href="{{ route('reports.index') }}">Clear</a>
            </div>
        </div>
    </form>

    <div class="panel table-wrap">
        <div>
            {{ ($employeeRows ?? $reports)->links() }}
        </div>

        <table>
            <thead>
                @if (auth()->user()->isAdmin() && $employeeRows)
                    <tr>
                        <th>Employee</th>
                        <th>Email</th>
                        <th>Daily Reports</th>
                        <th>Actions</th>
                    </tr>
                @elseif (auth()->user()->isAdmin())
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Actions</th>
                    </tr>
                @else
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Prepared By</th>
                        <th>Issuances</th>
                        <th>Actions</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @if (auth()->user()->isAdmin() && $employeeRows)
                    @forelse ($employeeRows as $employee)
                        <tr>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td style="text-align: center;">{{ $employee->daily_reports_count }}</td>
                            <td>
                                <a class="btn secondary" href="{{ route('reports.index', ['employee_id' => $employee->id]) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted" style="text-align: center;">No employees found.</td>
                        </tr>
                    @endforelse
                @else
                    @forelse ($reports as $report)
                        @if (auth()->user()->isAdmin())
                            <tr>
                                <td>{{ $report->report_date->format('F d, Y') }}</td>
                                <td>{{ $report->user?->name ?? 'Unassigned' }}</td>
                                <td>
                                    <a class="btn secondary" href="{{ route('reports.show', $report) }}">View</a>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td>{{ $report->report_date->format('F d, Y') }}</td>
                                <td>{{ $report->title }}</td>
                                <td>{{ $report->prepared_by ?: '-' }}</td>
                                <td style="text-align: center;">{{ $report->issuances_count }}</td>
                                <td>
                                    <div class="actions">
                                        <a class="btn secondary" href="{{ route('reports.show', $report) }}">View</a>
                                        <a class="btn secondary" href="{{ route('reports.edit', $report) }}">Edit</a>
                                        <a class="btn secondary" href="{{ route('reports.export', $report) }}">CSV</a>
                                        <form method="POST" action="{{ route('reports.destroy', $report) }}" onsubmit="return confirm('Delete this daily report?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 3 : 5 }}" class="muted" style="text-align: center;">No reports yet.</td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>

@endsection
