<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Daily Report System' }}</title>
    <style>
        :root {
            --bg: #eef3f8;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #d8e1ec;
            --panel: #ffffff;
            --brand: #0f766e;
            --brand-dark: #115e59;
            --brand-soft: #e8f7f3;
            --sidebar: #111827;
            --sidebar-2: #1f2937;
            --warn: #b42318;
            --shadow: 0 18px 45px rgba(15, 23, 42, .08);
        }

        * { box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--ink);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.45;
            margin: 0;
        }
        a { color: var(--brand); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .app-frame {
            display: grid;
            grid-template-columns: 248px minmax(0, 1fr);
            min-height: 100vh;
        }
        .app-frame.guest-frame {
            display: block;
        }
        .guest-frame .shell {
            margin: 0 auto;
        }
        .sidebar {
            background: var(--sidebar);
            color: #fff;
            display: flex;
            flex-direction: column;
            gap: 22px;
            min-height: 100vh;
            padding: 22px 16px;
            position: sticky;
            top: 0;
        }
        .brand {
            border-bottom: 1px solid rgba(255, 255, 255, .10);
            color: #f8fafc;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: .01em;
            padding: 0 10px 18px;
        }
        .nav-links {
            display: grid;
            gap: 6px;
        }
        .nav-links a,
        .nav-button {
            border-radius: 10px;
            color: #cbd5e1;
            display: block;
            font-weight: 700;
            padding: 11px 12px;
            text-align: left;
            width: 100%;
        }
        .nav-links a.active {
            background: var(--brand);
            color: #fff;
        }
        .nav-links a:hover,
        .nav-button:hover {
            background: var(--sidebar-2);
            color: #fff;
            text-decoration: none;
        }
        .sidebar-footer {
            border-top: 1px solid rgba(255, 255, 255, .10);
            margin-top: auto;
            padding: 16px 8px 0;
        }
        .sidebar-user {
            color: #dbe5ef;
            font-size: 13px;
            line-height: 1.35;
            margin-bottom: 10px;
        }
        .nav-button {
            background: transparent;
            border: 0;
            cursor: pointer;
            font: inherit;
        }
        .topbar {
            align-items: center;
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 20px;
            min-height: 58px;
            padding: 10px 14px 10px 18px;
        }
        .topbar-user {
            color: var(--ink);
            font-weight: 800;
            line-height: 1.2;
        }
        .topbar-logout {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            color: #1e293b;
            cursor: pointer;
            font: inherit;
            font-weight: 800;
            min-height: 40px;
            padding: 8px 16px;
        }
        .topbar-logout:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            text-decoration: none;
        }
        .shell {
            max-width: 1460px;
            padding: 30px 32px 42px;
            width: 100%;
        }
        .page-head {
            align-items: flex-start;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        h1 {
            color: var(--ink);
            font-size: 28px;
            line-height: 1.2;
            margin: 0 0 4px;
        }
        h2 { color: var(--ink); font-size: 18px; margin: 0; }
        p { color: var(--muted); margin: 0; }
        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 18px;
        }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .btn {
            align-items: center;
            background: var(--brand);
            border: 1px solid var(--brand);
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-weight: 800;
            justify-content: center;
            min-height: 40px;
            padding: 8px 16px;
            transition: background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }
        .btn:hover {
            background: var(--brand-dark);
            box-shadow: 0 10px 22px rgba(15, 118, 110, .16);
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }
        .btn.secondary {
            background: #fff;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        .btn.secondary:hover { background: #f8fafc; border-color: #94a3b8; color: var(--ink); }
        .btn.danger { background: var(--warn); border-color: var(--warn); }
        .alert {
            background: #ecfdf5;
            border: 1px solid #b7e4d4;
            border-radius: 12px;
            color: #065f46;
            margin-bottom: 14px;
            padding: 11px 14px;
        }
        .error {
            background: #fff4f1;
            border: 1px solid #f0c2ba;
            border-radius: 12px;
            color: #7e291b;
            margin-bottom: 14px;
            padding: 11px 14px;
        }
        .grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        label {
            color: #1f2937;
            display: block;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        input, textarea, select {
            border: 1px solid var(--line);
            border-radius: 10px;
            font: inherit;
            min-height: 40px;
            padding: 8px 12px;
            transition: border-color .15s ease, box-shadow .15s ease;
            width: 100%;
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(15, 118, 110, .12);
            outline: 0;
        }
        input[type="checkbox"] {
            min-height: 0;
            width: auto;
        }
        textarea { min-height: 84px; resize: vertical; }
        .paste-box {
            font-family: Consolas, "Courier New", monospace;
            min-height: 320px;
            white-space: pre;
        }
        .span-2 { grid-column: span 2; }
        .span-4 { grid-column: span 4; }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #2d333a;
            padding: 7px 9px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f1f5f9;
            font-weight: 800;
            text-align: center;
        }
        .report-title {
            background: #7b7b7b;
            color: #fff;
            font-weight: 800;
            text-align: center;
        }
        .total-row th {
            background: #bfeeff;
            text-align: center;
        }
        .meeting-row th {
            background: #8d8d8d;
            color: #fff;
            text-align: center;
        }
        .weekly-table th {
            background: #c7c7c7;
            color: #000;
        }
        .weekly-table .month-title {
            background: #bdbdbd;
            color: #111;
            text-align: center;
        }
        .weekly-table .date-cell,
        .weekly-table .center-cell,
        .weekly-total-row th {
            text-align: center;
        }
        .weekly-table .date-cell,
        .weekly-table .strong-cell {
            font-weight: 800;
        }
        .weekly-total-row th {
            background: #9c9c9c;
            color: #fff;
        }
        .monthly-table .month-title {
            background: #8d8d8d;
            color: #fff;
            text-align: center;
        }
        .monthly-table th {
            background: #f4f4f4;
        }
        .monthly-table .center-cell {
            text-align: center;
        }
        .monthly-table .strong-cell {
            font-weight: 800;
        }
        .monthly-table .summary-value {
            color: #f00;
            font-weight: 800;
            text-align: center;
        }
        .monthly-table .leave-row td {
            background: #ffc7cf;
            color: #c00;
        }
        .monthly-table .spacer-row td {
            border-color: #e4e4e4;
            height: 22px;
        }
        .muted { color: var(--muted); }
        .form-section {
            background: #fbfdff;
            border: 1px solid var(--line);
            border-radius: 12px;
            margin-top: 14px;
            padding: 14px;
        }
        .links { display: grid; gap: 10px; margin-top: 12px; }
        .link-row {
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(0, 3fr) minmax(180px, 1fr) auto;
        }
        .table-wrap {
            overflow-x: auto;
            scrollbar-color: #94a3b8 #e2e8f0;
        }
        .scroll-table {
            max-height: 520px;
            overflow: auto;
            scrollbar-color: #94a3b8 #e2e8f0;
        }
        .pagination-bar {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .pagination-summary {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }
        .pagination {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .page-item {
            display: inline-flex;
        }
        .page-link,
        .page-item span {
            align-items: center;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            color: #1e293b;
            display: inline-flex;
            font-weight: 700;
            justify-content: center;
            min-height: 34px;
            min-width: 34px;
            padding: 6px 10px;
        }
        .page-link:hover {
            background: #f8fafc;
            color: var(--ink);
            text-decoration: none;
        }
        .page-item.active .page-link,
        .page-item.active span {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }
        .page-item.disabled .page-link,
        .page-item.disabled span {
            color: #98a2b3;
            cursor: default;
        }
        .pagination svg {
            height: 16px;
            width: 16px;
        }
        .auth-wrap { margin: 0 auto; max-width: 520px; }
        .field-stack { display: grid; gap: 14px; }
        .check-row {
            align-items: center;
            display: flex;
            gap: 8px;
            margin: 0;
        }
        @media (max-width: 760px) {
            .app-frame { display: block; }
            .sidebar { padding: 16px; }
            .brand { padding-bottom: 12px; }
            .nav-links { grid-template-columns: 1fr; }
            .sidebar-footer { margin-top: 0; }
            .shell { padding: 16px; }
            .topbar { margin-bottom: 16px; }
            .page-head { display: block; }
            .actions { margin-top: 12px; }
            .grid, .link-row { grid-template-columns: 1fr; }
            .span-2, .span-4 { grid-column: auto; }
            .pagination-bar {
                align-items: flex-start;
                flex-direction: column;
            }
            table { min-width: 860px; }
        }
    </style>
</head>
<body>
    <div @class(['app-frame', 'guest-frame' => auth()->guest()])>
        @auth
            <aside class="sidebar">
                <div class="brand">Daily Report System</div>
                <nav class="nav-links">
                    <a @class(['active' => request()->routeIs('reports.index', 'reports.create', 'reports.edit', 'reports.show', 'reports.import')]) href="{{ route('reports.index') }}">Daily Reports</a>
                    <a @class(['active' => request()->routeIs('reports.weekly')]) href="{{ route('reports.weekly') }}">Weekly Report</a>
                    <a @class(['active' => request()->routeIs('reports.monthly')]) href="{{ route('reports.monthly') }}">Monthly Report</a>
                    @if (auth()->user()->isAdmin())
                        <a @class(['active' => request()->routeIs('employees.*')]) href="{{ route('employees.index') }}">Employees</a>
                    @endif
                </nav>
            </aside>
        @endauth

        <main class="shell">
            @auth
                <nav class="topbar" aria-label="User navigation">
                    <div class="topbar-user">{{ auth()->user()->name }}</div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="topbar-logout" type="submit">Logout</button>
                    </form>
                </nav>
            @endauth

            @if (session('success'))
                <div class="alert">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="error">
                    <strong>Please check the form.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
