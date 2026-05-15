@extends('layouts.app', ['title' => 'New Daily Report'])

@section('content')
    <div class="page-head">
        <div>
            <h1>New Daily Report</h1>
            <p>Add issuance numbers and the gathered words, phrases, or citations under each one.</p>
        </div>
        <a class="btn secondary" href="{{ route('reports.import') }}">Paste From Excel</a>
    </div>

    @include('reports.form', [
        'action' => route('reports.store'),
        'method' => 'POST',
    ])
@endsection
