@extends('layouts.app')
@section('title', 'Subjects')
@section('content')
<div class="section-header">
    <h1>Subjects</h1>
    <a href="{{ route('lecturer.subjects.create') }}" class="btn btn-primary">+ New Subject</a>
</div>
<div class="card" style="padding:0">
    <table>
        <thead><tr><th>Name</th><th>Description</th><th>Classes</th><th></th></tr></thead>
        <tbody>
        @forelse($subjects as $subject)
            <tr>
                <td><strong>{{ $subject->name }}</strong></td>
                <td style="color:#888">{{ $subject->description ?: '—' }}</td>
                <td>{{ $subject->classes->count() }}</td>
                <td style="text-align:right">
                    <a href="{{ route('lecturer.subjects.edit', $subject) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <form method="POST" action="{{ route('lecturer.subjects.destroy', $subject) }}" style="display:inline" onsubmit="return confirm('Delete this subject?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center;color:#888;padding:24px">No subjects yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
