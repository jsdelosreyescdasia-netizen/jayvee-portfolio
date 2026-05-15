@extends('layouts.app', ['title' => $report->title])

@section('content')
    @php
        $hasOtherTask = filled($report->other_task);
        $summaryColspan = $hasOtherTask ? 7 : 6;
    @endphp

    <div class="page-head">
        <div>
            <h1>{{ $report->title }} {{ $report->report_date->format('F d, Y') }}</h1>
            <p>{{ $report->prepared_by ? 'Prepared by '.$report->prepared_by : 'Issuance phrase report' }}</p>
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('reports.edit', $report) }}">Edit</a>
            <a class="btn secondary" href="{{ route('reports.export', $report) }}">Export CSV</a>
            <a class="btn secondary" href="{{ route('reports.index') }}">Back</a>
        </div>
    </div>

    <div class="panel table-wrap" style="margin-bottom: 14px;">
        <table>
            <thead>
                <tr>
                    <th class="report-title" colspan="{{ $summaryColspan }}">{{ $report->report_date->format('F d, Y') }}</th>
                </tr>
                <tr>
                    <th>title</th>
                    <th>Work done</th>
                    <th>List of Documents</th>
                    <th>No. of Links</th>
                    @if ($hasOtherTask)
                        <th>Other Task no. of Documents</th>
                    @endif
                    <th>Remarks</th>
                    <th>Time Consumed</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report->outputRows() as $row)
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $row['category'] }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ $row['work_done'] }}</td>
                        <td style="text-align: center;">{{ $row['document'] }}</td>
                        <td style="text-align: center;">{{ $row['links_count'] }}</td>
                        @if ($hasOtherTask)
                            <td style="text-align: center;">{{ $row['other_task'] }}</td>
                        @endif
                        <td>{{ $row['remarks'] }}</td>
                        <td></td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <th></th>
                    <th></th>
                    <th>{{ $report->issuances->count() }}</th>
                    <th>{{ $report->foundGatheredCount() }}</th>
                    @if ($hasOtherTask)
                        <th>{{ $report->other_task }}</th>
                    @endif
                    <th></th>
                    <th>{{ $report->time_consumed }}</th>
                </tr>
                @if ($report->meeting_note)
                    <tr class="meeting-row">
                        <th colspan="{{ $summaryColspan }}">{{ $report->meeting_note }}</th>
                    </tr>
                @endif
            </tbody>
        </table>
        <p style="margin-top: 8px;">Rows marked as not found are excluded from No. of Links.</p>
    </div>

    <div class="panel table-wrap scroll-table">
        <table>
            <thead>
                <tr>
                    <th class="report-title" colspan="4">{{ $report->title }} {{ $report->report_date->format('F d, Y') }}</th>
                </tr>
                <tr>
                    <th style="width: 24%;">Issuance No.</th>
                    <th style="width: 12%;">Date</th>
                    <th>Gathered Links</th>
                    <th style="width: 18%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report->issuances as $issuance)
                    @foreach ($issuance->gatheredLinks as $link)
                        <tr>
                            @if ($loop->first)
                                <td rowspan="{{ $issuance->gatheredLinks->count() }}">{{ $issuance->issuance_no }}</td>
                                <td rowspan="{{ $issuance->gatheredLinks->count() }}">
                                    {{ $issuance->issuance_date ? $issuance->issuance_date->format('F d, Y') : '' }}
                                </td>
                            @endif
                            <td>
                                {{ $link->title }}
                            </td>
                            <td>{{ $link->remarks }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
