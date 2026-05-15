@extends('layouts.app', ['title' => 'Employees'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Employees</h1>
            <p>Add employee accounts and remove accounts that no longer need access.</p>
        </div>
    </div>

    <div class="panel" style="margin-bottom: 14px;">
        <h2>Add Employee</h2>
        <form method="POST" action="{{ route('employees.store') }}" style="margin-top: 14px;">
            @csrf
            <div class="grid">
                <div class="span-2">
                    <label for="name">Employee Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="span-2">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div class="span-2">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="text" placeholder="Leave blank to use email as password">
                </div>
                <div style="align-self: end;">
                    <button class="btn" type="submit">Add Employee</button>
                </div>
            </div>
        </form>
    </div>

    <form class="panel" method="GET" action="{{ route('employees.index') }}" style="margin-bottom: 14px;">
        <div class="grid">
            <div class="span-2">
                <label for="search">Search Employees</label>
                <input id="search" name="search" value="{{ $search }}" placeholder="Name or email">
            </div>
            <div style="align-self: end;">
                <button class="btn" type="submit">Search</button>
            </div>
            <div style="align-self: end;">
                <a class="btn secondary" href="{{ route('employees.index') }}">Clear</a>
            </div>
        </div>
    </form>

    <div class="panel table-wrap">
        <div>
            {{ $employees->links() }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Daily Reports</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td style="text-align: center;">{{ $employee->daily_reports_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Delete this employee account? Their old reports will stay in the system.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted" style="text-align: center;">No employee accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
