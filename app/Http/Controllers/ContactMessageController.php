<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ContactMessageController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
            'g-recaptcha-response' => ['required', 'string'],
            'website' => ['nullable', 'prohibited'],
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
            'website.prohibited' => 'The message could not be submitted.',
        ]);

        $verified = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $data['g-recaptcha-response'],
            'remoteip' => $request->ip(),
        ])->json('success', false);

        if (! $verified) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.',
            ]);
        }

        ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('contact_status', 'Thank you. Your message has been sent.');
    }
}
