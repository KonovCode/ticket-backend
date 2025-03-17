<?php

namespace Tests\Feature;

use Database\Factories\TicketFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::new()->create([
            'name' => 'Vladislav',
            'email' => 'vlad@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_get_all_tickets(): void
    {
        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/tickets');

        $response->assertStatus(200)
        ->assertJsonStructure(['tickets']);
    }

    public function test_show_concrete_ticket()
    {
        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $ticket = TicketFactory::new()->create([
            'title' => 'Ticket title',
            'description' => 'Ticket default description',
            'date' => now(),
            'time' => '14:50',
            'price' => 100,
            'city' => 'Dnepr',
            'address' => 'Dovatora 88',
            'user_id' => $this->user->id,
            'total_tickets' => 80,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/show-ticket/{$ticket->id}");

        $response->assertStatus(200)->assertJsonStructure(['ticket']);
    }

    public function test_show_concrete_ticket_not_found()
    {
        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/show-ticket/10");

        $response->assertStatus(404)->assertJsonStructure(['message']);
    }

    public function test_create_ticket_with_exists_token_access()
    {
        $token = $this->user->createToken('api-token', ['ticket-crud'])->plainTextToken;

        $data = [
            'title' => 'Ticket title',
            'description' => 'Ticket default description',
            'date' => now(),
            'time' => '14:50',
            'price' => 100,
            'city' => 'Dnepr',
            'address' => 'Dovatora 88',
            'total_tickets' => 80,

        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/create-ticket', $data);

        $response->assertStatus(200)->assertJsonStructure(['message', 'ticket']);
    }

    public function test_create_ticket_with_exists_token_without_access()
    {
        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $data = [
            'title' => 'Ticket title',
            'description' => 'Ticket default description',
            'date' => now(),
            'time' => '14:50',
            'price' => 100,
            'city' => 'Dnepr',
            'address' => 'Dovatora 88',
            'total_tickets' => 80,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/create-ticket', $data);

        $response->assertStatus(403)->assertJsonStructure(['message']);
    }

    public function test_update_ticket()
    {
        $token = $this->user->createToken('api-token', ['ticket-crud'])->plainTextToken;

        $ticket = TicketFactory::new()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'price' => 888,
            'city' => 'Odessa',
            'time' => '18:20',
            'total_tickets' => 80,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/update-ticket/{$ticket->id}", $data);

        $response->assertStatus(200)->assertJsonStructure(['message', 'ticket']);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'price' => 888,
            'city' => 'Odessa',
            'time' => '18:20',
            'total_tickets' => 80,
            'title' => $ticket->title,
            'description' => $ticket->description,
        ]);
    }

    public function test_delete_ticket()
    {
        $token = $this->user->createToken('api-token', ['ticket-crud'])->plainTextToken;

        $ticket = TicketFactory::new()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/delete-ticket/{$ticket->id}");

        $response->assertStatus(200)->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('tickets', [
            'id' => $ticket->id
        ]);
    }
}
