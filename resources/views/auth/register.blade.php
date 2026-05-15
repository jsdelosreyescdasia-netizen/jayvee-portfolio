@extends('layouts.app', ['title' => 'Create Employee Account'])

@section('content')
    <div class="auth-wrap">
        <div class="page-head">
            <div>
                <h1>Create Employee Account</h1>
                <p>Each employee gets a separate report workspace.</p>
            </div>
        </div>

        <form class="panel" method="POST" action="{{ route('register.store') }}">
            @csrf

            <div class="field-stack">
                <div>
                    <label for="name">Employee Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" required autofocus>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div>
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>
                <div class="actions">
                    <button class="btn" type="submit">Create Account</button>
                    <a class="btn secondary" href="{{ route('login') }}">Login</a>
                </div>
            </div>
        </form>
    </div>
@endsection
