<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="login-body">
    <form class="login-card" method="post" action="{{ route('admin.login.store') }}">
        @csrf
        <h1>Showcase CMS</h1>
        <p>Enter the admin password to edit website content.</p>
        @if($errors->any())
            <div class="notice notice--error">{{ $errors->first() }}</div>
        @endif
        <label>Password<input type="password" name="password" required autofocus></label>
        <button class="button button--red" type="submit">Login</button>
    </form>
</body>
</html>
