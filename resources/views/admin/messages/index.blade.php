@extends('layouts.admin', ['title' => 'Messages'])

@section('content')
<div class="admin-heading admin-heading--split">
    <div>
        <h1>Messages</h1>
        <p>{{ $unreadCount }} unread contact {{ \Illuminate\Support\Str::plural('message', $unreadCount) }}.</p>
    </div>
</div>

<div class="admin-table">
    @forelse($messages as $message)
        <div class="admin-row admin-row--message {{ $message->read_at ? '' : 'is-unread' }}">
            <span>{{ $message->created_at->format('M d') }}</span>
            <strong>{{ $message->name }}</strong>
            <span>{{ $message->email }}</span>
            <a class="button button--small" href="{{ route('admin.messages.show', $message) }}">View</a>
        </div>
    @empty
        <div class="empty-state">No messages yet.</div>
    @endforelse
</div>

<div class="pagination-wrap">
    {{ $messages->links() }}
</div>
@endsection
