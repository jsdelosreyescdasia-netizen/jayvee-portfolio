@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <div class="auth-wrap">
        <div class="page-head">
            <div>
                <h1>Employee Login</h1>
                <p>Sign in to manage your daily, weekly, and monthly reports.</p>
            </div>
        </div>

        <form class="panel" method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="field-stack">
                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <label class="check-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <div class="actions">
                    <button class="btn" type="submit">Login</button>
                    <a class="btn secondary" href="{{ route('register') }}">Create Account</a>
                </div>
            </div>
        </form>
    </div>
@endsection
