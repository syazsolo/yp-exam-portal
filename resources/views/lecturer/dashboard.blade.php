@extends('layouts.app')
@section('title', 'Overview')
@section('content')

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Active Exams</div>
        <div class="value" style="color:#8b1a1a">{{ $stats['active_exams'] }}</div>
        <div class="sub">Currently running</div>
    </div>
    <div class="stat-card">
        <div class="label">Total Exams</div>
        <div class="value">{{ $stats['total_exams'] }}</div>
        <div class="sub">All time</div>
    </div>
    <div class="stat-card">
        <div class="label">Classes</div>
        <div class="value">{{ $stats['classes'] }}</div>
        <div class="sub">Active groups</div>
    </div>
    <div class="stat-card">
        <div class="label">Students</div>
        <div class="value">{{ $stats['students'] }}</div>
        <div class="sub">Across all classes</div>
    </div>
</div>

@if($pendingReviews > 0)
<div class="alert" style="background:#fff3cd;color:#856404;margin-bottom:24px">
    {{ $pendingReviews }} session(s) awaiting open-text review.
</div>
@endif

<div class="section-header">
    <h2>Recent Exams</h2>
    <a href="{{ route('lecturer.exams.index') }}" class="btn btn-secondary btn-sm">View all →</a>
</div>
<div class="card" style="padding:0">
    <table>
        <thead><tr><th>Title</th><th>Subject</th><th>Status</th><th>Duration</th><th>Sessions</th></tr></thead>
        <tbody>
        @forelse($recentExams as $exam)
            <tr>
                <td><a href="{{ route('lecturer.exams.show', $exam['id']) }}">{{ $exam['title'] }}</a></td>
                <td style="color:#888">{{ $exam['subject'] }}</td>
                <td><span class="badge badge-{{ $exam['status'] }}">{{ strtoupper($exam['status']) }}</span></td>
                <td>{{ $exam['time_limit'] }}min</td>
                <td>{{ $exam['sessions_count'] }}</td>
            </tr>
        @empty
            <tr><td colspan="5" style="color:#888;text-align:center;padding:24px">No exams yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="section-header" style="margin-top:32px">
    <h2>Classes</h2>
    <a href="{{ route('lecturer.classes.index') }}" class="btn btn-secondary btn-sm">Manage →</a>
</div>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
    @forelse($classes as $class)
    <div class="card">
        <strong>{{ $class['name'] }}</strong>
        <div style="color:#888;font-size:13px;margin-top:4px">{{ $class['students'] }} students</div>
        <div style="margin-top:8px;display:flex;flex-wrap:wrap;gap:4px">
            @foreach($class['subjects'] as $sub)
                <span style="background:#e8e8e8;color:#555;font-size:11px;padding:2px 8px;border-radius:3px">{{ $sub }}</span>
            @endforeach
        </div>
    </div>
    @empty
        <p style="color:#888;grid-column:1/-1">No classes yet. <a href="{{ route('lecturer.classes.create') }}">Create one</a>.</p>
    @endforelse
</div>

@endsection
