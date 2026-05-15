@extends('layouts.app', ['title' => 'Edit Daily Report'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit Daily Report</h1>
            <p>{{ $report->report_date->format('F d, Y') }}</p>
        </div>
        <form method="POST" action="{{ route('reports.destroy', $report) }}" onsubmit="return confirm('Delete this report?')">
            @csrf
            @method('DELETE')
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>

    @include('reports.form', [
        'action' => route('reports.update', $report),
        'method' => 'PUT',
    ])
@endsection
