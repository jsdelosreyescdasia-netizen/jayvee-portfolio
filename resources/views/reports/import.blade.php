@extends('layouts.app', ['title' => 'Paste From Excel'])

@section('content')
    @php
        $oldSections = old('linking_sections');
        $linkingSections = is_array($oldSections) && count($oldSections)
            ? $oldSections
            : [[
                'title' => old('title', $report->title),
                'rows' => old('pasted_rows', ''),
            ]];
    @endphp

    <div class="page-head">
        <div>
            <h1>Paste From Excel</h1>
            <p>Paste one or more Linking sections, Other Task rows, or both. Other Task rows are counted automatically.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('reports.import.store') }}">
        @csrf

        <div class="panel">
            <div class="grid">
                <div>
                    <label for="report_date">Report Date</label>
                    <input id="report_date" type="date" name="report_date" value="{{ old('report_date', optional($report->report_date)->format('Y-m-d')) }}" required>
                </div>
                <div>
                    <label for="title">Report Title</label>
                    <input id="title" name="title" value="{{ old('title', $report->title) }}" required>
                </div>
                <div>
                    <label for="work_done">Work Done</label>
                    <input id="work_done" name="work_done" value="{{ old('work_done', $report->work_done ?: 'Linking') }}">
                </div>
                <div>
                    <label for="other_task_title">Other Task Title</label>
                    <input id="other_task_title" name="other_task_title" value="{{ old('other_task_title', $report->other_task_title) }}" placeholder="ENR">
                </div>
                <div>
                    <label for="other_task_work_done">Other Task Work Done</label>
                    <input id="other_task_work_done" name="other_task_work_done" value="{{ old('other_task_work_done', $report->other_task_work_done) }}" placeholder="Checking Documents from Premium for Fixing format">
                </div>
                <div>
                    <label for="time_consumed">Time Consumed</label>
                    <input id="time_consumed" name="time_consumed" value="{{ old('time_consumed', $report->time_consumed) }}" placeholder="7.5 hrs">
                </div>
                <div>
                    <label for="leave_duration">Leave</label>
                    <select id="leave_duration" name="leave_duration">
                        <option value="">No Leave</option>
                        <option value="0.5" @selected((string) old('leave_duration', $report->leave_duration) === '0.5')>Half Day</option>
                        <option value="1" @selected((string) old('leave_duration', $report->leave_duration) === '1' || (string) old('leave_duration', $report->leave_duration) === '1.0')>Whole Day</option>
                    </select>
                </div>
                <div class="span-4">
                    <label for="meeting_note">Meeting</label>
                    <input id="meeting_note" name="meeting_note" value="{{ old('meeting_note', $report->meeting_note) }}" placeholder="WPD Meeting (1hr) March 30, 2026">
                </div>
                <div class="span-2">
                    <label for="prepared_by">Prepared By</label>
                    <input id="prepared_by" value="{{ $report->prepared_by ?: auth()->user()->name }}" readonly>
                </div>
                <div class="span-4">
                    <div class="page-head" style="margin-bottom: 10px;">
                        <h2>Linking Sections</h2>
                        <button class="btn secondary" type="button" onclick="addLinkingSection()">Add Section</button>
                    </div>
                    <div id="linking-sections"></div>
                </div>
                <div class="span-4">
                    <label for="other_task_rows">Other Task Excel Rows</label>
                    <textarea id="other_task_rows" name="other_task_rows" class="paste-box" placeholder="Issuance number&#9;Title&#9;date&#9;remarks&#10;DENR Memorandum&#9;Compliance to Rules and Regulations&#9;01/22/2024&#9;table&#10;DENR Administrative Order No. 2024-09&#9;Guidelines on Certification&#9;09/30/2024&#9;">{{ old('other_task_rows') }}</textarea>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-top: 14px;">
            <strong>How it reads your paste:</strong>
            <p style="margin-top: 6px;">Each Linking section becomes its own title in the reports, such as Audit, SEC, or Insurance. Other Task rows are counted separately from Linking.</p>
        </div>

        <div class="actions" style="margin-top: 16px;">
            <button class="btn" type="submit">Import Report</button>
            <a class="btn secondary" href="{{ route('reports.index') }}">Cancel</a>
        </div>
    </form>

    <template id="linking-section-template">
        <section class="form-section linking-section">
            <div class="page-head" style="margin-bottom: 12px;">
                <h2>Linking Section</h2>
                <button class="btn danger" type="button" onclick="removeLinkingSection(this)">Remove</button>
            </div>
            <div class="grid">
                <div class="span-2">
                    <label>Section Title</label>
                    <input data-name="title" placeholder="Audit, SEC, Insurance">
                </div>
                <div class="span-4">
                    <label>Excel Rows</label>
                    <textarea data-name="rows" class="paste-box" placeholder="Issuance No.&#9;Date&#9;Gathered Links&#9;Remarks&#10;COA Decision No. 2023-357&#9;March 22, 2023&#9;Presidential Decree (PD) No. 1445&#9;&#10;&#9;&#9;2009 Revised Rules of Procedure of the COA&#9;"></textarea>
                </div>
            </div>
        </section>
    </template>

    <script>
        const initialLinkingSections = @json($linkingSections);

        function addLinkingSection(data = null) {
            const container = document.getElementById('linking-sections');
            const template = document.getElementById('linking-section-template').content.cloneNode(true);
            const section = template.querySelector('.linking-section');
            container.appendChild(section);

            section.querySelector('[data-name="title"]').value = data?.title ?? '';
            section.querySelector('[data-name="rows"]').value = data?.rows ?? '';
            refreshLinkingSectionNames();
        }

        function removeLinkingSection(button) {
            const sections = document.querySelectorAll('.linking-section');
            if (sections.length > 1) {
                button.closest('.linking-section').remove();
                refreshLinkingSectionNames();
            }
        }

        function refreshLinkingSectionNames() {
            document.querySelectorAll('.linking-section').forEach((section, index) => {
                section.querySelector('[data-name="title"]').name = `linking_sections[${index}][title]`;
                section.querySelector('[data-name="rows"]').name = `linking_sections[${index}][rows]`;
            });
        }

        initialLinkingSections.forEach((section) => addLinkingSection(section));
    </script>
@endsection
