<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index()
    {
        return view('admin.messages.index', [
            'messages' => ContactMessage::latest()->paginate(12),
            'unreadCount' => ContactMessage::whereNull('read_at')->count(),
        ]);
    }

    public function show(ContactMessage $message)
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function update(ContactMessage $message)
    {
        $message->update([
            'read_at' => $message->read_at ? null : now(),
        ]);

        return back()->with('status', $message->read_at ? 'Message marked as read.' : 'Message marked as unread.');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return to_route('admin.messages.index')->with('status', 'Message deleted.');
    }
}
