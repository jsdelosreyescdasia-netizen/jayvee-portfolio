<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_contact_messages(): void
    {
        ContactMessage::create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'message' => 'I need help with a product inquiry.',
        ]);

        $this->withSession(['admin_authenticated' => true])
            ->get(route('admin.messages.index'))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('maria@example.com');
    }
}
