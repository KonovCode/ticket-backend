<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use Database\Factories\TicketFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TicketOrderControllerTest extends TestCase
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

        $this->ticket = TicketFactory::new()->create(['user_id' => $this->user->id]);
    }

    public function test_user_success_purchase(): void
    {
        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/purchase-ticket/{$this->ticket->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'order_id'
            ])
            ->assertJson([
                'message' => 'Ticket successfully purchased.'
            ]);
    }

    public function test_unauthorized_user_purchase(): void
    {
        $response = $this->postJson("/api/purchase-ticket/{$this->ticket->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_ticket_not_found(): void
    {
        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/purchase-ticket/9999");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Ticket not found.'
            ]);
    }

    public function test_payment_callback_success(): void
    {
        $data = [
            'status' => 'paid',
        ];

        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $orderResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/purchase-ticket/{$this->ticket->id}");

        $orderId = $orderResponse->json('order_id');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/confirm-payment/ticket-order/{$orderId}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Payment successful.'
            ]);
    }

    public function test_payment_callback_failed(): void
    {
        $data = ['status' => 'failed'];

        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $orderResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/purchase-ticket/{$this->ticket->id}");

        $orderId = $orderResponse->json('order_id');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/confirm-payment/ticket-order/{$orderId}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Payment failed.'
            ]);

        $this->assertDatabaseHas('ticket_orders', [
            'id' => $orderId,
            'status' => OrderStatus::FAILED
        ]);
    }

    public function test_payment_callback_order_not_found(): void
    {
        $data = ['status' => 'paid'];

        $token = $this->user->createToken('api-token', ['default'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/confirm-payment/ticket-order/999999", $data); // Несуществующий ID

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Order not found.'
            ]);
    }
}
