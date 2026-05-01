<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} – @yield('title', 'Portal')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f5f5f0; color: #1a1a1a; }
        nav { background: #111; color: #fff; padding: 12px 24px; display: flex; align-items: center; gap: 24px; }
        nav a { color: #ccc; text-decoration: none; font-size: 14px; }
        nav a:hover { color: #fff; }
        nav .brand { font-weight: 700; color: #fff; font-size: 16px; }
        nav .spacer { flex: 1; }
        .container { max-width: 1100px; margin: 0 auto; padding: 32px 24px; }
        h1 { font-size: 22px; margin-bottom: 24px; }
        h2 { font-size: 18px; margin-bottom: 16px; }
        .card { background: #fff; border: 1px solid #e0e0d8; border-radius: 6px; padding: 20px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        th { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #888; }
        .btn { display: inline-block; padding: 8px 16px; border-radius: 4px; font-size: 13px; cursor: pointer; text-decoration: none; border: none; }
        .btn-primary { background: #1a1a1a; color: #fff; }
        .btn-secondary { background: #e0e0d8; color: #1a1a1a; }
        .btn-danger { background: #c0392b; color: #fff; }
        .btn-sm { padding: 4px 10px; font-size: 12px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-closed { background: #fce8e8; color: #c0392b; }
        .badge-draft { background: #e8e8e8; color: #666; }
        .badge-in_progress { background: #fff3cd; color: #856404; }
        .badge-submitted { background: #cfe2ff; color: #084298; }
        .badge-under_review { background: #fff3cd; color: #856404; }
        .badge-graded { background: #d4edda; color: #155724; }
        form .field { margin-bottom: 16px; }
        form label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; }
        form input, form select, form textarea { width: 100%; padding: 8px 10px; border: 1px solid #d0d0c8; border-radius: 4px; font-size: 14px; }
        form textarea { min-height: 80px; }
        .alert { padding: 10px 16px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #fce8e8; color: #c0392b; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 32px; }
        .stat-card { background: #fff; border: 1px solid #e0e0d8; border-radius: 6px; padding: 20px; }
        .stat-card .label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-bottom: 8px; }
        .stat-card .value { font-size: 36px; font-weight: 700; }
        .stat-card .sub { font-size: 12px; color: #888; margin-top: 4px; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .options-builder { border: 1px solid #e0e0d8; border-radius: 4px; padding: 12px; margin-top: 8px; }
        .option-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .option-row input[type=text] { flex: 1; }
    </style>
</head>
<body>
<nav>
    <span class="brand">YP Portal</span>
    @auth
        @if(auth()->user()->isLecturer())
            <a href="{{ route('lecturer.dashboard') }}">Overview</a>
            <a href="{{ route('lecturer.exams.index') }}">Exams</a>
            <a href="{{ route('lecturer.classes.index') }}">Classes</a>
            <a href="{{ route('lecturer.subjects.index') }}">Subjects</a>
        @else
            <a href="{{ route('student.dashboard') }}">Dashboard</a>
            <a href="{{ route('student.exams.index') }}">Available Exams</a>
        @endif
        <span class="spacer"></span>
        <span style="font-size:13px;color:#aaa">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;color:#aaa;cursor:pointer;font-size:13px">Logout</button>
        </form>
    @endauth
</nav>
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</div>
</body>
</html>
