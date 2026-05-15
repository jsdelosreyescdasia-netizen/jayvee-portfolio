@php
    $oldIssuances = old('issuances');
    $initialIssuances = $oldIssuances ?? ($report->exists
        ? $report->issuances->map(fn ($issuance) => [
            'issuance_no' => $issuance->issuance_no,
            'issuance_date' => optional($issuance->issuance_date)->format('Y-m-d'),
            'links' => $issuance->gatheredLinks->map(fn ($link) => [
                'title' => $link->title,
                'remarks' => $link->remarks,
            ])->values(),
        ])->values()->all()
        : [[
            'issuance_no' => '',
            'issuance_date' => '',
            'links' => [[
                'title' => '',
                'remarks' => '',
            ]],
        ]]);
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

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
                <label for="other_task">Other Task No. of Documents</label>
                <input id="other_task" name="other_task" value="{{ old('other_task', $report->other_task) }}">
            </div>
            <div>
                <label for="other_task_title">Other Task Title</label>
                <input id="other_task_title" name="other_task_title" value="{{ old('other_task_title', $report->other_task_title) }}">
            </div>
            <div>
                <label for="other_task_work_done">Other Task Work Done</label>
                <input id="other_task_work_done" name="other_task_work_done" value="{{ old('other_task_work_done', $report->other_task_work_done) }}">
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
        </div>
    </div>

    <div id="issuances"></div>

    <div class="actions" style="margin-top: 16px;">
        <button class="btn" type="button" onclick="addIssuance()">Add Issuance</button>
        <button class="btn" type="submit">Save Report</button>
        <a class="btn secondary" href="{{ route('reports.index') }}">Cancel</a>
    </div>
</form>

<template id="issuance-template">
    <section class="form-section issuance-block">
        <div class="page-head" style="margin-bottom: 12px;">
            <h2>Issuance</h2>
            <button class="btn danger" type="button" onclick="removeIssuance(this)">Remove</button>
        </div>
        <div class="grid">
            <div class="span-2">
                <label>Issuance No.</label>
                <input data-name="issuance_no" required>
            </div>
            <div>
                <label>Date</label>
                <input data-name="issuance_date" type="date">
            </div>
        </div>
        <div class="links"></div>
        <button class="btn secondary" style="margin-top: 12px;" type="button" onclick="addLink(this)">Add Gathered Text</button>
    </section>
</template>

<template id="link-template">
    <div class="link-row">
        <div>
            <label>Gathered Word / Phrase / Citation</label>
            <textarea data-name="title" required></textarea>
        </div>
        <div>
            <label>Remarks</label>
            <input data-name="remarks" placeholder="not found, no exact date, etc.">
        </div>
        <div style="align-self: end;">
            <button class="btn danger" type="button" onclick="removeLink(this)">Remove</button>
        </div>
    </div>
</template>

<script>
    const initialIssuances = @json($initialIssuances);

    function addIssuance(data = null) {
        const container = document.getElementById('issuances');
        const template = document.getElementById('issuance-template').content.cloneNode(true);
        const block = template.querySelector('.issuance-block');
        container.appendChild(block);

        block.querySelector('[data-name="issuance_no"]').value = data?.issuance_no ?? '';
        block.querySelector('[data-name="issuance_date"]').value = data?.issuance_date ?? '';

        const links = data?.links?.length ? data.links : [{ title: '', remarks: '' }];
        links.forEach((link) => addLink(block.querySelector('.btn.secondary'), link));
        refreshNames();
    }

    function removeIssuance(button) {
        const blocks = document.querySelectorAll('.issuance-block');
        if (blocks.length > 1) {
            button.closest('.issuance-block').remove();
            refreshNames();
        }
    }

    function addLink(button, data = null) {
        const block = button.closest('.issuance-block');
        const template = document.getElementById('link-template').content.cloneNode(true);
        const row = template.querySelector('.link-row');
        block.querySelector('.links').appendChild(row);

        row.querySelector('[data-name="title"]').value = data?.title ?? '';
        row.querySelector('[data-name="remarks"]').value = data?.remarks ?? '';
        refreshNames();
    }

    function removeLink(button) {
        const links = button.closest('.links').querySelectorAll('.link-row');
        if (links.length > 1) {
            button.closest('.link-row').remove();
            refreshNames();
        }
    }

    function refreshNames() {
        document.querySelectorAll('.issuance-block').forEach((block, issuanceIndex) => {
            block.querySelector('[data-name="issuance_no"]').name = `issuances[${issuanceIndex}][issuance_no]`;
            block.querySelector('[data-name="issuance_date"]').name = `issuances[${issuanceIndex}][issuance_date]`;

            block.querySelectorAll('.link-row').forEach((row, linkIndex) => {
                row.querySelector('[data-name="title"]').name = `issuances[${issuanceIndex}][links][${linkIndex}][title]`;
                row.querySelector('[data-name="remarks"]').name = `issuances[${issuanceIndex}][links][${linkIndex}][remarks]`;
            });
        });
    }

    initialIssuances.forEach((issuance) => addIssuance(issuance));
</script>
