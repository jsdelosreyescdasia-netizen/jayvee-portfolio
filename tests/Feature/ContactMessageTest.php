<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_stores_message_when_not_robot_is_checked(): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true]),
        ]);

        $response = $this->post(route('contact.store'), [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'message' => 'Please contact me about service support.',
            'g-recaptcha-response' => 'valid-token',
        ]);

        $response->assertSessionHas('contact_status');

        $this->assertDatabaseHas(ContactMessage::class, [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
        ]);
    }
}
