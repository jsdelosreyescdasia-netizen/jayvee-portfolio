@extends('layouts.admin', ['title' => 'Message from '.$message->name])

@section('content')
<div class="admin-heading admin-heading--split">
    <div>
        <h1>{{ $message->name }}</h1>
        <p>{{ $message->email }} · {{ $message->created_at->format('M d, Y g:i A') }}</p>
    </div>
    <div class="message-actions">
        <form method="post" action="{{ route('admin.messages.update', $message) }}">
            @csrf
            @method('put')
            <button class="button button--small" type="submit">{{ $message->read_at ? 'Mark Unread' : 'Mark Read' }}</button>
        </form>
        <form method="post" action="{{ route('admin.messages.destroy', $message) }}">
            @csrf
            @method('delete')
            <button class="button button--dark" type="submit">Delete</button>
        </form>
    </div>
</div>

<article class="message-panel">
    <dl>
        <div><dt>Status</dt><dd>{{ $message->read_at ? 'Read' : 'Unread' }}</dd></div>
        <div><dt>IP Address</dt><dd>{{ $message->ip_address ?: 'Unavailable' }}</dd></div>
    </dl>
    <h2>Message</h2>
    <p>{{ $message->message }}</p>
</article>
@endsection
